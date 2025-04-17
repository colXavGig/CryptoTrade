export default function initNotifications() {

    function fetchNotifications() {
        fetch('index.php?route=api/user/notifications/all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.notifications);
                }
            });
    }

    function renderNotifications(notifications) {
        const container = document.getElementById('notification-list');
        container.innerHTML = '';

        if (notifications.length === 0) {
            container.innerHTML = '<p>No notifications found.</p>';
            return;
        }

        notifications.forEach(n => {
            const div = document.createElement('div');
            div.className = `notification ${n.seen ? '' : 'unseen'}`;

            const message = document.createElement('p');
            message.innerHTML = `<strong>${n.created_at}</strong> - ${n.message}`;
            div.appendChild(message);

            if (!n.seen) {
                const btn = document.createElement('button');
                btn.textContent = 'Mark as seen';
                btn.addEventListener('click', () => markAsSeen(n.id, btn));
                div.appendChild(btn);
            }

            container.appendChild(div);
        });
    }

    function markAsSeen(id, btn) {
        fetch('index.php?route=api/user/notifications/mark_seen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}&notification_id=${id}`
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    btn.remove();
                    const container = btn.closest('.notification');
                    if (container) {
                        container.classList.remove('unseen');
                    }
                    updateNotificationCount();
                }
            });
    }


    function updateNotificationCount() {
        fetch('index.php?route=api/user/notifications/unseen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${CSRF_TOKEN}`
        })
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notif-count');
                if (badge) badge.innerText = data.length > 0 ? data.length : '';
            });
    }

    fetchNotifications();
}
