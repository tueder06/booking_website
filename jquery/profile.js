import { locationData } from '../js/data.js';

$(document).ready(function() {
    const $country = $('#country');
    const $city = $('#city');

    if ($country.length && $city.length && typeof locationData !== 'undefined') {
        const countries = Object.keys(locationData);
        $country.html(countries.map(c => `<option value="${c}">${c}</option>`).join(''));
        
        $country.val('Romania');

        function updateCities(selectedCountry) {
            const cities = locationData[selectedCountry] || [];
            $city.html(cities.map(city => `<option value="${city}">${city}</option>`).join(''));
        }

        updateCities($country.val());
        $city.val('Cluj-Napoca');

        $country.on('change', function() {
            updateCities($(this).val());
            $city.prop('disabled', false).focus();
        });
    }

    $('.profile-form .btn-icon').on('click', function() {
        const $field = $(this).prev();

        if ($field.is('[readonly]') || $field.is(':disabled')) {
            $field.removeAttr('readonly').prop('disabled', false).focus();
            
            if ($field.is('input')) {
                const val = $field.val();
                $field.val('').val(val);
            }
        }
    });

    $('.profile-form input, .profile-form select').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            validateAndSave($(this));
        }
    }).on('blur', function() {
        if (!$(this).is('[readonly]') && !$(this).is(':disabled')) {
            validateAndSave($(this));
        }
    });

    function validateAndSave($field) {
        let isValid = true;
        let errorMessage = "";
        const value = $field.val().trim();
        const fieldId = $field.attr('id');

        switch(fieldId) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value === "") {
                    isValid = false;
                    errorMessage = "Email cannot be empty.";
                } 
                else if (!emailRegex.test(value)) { 
                    isValid = false;
                    errorMessage = "Please enter a valid email address.";
                }
                break;
                
            case 'password':
                if (value === "") {
                    isValid = false;
                    errorMessage = "Password cannot be empty.";
                } 
                else if (value.length < 8) {
                    isValid = false;
                    errorMessage = "Password must be at least 8 characters long.";
                }
                break;
                
            case 'firstname':
            case 'lastname':
                if (value === "") {
                    isValid = false;
                    errorMessage = "This field cannot be empty.";
                } 
                else if (value.length < 2) {
                    isValid = false;
                    errorMessage = "Must be at least 2 characters long.";
                }
                break;
                
            case 'phone':
                const phoneRegex = /^\+?[0-9]{7,15}$/;
                if (value === "") {
                    isValid = false;
                    errorMessage = "Phone number cannot be empty.";
                } 
                else if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = "Please enter a valid phone number.";
                }
                break;

            case 'birthdate':
                const selectedDate = new Date(value);
                const today = new Date();
                let age = today.getFullYear() - selectedDate.getFullYear();
                const m = today.getMonth() - selectedDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < selectedDate.getDate())) age--;

                if (value === "") {
                    isValid = false;
                    errorMessage = "Birthdate cannot be empty.";
                } 
                else if (selectedDate > today) {
                    isValid = false;
                    errorMessage = "Are you a time traveler? Birthdate cannot be in the future.";
                } 
                else if (age < 18) {
                    isValid = false;
                    errorMessage = "You must be at least 18 years old to make reservations.";
                } 
                else if (age > 120) {
                    isValid = false;
                    errorMessage = "Please enter a valid birthdate.";
                }
                break;
        }


        if (isValid) {
            $field.val(value); 
            $field.prop('defaultValue', value);
            $field.is('select') ? $field.prop('disabled', true) : $field.attr('readonly', true);
            $field.css({'transition': 'background-color 0.3s', 'background-color': '#d1fae5'})
                  .delay(400)
                  .queue(function(next) {
                      $(this).css('background-color', '');
                      next();
                  });
            
        } else {
            alert(errorMessage); 
            $field.val($field.prop('defaultValue')); 
            $field.is('select') ? $field.prop('disabled', true) : $field.attr('readonly', true);
        }
    }
});