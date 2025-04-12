
// Confirm before marking as delivered
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const status = this.querySelector('select[name="status"]').value;
        if (status === 'delivered') {
            if (!confirm('Are you sure you want to mark this order as delivered? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });
});