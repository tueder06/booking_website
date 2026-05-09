export function removeAccents(str) {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

export function showError(inputElement, errorElementId, message) {
    const errorElement = document.getElementById(errorElementId);
    if (inputElement && errorElement) {
        inputElement.classList.add('input-error');
        errorElement.innerText = message;
        errorElement.style.display = 'block';
        return false;
    }
    return true;
}

export function clearErrors(formElement) {
    const inputs = formElement.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('input-error');
        if (input.id !== 'confirm-password' || input.style.borderColor === 'rgb(217, 83, 79)') {
            input.style.borderColor = ''; 
        }
    });
    
    const errors = formElement.querySelectorAll('.error-text');
    errors.forEach(err => {
        err.style.display = 'none';
        err.innerText = '';
    });
}