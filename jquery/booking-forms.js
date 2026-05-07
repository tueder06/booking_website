import { activeCities } from '../scripts/data.js';
import { removeAccents, showError, clearErrors } from './utils.js';

$(document).ready(function() {
    const $searchInput = $('#search');
    const $autocompleteList = $('#autocomplete-list');
    const $discoverFilterForm = $('#filter-form');
    const $homeForm = $('#home-form');
    const $activeForm = $homeForm.length ? $homeForm : $discoverFilterForm;

    const $btnSubmit = $('#home-submit');

    const $checkInInput = $('#check-in');
    const $checkOutInput = $('#check-out');
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');

    if (searchParam && $searchInput.length) {
        $searchInput.val(searchParam).trigger('input'); 
    }

    function enableHomeSubmit() {
        if ($homeForm.length && $btnSubmit.length) {
            $activeForm.on('input', function() {
                const isSearchFilled = $searchInput.length && $searchInput.val().trim() !== '';
                const isCheckInFilled = $checkInInput.length && $checkInInput.val() !== '';
                const isCheckOutFilled = $checkOutInput.length && $checkOutInput.val() !== '';
                $btnSubmit.prop('disabled', !(isSearchFilled && isCheckInFilled && isCheckOutFilled));
            });
        }
    }

    if ($checkInInput.length && $checkOutInput.length) {
        const today = new Date().toISOString().split('T')[0];
        $checkInInput.attr('min', today);
        $checkOutInput.attr('min', today);
        $checkInInput.on('change', function() {
            $checkOutInput.attr('min', this.value);
            if ($checkOutInput.val() && $checkOutInput.val() < this.value) {
                $checkOutInput.val('');
                enableHomeSubmit();
            }
        });
        $checkOutInput.on('change', enableHomeSubmit);
    }

    let searchCities = [];
    if (typeof activeCities !== 'undefined') {
        searchCities = activeCities;
    }

    if ($searchInput.length && $autocompleteList.length) {
        $searchInput.on('input', function() {
            const val = $(this).val();

            $autocompleteList.empty();
            
            if (!val) { return false; }

            const cleanVal = removeAccents(val.toLowerCase());
            
            searchCities.forEach(city => {
                const cleanCity = removeAccents(city.toLowerCase());

                if (cleanCity.includes(cleanVal)) {
                    const matchIndex = cleanCity.indexOf(cleanVal);
                    const matchText = city.substr(matchIndex, val.length);
                    
                    const htmlContent = city.substring(0, matchIndex) 
                                    + "<strong>" + matchText + "</strong>" 
                                    + city.substring(matchIndex + val.length);

                    const $suggestion = $('<div>')
                        .html(htmlContent)
                        .append($('<input>', { type: 'hidden', value: city }));

                    $suggestion.on('click', function() {
                        $searchInput.val($(this).find('input').val());
                        $autocompleteList.empty();
                    });

                    $autocompleteList.append($suggestion);
                }
            });
            
            enableHomeSubmit();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).is($searchInput) && !$(e.target).closest($autocompleteList).length) {
                $autocompleteList.empty();
            }
        });
    }

    const $priceSlider = $('#price-slider');
    const $priceInput = $('#price');
    if ($priceSlider.length && $priceInput.length) {
        $priceInput.val($priceSlider.val());

        $priceSlider.on('input', function() {
            $priceInput.val($(this).val());
        });
    }

    if ($activeForm.length) {
        $activeForm.on('submit', function(e) {
            let isValid = true;
            clearErrors(this);

            const typedDestination = $searchInput ? $searchInput.val().trim() : "";
            if ($searchInput.length && typedDestination === '') {
                isValid = showError($searchInput, 'search-error', 'Please enter a destination.');
            } else {
                const cityExists = searchCities.some(city => 
                    city.toLowerCase() === typedDestination.toLowerCase()
                );

                if (!cityExists) {
                    isValid = showError($searchInput, 'search-error', 'Destination not available.');
                }
            }

            if ($checkInInput.length && $checkInInput.val() === '') {
                isValid = showError($checkInInput, 'check-in-error', 'Please select a check-in date.');
            }
            if ($checkOutInput.length && $checkOutInput.val() === '') {
                isValid = showError($checkOutInput, 'check-out-error', 'Please select a check-out date.');
            }
            if ($checkInInput.length && $checkOutInput.length && $checkInInput.val() !== '' && $checkOutInput.val() !== '') {
                if ($checkInInput.val() >= $checkOutInput.val()) {
                    isValid = showError($checkOutInput, 'check-out-error', 'Check-out date must be after check-in date.');
                }
            }

            if ($discoverFilterForm.length) {
                const $propertyContainer = $('#property-container');
                const isAnyPropertyChecked = $discoverFilterForm
                    .find('input[type="checkbox"]')
                    .is(':checked');
                
                if (!isAnyPropertyChecked) {
                    isValid = showError($propertyContainer, 'property-error', 'Please select at least one property type.');
                }

                if (!$discoverFilterForm.find('input[name="catering"]:checked').length) {
                    isValid = showError($('#meal-container'), 'meal-error', 'Please select a meal plan.');
                }

                const $facilitiesSelect = $('#facilities');
                if ($facilitiesSelect.length) {
                    if ($facilitiesSelect.val().length === 0) {
                        isValid = showError($facilitiesSelect, 'facilities-error', 'Please select at least one facility.');
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
            }   
        });
    }
});