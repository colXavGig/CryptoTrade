
if (window.location.search.includes('session_id')) {
    initialize();
}


async function initialize() {
    // FIXME: the balance gets update on every reload
    //  so we need to find a way to only update it once
    //  maybe by keeping track of the session_id in the database
    //  check stripe docs to see if there is a fix for this

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const sessionId = urlParams.get('session_id');
    const response = await fetch("/api/user/checkout-status", {
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
        },
        method: "POST",
        body: JSON.stringify({ session_id: sessionId }),
    });
    const session = await response.json();

    if (session.status == 'open') {
        window.location.replace('/user-checkout')
    } else if (session.status == 'complete') {

        const userID = document.getElementById('user-wallet').getAttribute('data-user-id');

        let user = await fetch("/api/user/getById?id=" + userID)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    return data.user;
                }
                return null;
            })
            .catch(err => {
                console.error("Failed to load user:", err);
            });
        user.balance = parseFloat(user.balance) + parseFloat(session.amount);

        console.log(user);
        let csrf_token = document.getElementById('csrf_token').value;

        fetch("/api/user/update", {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            method: "POST",
            body: new URLSearchParams({
                csrf_token: csrf_token,
                id: user.id,
                balance: user.balance,
                two_factor_enabled: user.two_factor_enabled,
                role: user.role,
                email: user.email,
                created_at: user.created_at,
            }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.replace('/user-wallet')
                }
            })
            .catch(err => {
                console.error("Update failed:", err);
            });


    }
}