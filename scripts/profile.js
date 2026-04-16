import { locationData } from './data.js';

document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country');
    const citySelect = document.getElementById('city');

    if (countrySelect && citySelect && typeof locationData !== 'undefined') {
        const countries = Object.keys(locationData);
        countrySelect.innerHTML = countries.map(c => `<option value="${c}">${c}</option>`).join('');
        
        countrySelect.value = 'Romania';

        function updateCities(selectedCountry) {
            const cities = locationData[selectedCountry] || [];
            citySelect.innerHTML = cities.map(city => `<option value="${city}">${city}</option>`).join('');
        }

        updateCities(countrySelect.value);
        citySelect.value = 'Cluj-Napoca';

        countrySelect.addEventListener('change', function() {
            updateCities(this.value);

            citySelect.removeAttribute('disabled');
            citySelect.focus();
        });
    }

    const editButtons = document.querySelectorAll('.profile-form .btn-icon');
    const profileFields = document.querySelectorAll('.profile-form input, .profile-form select');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const field = this.previousElementSibling; 

            if (field.hasAttribute('readonly') || field.hasAttribute('disabled')) {
                field.removeAttribute('readonly');
                field.removeAttribute('disabled');
                
                field.focus();
                
                if(field.tagName.toLowerCase() === 'input') {
                    const val = field.value;
                    field.value = '';
                    field.value = val;
                }
            }
        });
    });

    profileFields.forEach(field => {
        field.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                validateAndSave(this);
            }
        });

        field.addEventListener('blur', function() {
            if (!this.hasAttribute('readonly') && !this.hasAttribute('disabled')) {
                validateAndSave(this);
            }
        });
    });

    function validateAndSave(field) {
        let isValid = true;
        let errorMessage = "";
        const value = field.value.trim();

        switch(field.id) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (value === "") {
                    isValid = false;
                    errorMessage = "Email cannot be empty.";
                } else if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = "Please enter a valid email address.";
                }
                break;
                
            case 'password':
                if (value === "") {
                    isValid = false;
                    errorMessage = "Password cannot be empty.";
                } else if (value.length < 8) {
                    isValid = false;
                    errorMessage = "Password must be at least 8 characters long.";
                }
                break;
                
            case 'firstname':
            case 'lastname':
                if (value === "") {
                    isValid = false;
                    errorMessage = "This field cannot be empty.";
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = "Must be at least 2 characters long.";
                }
                break;
                
            case 'phone':
                const phoneRegex = /^\+?[0-9]{7,15}$/;
                if (value === "") {
                    isValid = false;
                    errorMessage = "Phone number cannot be empty.";
                } else if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = "Please enter a valid phone number.";
                }
                break;

            case 'birthdate':
                const selectedDate = new Date(value);
                const today = new Date();
                
                let age = today.getFullYear() - selectedDate.getFullYear();
                const m = today.getMonth() - selectedDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < selectedDate.getDate())) {
                    age--;
                }

                if (value === "") {
                    isValid = false;
                    errorMessage = "Birthdate cannot be empty.";
                } else if (selectedDate > today) {
                    isValid = false;
                    errorMessage = "Are you a time traveler? Birthdate cannot be in the future.";
                } else if (age < 18) {
                    isValid = false;
                    errorMessage = "You must be at least 18 years old to make reservations.";
                } else if (age > 120) {
                    isValid = false;
                    errorMessage = "Please enter a valid birthdate.";
                }
                break;
        }

        if (isValid) {
            field.value = value; 
            field.defaultValue = value;

            if(field.tagName.toLowerCase() === 'select') {
                field.setAttribute('disabled', true);
            } else {
                field.setAttribute('readonly', true);
            }
            
            field.style.transition = 'background-color 0.3s';
            field.style.backgroundColor = '#d1fae5'; 
            setTimeout(() => field.style.backgroundColor = '', 400);
            
        } else {
            alert(errorMessage); 
            
            field.value = field.defaultValue; 
            
            if(field.tagName.toLowerCase() === 'select') {
                field.setAttribute('disabled', true);
            } else {
                field.setAttribute('readonly', true);
            }
        }
    }
});