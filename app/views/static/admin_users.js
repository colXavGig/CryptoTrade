import initUserForm from './user_form.js';

document.querySelectorAll(".edit-user-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        const userId = btn.dataset.id;

        fetch(`/?route=api/user/getById&id=${encodeURIComponent(userId)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("edit-panel").style.display = "block";
                    initUserForm(data.user);
                    document.getElementById("edit-panel").scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert("Error loading user: " + data.error);
                }
            })
            .catch(err => {
                console.error("Failed to load user:", err);
                alert("Unexpected error occurred while loading user.");
            });
    });
});
