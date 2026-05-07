export function removeAccents(str) {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

export function showError(inputElement, errorElementId, message) {
    const $errorElement = $(`#${errorElementId}`);
    const $input = $(inputElement);

    if ($input.length && $errorElement.length) {
        $input.addClass('input-error');
        $errorElement.text(message).show();
        return false;
    }
    return true;
}

export function clearErrors(formElement) {
    const $form = $(formElement);

    $form.find('input, select, textarea').each(function() {
        const $input = $(this);
        
        $input.removeClass('input-error');
        
        if ($input.attr('id') !== 'confirm-password' || $input.css('border-color') === 'rgb(217, 83, 79)') {
            $input.css('border-color', ''); 
        }
    });
    
    $form.find('.error-text').hide().text('');
}