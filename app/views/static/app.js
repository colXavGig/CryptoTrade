import initChartViewer from "./chart_viewer.js";
import initLivePrices from "./live_prices.js";
import initTransactionHistory from "./transactionHistory.js";
import initUserForm from "./user_form.js";
import initNotifications from "./notification.js";

// Widget initializer registry
const widgetInitializers = [
    {
        requiredIds: ['live-price-table'],
        init: initLivePrices
    },
    {
        requiredIds: ['crypto-chart', 'crypto-tabs', 'data-range'],
        init: initChartViewer
    },
    {
        requiredIds: ['transactions-table'],
        init: initTransactionHistory
    },
    {
        requiredIds: ['user-form'],
        init: () => {
            fetch('/?route=api/user/verify')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        initUserForm({
                            id: data.id,
                            email: data.email,
                            role: data.role,
                            two_factor_enabled: data.two_factor_enabled
                        });
                    }
                });
        },

    },
    {
        requiredIds: ['users-table'],
        init: () => {
            import('./admin_users.js')
                .catch(err => console.error("Failed to load admin_users.js:", err));
        }
    },
    {
        requiredIds: ['notification-list'],
        init: initNotifications
    },
    {
        requiredIds: ['alerts-table'],
        init: () => import('./user_alerts.js').then(m => m.default())
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


function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function checkUserAuth() {
    return new Promise((resolve) => {
        const token = getCookie("jwt");

        if (token) {
            fetch("api/user/verify", {
                method: "GET",
                headers: { "Authorization": "Bearer " + token }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user_id) {
                        window.AUTH_USER = {
                            id: data.user_id,
                            email: data.email,
                            role: data.role
                        };

                        const greeting = document.getElementById("user-greeting");
                        const authLinks = document.getElementById("auth-links");

                        if (greeting) greeting.innerText = "Welcome " + data.email;
                        if (authLinks) authLinks.style.display = "none";
                    }
                    resolve(); // done
                })
                .catch(() => {
                    document.cookie = "jwt=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    resolve();
                });
        } else {
            resolve();
        }
    });
}





function fetchUnseenNotificationCount() {
    fetch("index.php?route=api/user/notifications/unseen", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `csrf_token=${CSRF_TOKEN}`
    })
        .then(res => res.json())
        .then(data => {
            const notifications = data.notifications || [];
            const unseenCount = notifications.length;

            const desktopBadge = document.getElementById("notif-count");
            const mobileBadge = document.getElementById("notif-badge-mobile");

            if (desktopBadge) {
                desktopBadge.innerText = unseenCount > 0 ? unseenCount : '';
            }

            if (mobileBadge) {
                mobileBadge.innerText = unseenCount > 0 ? unseenCount : '';
            }
        })
        .catch(err => {
            console.error("Failed to fetch notification count:", err);
        });
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
            console.log(route);
            //temp fix - if route == admin-users, then redirect to admin-users //TODO: fix this later
            if (route === "admin-users") {
                window.location.href = "/admin-users"; //force redirect to rebind events
                return;
            }
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
                    fetchUnseenNotificationCount();


                });
        });
    });
}

// Entry point
document.addEventListener("DOMContentLoaded", () => {
    checkUserAuth().then(() => {
        setupSpaNavigation();
        setupLogoutButton();
        initializeWidgets();
        fetchUnseenNotificationCount();
    });
});

