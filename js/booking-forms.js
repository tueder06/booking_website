import { activeCities } from './data.js';
import { removeAccents, showError, clearErrors } from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const autocompleteList = document.getElementById('autocomplete-list');
    const discoverFilterForm = document.getElementById('filter-form');
    const homeForm = document.getElementById('home-form');
    const activeForm = homeForm || discoverFilterForm;

    const btnSubmit = document.getElementById('home-submit');

    const checkInInput = document.getElementById('check-in');
    const checkOutInput = document.getElementById('check-out');

    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');

    if (searchParam && searchInput) {
        searchInput.value = searchParam;
        searchInput.dispatchEvent(new Event('input')); 
    }

    function enableHomeSubmit() {
        if (homeForm && btnSubmit) {
            activeForm.addEventListener('input', function() {
                const isSearchFilled = searchInput && searchInput.value.trim() !== '';
                const isCheckInFilled = checkInInput && checkInInput.value !== '';
                const isCheckOutFilled = checkOutInput && checkOutInput.value !== '';
                btnSubmit.disabled = !(isSearchFilled && isCheckInFilled && isCheckOutFilled);
            });
        }
    }

    if (checkInInput && checkOutInput) {
        const today = new Date().toISOString().split('T')[0];
        checkInInput.setAttribute('min', today);
        checkOutInput.setAttribute('min', today);
        checkInInput.addEventListener('change', function() {
            checkOutInput.setAttribute('min', this.value);

            if (checkOutInput.value && checkOutInput.value < this.value) {
                checkOutInput.value = '';
                enableHomeSubmit();
            }
        });
        checkOutInput.addEventListener('change', enableHomeSubmit);
    }

    let searchCities = [];
    if (typeof activeCities !== 'undefined') {
        searchCities = activeCities;
    }

    if (searchInput && autocompleteList) {
        searchInput.addEventListener('input', function() {
            const val = this.value;
            
            autocompleteList.innerHTML = '';
            
            if (!val) { return false; }

            const cleanVal = removeAccents(val.toLowerCase());
            searchCities.forEach(city => {
                const cleanCity = removeAccents(city.toLowerCase());

                if (cleanCity.includes(cleanVal)) {
                    const b = document.createElement('div');
                    
                    const matchIndex = cleanCity.indexOf(cleanVal);
                    
                    const matchText = city.substr(matchIndex, val.length);
                    
                    b.innerHTML = city.substring(0, matchIndex) 
                                + "<strong>" + matchText + "</strong>" 
                                + city.substring(matchIndex + val.length);
                    
                    b.innerHTML += `<input type='hidden' value='${city}'>`;

                    b.addEventListener('click', function() {
                        searchInput.value = this.getElementsByTagName('input')[0].value;
                        autocompleteList.innerHTML = '';
                    });
                    autocompleteList.appendChild(b);
                }
            });
            enableHomeSubmit();
        });

        document.addEventListener('click', function (e) {
            if (e.target !== searchInput && e.target !== autocompleteList) {
                autocompleteList.innerHTML = '';
            }
        });
    }


    const priceSlider = document.getElementById('price-slider');
    const priceInput = document.getElementById('price');

    if (priceSlider && priceInput) {
        priceInput.value = priceSlider.value;

        priceSlider.addEventListener('input', function() {
            priceInput.value = this.value;
        });
    }

    

    if (activeForm) {
        activeForm.addEventListener('submit', function(event) {
            let isValid = true;
            clearErrors(activeForm);

            const typedDestination = searchInput ? searchInput.value.trim() : "";
            if (searchInput && searchInput.value.trim() === '') {
                isValid = showError(searchInput, 'search-error', 'Please enter a destination.');
            } else {
                const cityExists = searchCities.some(city => 
                    city.toLowerCase() === typedDestination.toLowerCase()
                );

                if (!cityExists) {
                    isValid = showError(searchInput, 'search-error', 'Destination not available.');
                }
            }

            const checkIn = document.getElementById('check-in');
            const checkOut = document.getElementById('check-out');
            if (checkIn && checkIn.value === '') {
                isValid = showError(checkIn, 'checkin-error', 'Check-in date is required.');
            }
            if (checkOut && checkOut.value === '') {
                isValid = showError(checkOut, 'checkout-error', 'Check-out date is required.');
            }
            if (checkIn && checkOut && checkIn.value !== '' && checkOut.value !== '') {
                if (checkOut.value < checkIn.value) {
                    isValid = showError(checkOut, 'checkout-error', 'Check-out cannot be before Check-in.');
                }
            }

            if(discoverFilterForm) {
                const propertyContainer = document.getElementById('property-container');
                const propertyCheckboxes = propertyContainer.querySelectorAll('input[type="checkbox"]');
                const isAnyPropertySelected = Array.from(propertyCheckboxes).some(cb => cb.checked);

                if (!isAnyPropertySelected) {
                    isValid = showError(propertyContainer, 'property-error', 'Please select at least one property type.');
                }

                const selectedMeal = discoverFilterForm.querySelector('input[name="catering"]:checked');
                const mealContainer = document.getElementById('meal-container');

                if (!selectedMeal) {
                    isValid = showError(mealContainer, 'meal-error', 'Please select a meal plan.');
                }
                
                const facilitiesSelect = document.getElementById('facilities');
                if (facilitiesSelect) {
                    if (facilitiesSelect.selectedOptions.length === 0) {
                        isValid = showError(facilitiesSelect, 'facilities-error', 'Please select at least one facility.');
                    }
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});