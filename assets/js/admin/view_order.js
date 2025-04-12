// Toggle sidebar on mobile
document.getElementById('toggle-sidebar').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('-translate-x-full');
});

// Function to confirm delivery status
function confirmDeliveryStatus(event) {
    const statusSelect = document.getElementById('status');
    if (statusSelect.value === 'delivered') {
        if (!confirm('Are you sure this product has been delivered?\nOnce confirmed, the status cannot be changed.')) {
            event.preventDefault();
            return false;
        }
    }
    return true;
}
