document.addEventListener('DOMContentLoaded', function() {
    function confirmDelete(formElement) {
        if (confirm("Are you sure you want to delete this property? This action cannot be undone.")) {
            formElement.submit();
        }
    }

    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            confirmDelete(this);
        });
    });
});