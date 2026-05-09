import { locationData } from '../js/data.js';
import { showError, clearErrors } from './utils.js';

$(function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[0-9]{7,15}$/;

    function validateEmailField($emailInput, errorId) {
        if (!$emailInput.length) return true;
        const val = $emailInput.val().trim();
        
        if (val === '') {
            return showError($emailInput, errorId, 'Email is required.');
        } else if (!emailRegex.test(val)) {
            return showError($emailInput, errorId, 'Invalid email format.');
        }
        return true;
    }

    const $passwordInput = $('#password');
    const $confirmPasswordInput = $('#confirm-password');
    const $confirmErrorText = $('#confirm-password-error');

    function validatePasswordMatch() {
        if (!$confirmPasswordInput.length) return;

        const pwdVal = $passwordInput.val();
        const confirmVal = $confirmPasswordInput.val();
        const confirmDOM = $confirmPasswordInput[0];

        if (confirmVal === '') {
            $confirmErrorText.hide();
            $confirmPasswordInput.removeClass('input-error')
                .css('border-color', '#ccc');
            confirmDOM.setCustomValidity("");
            return;
        }

        if (pwdVal !== confirmVal) {
            confirmDOM.setCustomValidity("Passwords do not match");
            $confirmErrorText.text('Passwords do not match!').show();
            $confirmPasswordInput.addClass('input-error')
                .css('border-color', '#d9534f');
        } else {
            confirmDOM.setCustomValidity("");
            $confirmErrorText.hide();
            $confirmPasswordInput.removeClass('input-error')
                .css('border-color', '#5cb85c');
        }
    }

    if ($passwordInput.length && $confirmPasswordInput.length) {
        $passwordInput.add($confirmPasswordInput).on('input', validatePasswordMatch);
    }

    const $preferences = $('#bio');
    const $charCount = $('#char-count');

    if ($preferences.length && $charCount.length) {
        $preferences.on('input', function() {
            const currentLength = $(this).val().length;
            $charCount.text(`${currentLength} / 300`);

            if (currentLength > 300) {
                $charCount.css('color', '#d9534f');
                $preferences.css('border-color', '#d9534f');
            } else if (currentLength >= 250) {
                $charCount.css('color', '#f0ad4e');
                $preferences.css('border-color', '');
            } else {
                $charCount.css('color', '#666');
                $preferences.css('border-color', '');
            }
        });
    }

    const $signupForm = $('#signup-form');

    if ($signupForm.length) {
        $signupForm.on('submit', function(e) {
            let isValid = true;
            clearErrors(this); 

            const $fName = $('#first-name');
            if ($fName.length && $fName.val().trim() === '') {
                isValid = showError($fName, 'first-name-error', 'First Name is required.');
            }
            
            const $lName = $('#last-name');
            if ($lName.length && $lName.val().trim() === '') {
                isValid = showError($lName, 'last-name-error', 'Last Name is required.');
            }

            const $email = $('#email');
            if ($email.length && !validateEmailField($email, 'email-error')) {
                isValid = false;
            }

            const $phone = $('#phone');
            if ($phone.length) {
                const phoneVal = $phone.val().trim();
                if (phoneVal === '') {
                    isValid = showError($phone, 'phone-error', 'Phone is required.');
                } else if (!phoneRegex.test(phoneVal)) {
                    isValid = showError($phone, 'phone-error', 'Invalid phone.');
                }
            }

            const $countrySel = $('#country');
            const $citySel = $('#city');
            if ($countrySel.length && $countrySel.val() === '') {
                isValid = showError($countrySel, 'country-error', 'Please select a country.');
            }
            if ($citySel.length && $citySel.val() === '') {
                isValid = showError($citySel, 'city-error', 'Please select a city.');
            }

            const $birthdate = $('#birthdate');
            if ($birthdate.length) {
                const dobVal = $birthdate.val();
                if (dobVal === '') {
                    isValid = showError($birthdate, 'birthdate-error', 'Birthdate is required.');
                } else {
                    const today = new Date();
                    const dob = new Date(dobVal);
                    let age = today.getFullYear() - dob.getFullYear();
                    const m = today.getMonth() - dob.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                    
                    if (age < 18) {
                        isValid = showError($birthdate, 'birthdate-error', 'You must be at least 18 years old.');
                    }
                }
            }

            if ($passwordInput.length && $passwordInput.val().length < 8) {
                isValid = showError($passwordInput, 'main-password-error', 'Password must be at least 8 characters.');
            }

            if ($passwordInput.length && $confirmPasswordInput.length && $passwordInput.val() !== $confirmPasswordInput.val()) {
                isValid = showError($confirmPasswordInput, 'password-error', 'Passwords do not match!');
            }

            if ($preferences.length) {
                const textLength = $preferences.val().trim().length;
                if (textLength === 0) {
                    isValid = showError($preferences, 'bio-error', 'Please tell us your travel preferences.');
                } else if (textLength < 20) {
                    isValid = showError($preferences, 'bio-error', `Please enter at least 20 characters (you typed ${textLength}).`);
                } else if (textLength > 300) {
                    isValid = showError($preferences, 'bio-error', 'Your text is too long (maximum 300 characters).');
                }
            }

            if (!isValid) e.preventDefault(); 
        });
    }

    const $loginForm = $('#login-form');
    if ($loginForm.length) {
        $loginForm.on('submit', function(e) {
            let isValid = true;
            clearErrors(this);

            const $loginEmail = $('#login-email');
            if ($loginEmail.length && !validateEmailField($loginEmail, 'login-email-error')) isValid = false;

            const $loginPassword = $('#login-password');
            if ($loginPassword.length && $loginPassword.val().trim() === '') {
                isValid = showError($loginPassword, 'login-password-error', 'Password is required.');
            }

            if (!isValid) e.preventDefault(); 
        });
    }

    const $countrySelect = $('#country');
    const $citySelect = $('#city');
    if ($countrySelect.length && $citySelect.length && typeof locationData !== 'undefined') {
        $.each(locationData, function(country) {
            $countrySelect.append($('<option>', { value: country, text: country }));
        });

        $countrySelect.on('change', function() {
            const selectedCountry = $(this).val();
            $citySelect.html('<option value="">Select city</option>');
            
            if (selectedCountry !== "") {
                $citySelect.prop('disabled', false);
                
                $.each(locationData[selectedCountry], function(index, city) {
                    $citySelect.append($('<option>', { value: city, text: city }));
                });
            } else {
                $citySelect.prop('disabled', true);
            }
        });
    }
});