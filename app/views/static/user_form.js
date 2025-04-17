export default function initUserForm(user = null) {
    const form = document.getElementById("user-form");
    const deleteBtn = document.getElementById("delete-user-btn");

    if (user) {
        form.id.value = user.id;
        form.email.value = user.email;
        form.role && (form.role.value = user.role);
        form.two_factor_enabled && (form.two_factor_enabled.value = user.two_factor_enabled ? "true" : "false");
        form.balance && (form.balance.value = parseFloat(user.balance).toFixed(2)); // ✅ Prefill balance
    }


    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        // Remove password if blank
        const password = formData.get("password");
        if (!password) {
            formData.delete("password");
        }

        // Remove balance if blank
        const balance = formData.get("balance");
        if (balance === null || balance === "") {
            formData.delete("balance");
        }

        // Normalize two_factor_enabled to integer
        const twoFactor = formData.get("two_factor_enabled");
        if (twoFactor !== null) {
            formData.set("two_factor_enabled", twoFactor === "true" ? "1" : "0");
        }

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
                    location.reload(); // Reload on profile update
                }
            })
            .catch(err => {
                console.error("Update failed:", err);
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
                    location.reload();
                }
            })
            .catch(err => {
                console.error("Delete failed:", err);
            });
    });
}
