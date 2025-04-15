import initChartViewer from "./chart_viewer.js";
import initLivePrices from "./live_prices.js";

// Widget initializer registry
const widgetInitializers = [
    {
        requiredIds: ['live-price-table'],
        init: initLivePrices
    },
    {
        requiredIds: ['crypto-chart', 'crypto-tabs', 'data-range'],
        init: initChartViewer
    }
];


// Run all applicable widget initializers
function initializeWidgets() {
    widgetInitializers.forEach(({ requiredIds, init }) => {
        const allExist = requiredIds.every(id => document.getElementById(id));
        if (allExist) {
            try {
                init();
            } catch (e) {
                console.error(`Failed to initialize widget for IDs [${requiredIds.join(', ')}]:`, e);
            }
        }
    });
}

// Check authentication and show user name if valid
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
                    document.getElementById("user-greeting").innerText = "Welcome " + data.user_name;
                    document.getElementById("auth-links").style.display = "none";
                    setupLogoutButton();
                }
            })
            .catch(() => {
                sessionStorage.removeItem("jwt");
            });
    }
}

// Setup the logout button
function setupLogoutButton() {
    const logoutBtn = document.getElementById("logout-link");
    if (logoutBtn) {
        logoutBtn.removeEventListener("click", handleLogout);
        logoutBtn.addEventListener("click", handleLogout);
    }
}

// Logout action
function handleLogout(e) {
    e.preventDefault();

    const formData = new URLSearchParams();
    formData.append("csrf_token", CSRF_TOKEN);

    fetch("api/user/logout", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: formData.toString(),
        credentials: "include"
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                sessionStorage.removeItem("jwt");
                setTimeout(() => window.location.href = "/", 500);
            }
        })
        .catch(err => console.error("Logout failed:", err));
}

// SPA navigation logic
function setupSpaNavigation() {
    document.querySelectorAll(".spa-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const route = this.getAttribute("href");
            history.pushState(null, "", "/" + route);

            fetch("index.php?route=" + route)
                .then(response => response.text())
                .then(data => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, "text/html");
                    const newContent = doc.querySelector(".main-content").innerHTML;
                    document.querySelector(".main-content").innerHTML = newContent;

                    // Reinitialize dynamic behaviors
                    setupLogoutButton();
                    initializeWidgets();
                });
        });
    });
}

// Entry point
document.addEventListener("DOMContentLoaded", () => {
    checkUserAuth();
    setupSpaNavigation();
    setupLogoutButton();
    initializeWidgets();
});
