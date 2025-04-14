document.addEventListener("DOMContentLoaded", function () {
    checkUserAuth();
    setupSpaNavigation();
    setupLogoutButton();  // Ensure the logout button works when available
});

// Function to setup SPA navigation
function setupSpaNavigation() {
    document.querySelectorAll(".spa-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            let route = this.getAttribute("href");
            history.pushState(null, "", "/" + route);

            // Load content dynamically
            fetch("index.php?route=" + route)
                .then(response => response.text())
                .then(data => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data, "text/html");
                    let newContent = doc.querySelector(".main-content").innerHTML;
                    document.querySelector(".main-content").innerHTML = newContent;

                    setupLogoutButton(); // Ensure logout works on dynamically loaded content
                });
        });
    });
}

// Function to check authentication status
function checkUserAuth() {
    let token = sessionStorage.getItem("jwt");

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

                    setupLogoutButton(); // Ensure logout button is attached
                }
            })
            .catch(() => {
                sessionStorage.removeItem("jwt");
            });
    }
}

// Function to setup the logout button safely
function setupLogoutButton() {
    let logoutBtn = document.getElementById("logout-link");
    if (logoutBtn) {
        logoutBtn.removeEventListener("click", handleLogout); // Prevent duplicate listeners
        logoutBtn.addEventListener("click", handleLogout);
    }
}

// Logout handler function
function handleLogout(e) {
    e.preventDefault();

    // Use the CSRF token constant from your script
    const formData = new URLSearchParams();
    formData.append("csrf_token", CSRF_TOKEN); // CSRF_TOKEN is already defined in your script

    fetch("api/user/logout", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"  // Ensure PHP $_POST recognizes it
        },
        body: formData.toString(),  // Convert to URL-encoded string
        credentials: "include"  // Ensure cookies are sent
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


