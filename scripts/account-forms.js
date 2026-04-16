import { locationData } from './data.js';
import { showError, clearErrors } from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[0-9]{7,15}$/;

    function validateEmailField(emailInput, errorId) {
        if (!emailInput) return true;
        if (emailInput.value.trim() === '') {
            return showError(emailInput, errorId, 'Email is required.');
        } else if (!emailRegex.test(emailInput.value.trim())) {
            return showError(emailInput, errorId, 'Invalid email format.');
        }
        return true;
    }

    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const confirmErrorText = document.getElementById('confirm-password-error');

    function validatePasswordMatch() {
        if (!confirmPasswordInput) return;

        if (confirmPasswordInput.value === '') {
            confirmErrorText.style.display = 'none';
            confirmPasswordInput.classList.remove('input-error');
            confirmPasswordInput.style.borderColor = '#ccc';
            confirmPasswordInput.setCustomValidity("");
            return;
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity("Passwords do not match");
            confirmErrorText.innerText = 'Passwords do not match!';
            confirmErrorText.style.display = 'block';
            confirmPasswordInput.classList.add('input-error');
            confirmPasswordInput.style.borderColor = '#d9534f';
            
        } else {
            confirmPasswordInput.setCustomValidity("");
            confirmErrorText.style.display = 'none';
            confirmPasswordInput.classList.remove('input-error');
            confirmPasswordInput.style.borderColor = '#5cb85c';
        }
    }

    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', validatePasswordMatch);
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }

    const preferences = document.getElementById('bio');
    const charCount = document.getElementById('char-count');

    if (preferences && charCount) {
        preferences.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.innerText = `${currentLength} / 300`;

            if (currentLength > 300) {
                charCount.style.color = '#d9534f';
                preferences.style.borderColor = '#d9534f'; 
            } else if (currentLength >= 250) {
                charCount.style.color = '#f0ad4e';
                preferences.style.borderColor = '';
            } else {
                charCount.style.color = '#666';
                preferences.style.borderColor = ''; 
            }
        });
    }

    const signupForm = document.getElementById('signup-form');

    if (signupForm) {
        signupForm.addEventListener('submit', function(event) {
            let isValid = true;
            clearErrors(signupForm);

            const fName = document.getElementById('first-name');
            if (fName && fName.value.trim() === '') isValid = showError(fName, 'first-name-error', 'First Name is required.');
            
            const lName = document.getElementById('last-name');
            if (lName && lName.value.trim() === '') isValid = showError(lName, 'last-name-error', 'Last Name is required.');

            const email = document.getElementById('email');
            if (email && !validateEmailField(email, 'email-error')) isValid = false;

            const phone = document.getElementById('phone');
            if (phone) {
                if (phone.value.trim() === '') isValid = showError(phone, 'phone-error', 'Phone is required.');
                else if (!phoneRegex.test(phone.value.trim())) isValid = showError(phone, 'phone-error', 'Invalid phone.');
            }

            const countrySelect = document.getElementById('country');
            const citySelect = document.getElementById('city');
            if (countrySelect && countrySelect.value === '') isValid = showError(countrySelect, 'country-error', 'Please select a country.');
            if (citySelect && citySelect.value === '') isValid = showError(citySelect, 'city-error', 'Please select a city.');

            const birthdate = document.getElementById('birthdate');
            if (birthdate) {
                if (birthdate.value === '') {
                    isValid = showError(birthdate, 'birthdate-error', 'Birthdate is required.');
                } else {
                    const today = new Date();
                    const dob = new Date(birthdate.value);
                    let age = today.getFullYear() - dob.getFullYear();
                    const m = today.getMonth() - dob.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                    
                    if (age < 18) isValid = showError(birthdate, 'birthdate-error', 'You must be at least 18 years old.');
                }
            }

            if (passwordInput && passwordInput.value.length < 8) {
                isValid = showError(passwordInput, 'main-password-error', 'Password must be at least 8 characters.');
            }

            if (passwordInput && confirmPasswordInput && passwordInput.value !== confirmPasswordInput.value) {
                isValid = showError(confirmPasswordInput, 'password-error', 'Passwords do not match!');
            }

            if (preferences) {
                const textLength = preferences.value.trim().length;
                
                if (textLength === 0) {
                    isValid = showError(preferences, 'bio-error', 'Please tell us your travel preferences.');
                } else if (textLength < 20) {
                    isValid = showError(preferences, 'bio-error', `Please enter at least 20 characters (you typed ${textLength}).`);
                } else if (textLength > 300) {
                    isValid = showError(preferences, 'bio-error', 'Your text is too long (maximum 300 characters).');
                }
            }

            if (!isValid) event.preventDefault(); 
        });
    }

    const loginForm = document.getElementById('login-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            clearErrors(loginForm);

            const loginEmail = document.getElementById('login-email');
            if (loginEmail && !validateEmailField(loginEmail, 'login-email-error')) isValid = false;

            const loginPassword = document.getElementById('login-password');
            if (loginPassword && loginPassword.value.trim() === '') {
                isValid = showError(loginPassword, 'login-password-error', 'Password is required.');
            }

            if (!isValid) event.preventDefault(); 
        });
    }

    const countrySelect = document.getElementById('country');
    const citySelect = document.getElementById('city');

    if (countrySelect && citySelect) {
        for (let country in locationData) {
            let option = document.createElement('option');
            option.value = country;
            option.text = country;
            countrySelect.appendChild(option);
        }

        countrySelect.addEventListener('change', function() {
            const selectedCountry = this.value;
            citySelect.innerHTML = '<option value="">Select city</option>';
            
            if (selectedCountry !== "") {
                citySelect.disabled = false;
                locationData[selectedCountry].forEach(function(city) {
                    let option = document.createElement('option');
                    option.value = city;
                    option.text = city;
                    citySelect.appendChild(option);
                });
            } else {
                citySelect.disabled = true;
            }
        });
    }
});