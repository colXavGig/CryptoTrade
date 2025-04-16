export default function initUserForm(user = null) {
    const form = document.getElementById("user-form");
    const deleteBtn = document.getElementById("delete-user-btn");

    if (user) {
        form.id.value = user.id;
        form.email.value = user.email;
        form.role && (form.role.value = user.role);
        form.two_factor_enabled && (form.two_factor_enabled.value = user.two_factor_enabled ? "true" : "false");
        form.balance;// && (form.balance.value = parseFloat(user.balance).toFixed(2));
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("/?route=api/user/update", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams([...formData])
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("User updated successfully.");
                    if (!user) location.reload(); // Reload on profile update
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(err => {
                console.error("Update failed:", err);
                alert("An error occurred while updating.");
            });
    });

    deleteBtn?.addEventListener("click", function () {
        if (!confirm("Are you sure you want to delete this user?")) return;

        fetch("/?route=api/user/delete", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                csrf_token: form.csrf_token.value,
                id: form.id.value
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("User deleted.");
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(err => {
                console.error("Delete failed:", err);
                alert("An error occurred while deleting.");
            });
    });
}
