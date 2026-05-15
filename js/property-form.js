import { activeCities } from "./data.js";
import { showError, clearErrors } from "./utils.js";

document.addEventListener("DOMContentLoaded", () => {
    const citySelect = document.getElementById("city");
    if (citySelect) {
        const savedCity = citySelect.getAttribute("data-selected");
        activeCities.forEach(city => {
            const option = document.createElement("option");
            option.value = city;
            option.textContent = city;
            if (city === savedCity) option.selected = true;
            citySelect.appendChild(option);
        });
    }

    const propertyForm = document.querySelector(".property-form");

    if (propertyForm) {
        propertyForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            clearErrors(propertyForm);

            const name = document.getElementById('name');
            if (name && name.value.trim() === '') {
                isValid = showError(name, 'name-error', 'Property Name is required.') && isValid;
            }

            const cityField = document.getElementById('city');
            if (cityField && cityField.value === '') {
                isValid = showError(cityField, 'city-error', 'Please select a city.') && isValid;
            }

            const distance = document.getElementById('distance');
            if (distance && (distance.value === '' || parseFloat(distance.value) < 0 || parseFloat(distance.value) > 100)) {
                isValid = showError(distance, 'distance-error', 'Distance from center must be between 0 and 100 km.') && isValid;
            }

            const roomType = document.getElementById('room-type');
            if (roomType && roomType.value === '') {
                isValid = showError(roomType, 'room-type-error', 'Please select a room type.') && isValid;
            }

            const propertyType = document.getElementById('property-type');
            if (propertyType && propertyType.value === '') {
                isValid = showError(propertyType, 'property-type-error', 'Please select a property type.') && isValid;
            }

            const price = document.getElementById('price');
            if (price && (price.value === '' || parseFloat(price.value) <= 0)) {
                isValid = showError(price, 'price-error', 'Please enter a valid price greater than 0.') && isValid;
            }

            const rating = document.getElementById('rating');
            if (rating && (rating.value === '' || parseFloat(rating.value) < 1 || parseFloat(rating.value) > 10)) {
                isValid = showError(rating, 'rating-error', 'Rating must be between 1 and 10.') && isValid;
            }

            const stars = document.getElementById('stars');
            if (stars && (stars.value === '' || parseInt(stars.value) < 1 || parseInt(stars.value) > 5)) {
                isValid = showError(stars, 'stars-error', 'Stars must be between 1 and 5.') && isValid;
            }

            const mealPlanRadios = document.querySelectorAll('input[name="meal-plan"]');
            const mealPlanFirst = mealPlanRadios[0];
            let mealPlanSelected = false;
            mealPlanRadios.forEach(radio => { if (radio.checked) mealPlanSelected = true; });
            
            if (!mealPlanSelected && mealPlanRadios.length > 0) {
                isValid = showError(mealPlanFirst, 'meal-plan-error', 'Please select a meal plan.') && isValid;
            }

            const facilities = document.getElementById('facilities');
            if (facilities && facilities.selectedOptions.length === 0) {
                isValid = showError(facilities, 'facilities-error', 'Please select at least one facility.') && isValid;
            }

            const propertyImage = document.getElementById('property-image');
            const actionInput = document.querySelector('input[name="action"]');
            const isAddMode = actionInput && actionInput.value === 'add';

            if (propertyImage) {
                if (isAddMode && propertyImage.files.length === 0) {
                    isValid = showError(propertyImage, 'property-image-error', 'An image is required for new properties.') && isValid;
                } else if (propertyImage.files.length > 0) {
                    const file = propertyImage.files[0];
                    const maxSize = 2 * 1024 * 1024;
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                    if (file.size > maxSize) {
                        isValid = showError(propertyImage, 'property-image-error', 'The image must be smaller than 2MB.') && isValid;
                    } 
                    // else if (!allowedTypes.includes(file.type)) {
                    //    isValid = showError(propertyImage, 'property-image-error', 'Only JPG, PNG, and WEBP formats are allowed.') && isValid;
                    // }
                }
            }

            if (!isValid) {
                event.preventDefault();
                const firstError = document.querySelector('.input-error');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});