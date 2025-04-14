import { initLivePrices } from './live_prices.js';
import { initChartViewer } from './chart_viewer.js';

document.addEventListener("DOMContentLoaded", () => {
    checkUserAuth();
    setupSpaNavigation();
    setupLogoutButton();
    reinitializePage(getCurrentRoute()); // First time load
    window.addEventListener("popstate", handlePopState); // Handle browser back/forward
});

function getCurrentRoute() {
    const path = window.location.pathname;
    return path.startsWith('/') ? path.slice(1) : path;
}

function setupSpaNavigation() {
    document.querySelectorAll(".spa-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const route = this.getAttribute("href");

            history.pushState(null, "", "/" + route);
            loadRouteContent(route);
        });
    });
}

function loadRouteContent(route) {
    fetch("index.php?route=" + route)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.querySelector(".main-content").innerHTML;

            document.querySelector(".main-content").innerHTML = newContent;

            // Rebind everything
            setupSpaNavigation();
            setupLogoutButton();
            reinitializePage(route);
        })
        .catch(err => {
            console.error("Failed to load page:", err);
        });
}

function handlePopState() {
    const route = getCurrentRoute();
    loadRouteContent(route);
}

function reinitializePage(route) {
    switch (route) {
        case "user-market":
            initLivePrices();
            initChartViewer();
            break;
        case "home":
            initLivePrices();
            initChartViewer();
            break;
        case "user-wallet":
            // TODO: Add wallet-specific logic
            break;
        case "user-history":
            // TODO: Add history transaction filtering logic
            break;
        default:
            break;
    }
}

function checkUserAuth() {
    const token = sessionStorage.getItem("jwt");

    if (token) {
        fetch("api/user/verify", {
            method: "GET",
            headers: { "Authorization": "Bearer " + token }
        })
            .then(response => response.json())
            .then(data => {
                if (data.user_name) {
                    const greeting = document.getElementById("user-greeting");
                    const authLinks = document.getElementById("auth-links");

                    if (greeting) greeting.innerText = "Welcome " + data.user_name;
                    if (authLinks) authLinks.style.display = "none";

                    setupLogoutButton();
                }
            })
            .catch(() => {
                sessionStorage.removeItem("jwt");
            });
    }
}

function setupLogoutButton() {
    const logoutBtn = document.getElementById("logout-link");
    if (logoutBtn) {
        logoutBtn.removeEventListener("click", handleLogout);
        logoutBtn.addEventListener("click", handleLogout);
    }
}

function handleLogout(e) {
    e.preventDefault();

    const formData = new URLSearchParams();
    formData.append("csrf_token", CSRF_TOKEN); // CSRF_TOKEN must be globally available

    fetch("api/user/logout", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: formData.toString(),
        credentials: "include"
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sessionStorage.removeItem("jwt");
                setTimeout(() => {
                    window.location.href = "/";
                }, 500);
            }
        })
        .catch(error => console.error("Logout failed:", error));
}
