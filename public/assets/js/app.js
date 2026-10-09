// Notif bell polling
function loadNotif() {
    fetch(BASE_URL + 'notifikasi/unread')
        .then(r => r.json())
        .then(data => {
            const count = data.count || 0;
            const badge = document.getElementById('notifCount');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? '' : 'none';
            }
        }).catch(() => {});
}
setInterval(loadNotif, 60000);
document.addEventListener('DOMContentLoaded', loadNotif);

// Global confirm delete
document.addEventListener('click', e => {
    if (e.target.matches('[data-confirm]')) {
        if (!confirm(e.target.dataset.confirm)) e.preventDefault();
    }
});