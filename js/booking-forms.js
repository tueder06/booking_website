import { activeCities } from './data.js';
import { removeAccents, showError, clearErrors } from './utils.js';
import { fetchProperties } from './locations.js'; 

const searchInput = document.getElementById('search');
const autocompleteList = document.getElementById('autocomplete-list');
const discoverFilterForm = document.getElementById('filter-form');
const homeForm = document.getElementById('home-form');
const activeForm = homeForm || discoverFilterForm;
const btnSubmit = document.getElementById('home-submit');
const checkInInput = document.getElementById('check-in');
const checkOutInput = document.getElementById('check-out');

function enableHomeSubmit() {
    if (homeForm && btnSubmit) {
        const isSearchFilled = searchInput && searchInput.value.trim() !== '';
        const isCheckInFilled = checkInInput && checkInInput.value !== '';
        const isCheckOutFilled = checkOutInput && checkOutInput.value !== '';
        btnSubmit.disabled = !(isSearchFilled && isCheckInFilled && isCheckOutFilled);
    }
}

if (activeForm && btnSubmit) {
    activeForm.addEventListener('input', enableHomeSubmit);
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
                            + city.substring(matchIndex + val.length)
                            + `<input type='hidden' value='${city}'>`;

                b.addEventListener('click', function() {
                    searchInput.value = this.getElementsByTagName('input')[0].value;
                    autocompleteList.innerHTML = '';
                    enableHomeSubmit();
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
        if (activeForm === discoverFilterForm) {
            event.preventDefault();
        }

        let isValid = true;
        clearErrors(activeForm);

        const typedDestination = searchInput ? searchInput.value.trim() : "";

        if (typedDestination === '') {
            if (activeForm !== discoverFilterForm) { 
                isValid = showError(searchInput, 'search-error', 'Please enter a destination.');
            }
        } else {
            const cityExists = searchCities.some(city => city.toLowerCase() === typedDestination.toLowerCase());
            if (!cityExists) {
                isValid = showError(searchInput, 'search-error', 'Destination not available.');
            }
        }

        const now = new Date();
        const stringToday = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

        if (checkInInput && checkInInput.value !== '') {
            if (checkInInput.value < stringToday) {
                isValid = showError(checkInInput, 'checkin-error', 'Check-in cannot be in the past.');
            }
        }
        
        if (checkInInput && checkOutInput && checkInInput.value !== '' && checkOutInput.value !== '') {
            if (checkOutInput.value < checkInInput.value) {
                isValid = showError(checkOutInput, 'checkout-error', 'Check-out cannot be before Check-in.');
            }
        } else if (activeForm !== discoverFilterForm && (!checkInInput.value || !checkOutInput.value)) {
            if (!checkInInput.value) isValid = showError(checkInInput, 'checkin-error', 'Check-in is required.');
            if (!checkOutInput.value) isValid = showError(checkOutInput, 'checkout-error', 'Check-out is required.');
        }

        if (activeForm === discoverFilterForm) {
            if (priceSlider && (priceSlider.value < 0 || priceSlider.value > 2000)) {
                isValid = showError(priceSlider, 'price-error', 'Invalid price range.');
            }
        }

        if (!isValid) {
            if (activeForm !== discoverFilterForm) {
                event.preventDefault();
            }
            return;
        }

        if (activeForm === discoverFilterForm) {
            fetchProperties(1, true);
        }
    });
}

if (discoverFilterForm) {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString() !== "") {
        for (const [key, value] of urlParams) {
            const inputs = discoverFilterForm.querySelectorAll(`[name="${key}"]`);
            
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.value === value) input.checked = true;
                } else {
                    input.value = value;
                }
            });
        }
    }

    if (searchInput && searchInput.value) {
        searchInput.dispatchEvent(new Event('input'));
    }
    
    fetchProperties(1, true); 
}