export default function initUserAlerts() {
    const user = window.AUTH_USER;
    if (!user || !user.id) {
        console.error("User not authenticated or missing ID.");
        return;
    }

    const userId = user.id;
    const alertTableBody = document.getElementById('alerts-body');
    const cryptoSelect = document.getElementById('crypto_id');
    const typeSelect = document.getElementById('alert_type'); // assuming you have this

    let cryptoMap = {};

    function fetchAlerts() {
        fetch('index.php?route=api/user/alerts/getByUserId', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}&user_id=${userId}`
        })
            .then(res => res.json())
            .then(data => {
                const alerts = Array.isArray(data.alerts) ? data.alerts : data;
                renderAlerts(alerts);
            });
    }

    function renderAlerts(alerts) {
        alertTableBody.innerHTML = '';

        if (!Array.isArray(alerts)) {
            console.error("Expected array of alerts but got:", alerts);
            return;
        }

        alerts.forEach(alert => {
            const row = document.createElement('tr');
            row.classList.add('alert-row');
            row.classList.add(alert.active ? 'active' : 'inactive'); // 👈 Apply proper class

            row.innerHTML = `
            <td>${cryptoMap[alert.crypto_id] || `#${alert.crypto_id}`}</td>
            <td>${alert.alert_type === 'higher' ? 'Higher than' : 'Lower than'}</td>
            <td>${alert.price_threshold}</td>
            <td>${alert.active ? 'Active' : 'Inactive'}</td>
            <td>${alert.last_triggered_at ?? '-'}</td>
            <td>
                <button onclick="window.toggleAlert(${alert.id}, ${alert.active})">
                    ${alert.active ? 'Disable' : 'Enable'}
                </button>

                <button onclick="window.deleteAlert(${alert.id})">Delete</button>
            </td>
        `;
            alertTableBody.appendChild(row);
        });
    }



    function deleteAlert(id) {
        fetch('index.php?route=api/user/alerts/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}&alert_id=${id}`
        }).then(() => fetchAlerts());
    }


    function loadCryptos() {
        fetch('index.php?route=api/prices/live')
            .then(res => res.json())
            .then(response => {
                const cryptos = response.data;
                cryptoSelect.innerHTML = '';
                cryptoMap = {};

                Object.values(cryptos).forEach(c => {
                    cryptoMap[c.id] = c.name;
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = `${c.name} (${c.symbol})`;
                    cryptoSelect.appendChild(opt);
                });
            });
    }

    function toggleAlert(id, currentStatus) {
        const newStatus = currentStatus ? 'false' : 'true';

        fetch('index.php?route=api/user/alerts/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}&alert_id=${id}&active=${newStatus}`
        }).then(() => fetchAlerts());
    }

    document.getElementById('new-alert-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = new FormData(this);

        form.append('csrf_token', CSRF_TOKEN);
        form.append('user_id', userId);
        form.append('alert_type', typeSelect.value);
        form.append('active', 1); // default active

        fetch('index.php?route=api/user/alerts/create', {
            method: 'POST',
            body: new URLSearchParams(form)
        }).then(() => {
            this.reset();
            fetchAlerts();
        });
    });


    // expose globally
    window.deleteAlert = deleteAlert;
    window.toggleAlert = toggleAlert;

    // init
    loadCryptos();
    fetchAlerts();
}
