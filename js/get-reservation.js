document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('reservation-selector');
    const form = document.getElementById('manage-trip-form');
    if (!selector) return;

    let hasUnsavedChanges = false; 
    let currentReservationId = selector.value;

    function markAsChanged() {
        hasUnsavedChanges = true;
        const btnSave = document.getElementById('btn-save-trip');
        if (btnSave) btnSave.disabled = false;
    }

    document.getElementById('edit-checkin').addEventListener('change', markAsChanged);
    document.getElementById('edit-checkout').addEventListener('change', markAsChanged);
    document.getElementById('edit-toggle-box').addEventListener('change', markAsChanged);

    function loadTripData(reservationId) {
        if (!reservationId) return;

        document.getElementById('edit-hotel-name').textContent = "Loading...";
        document.getElementById('edit-hotel-city').textContent = "Loading...";
        const btnSave = document.getElementById('btn-save-trip');
        if (btnSave) btnSave.disabled = true;

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `includes/get_reservation.php?id=${reservationId}`, true);

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
                
            try {
                const data = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    if (data.success) {
                        const trip = data.trip;
                        
                        document.getElementById('edit-hotel-name').textContent = trip.hotel_name;
                        document.getElementById('edit-hotel-city').textContent = trip.city;
                        document.getElementById('edit-checkin').value = trip.check_in;
                        document.getElementById('edit-checkout').value = trip.check_out;
                        document.getElementById('edit-total-price').textContent = `Total: ${trip.total_price} RON`;
                        
                        const checkBox = document.getElementById('edit-toggle-box');
                        checkBox.checked = false;
                        const checkBoxLabel = document.getElementById('edit-toggle-label');
                        const status = document.getElementById('status');
                        status.textContent = trip.status;
                        if (trip.status.trim() === "Confirmed") {
                            status.classList.add("confirmed");
                            status.classList.remove("cancelled");
                            checkBox.classList.add("confirm");
                            checkBox.classList.remove("cancel");
                            checkBoxLabel.classList.add("confirm");
                            checkBoxLabel.classList.remove("cancel");
                            checkBoxLabel.textContent = "Confirm Booking";
                        } else {
                            status.classList.add("cancelled");
                            status.classList.remove("confirmed");
                            checkBox.classList.add("cancel");
                            checkBox.classList.remove("confirm");
                            checkBoxLabel.classList.add("cancel");
                            checkBoxLabel.classList.remove("confirm");
                            checkBoxLabel.textContent = "Cancel Booking";
                        }

                        const imgEl = document.getElementById('edit-hotel-img');
                        if (imgEl) {
                            imgEl.src = trip.image ? trip.image : 'images/placeholder-home.webp';
                        }

                        document.getElementById('edit-res-id').value = trip.id;
                        
                        if (btnSave) btnSave.disabled = false;
                    } else {
                        document.getElementById('edit-hotel-name').textContent = "Loading error.";
                        console.error("Backend Error:", data.message);
                    }
                } else {
                    document.getElementById('edit-hotel-name').textContent = data.message;
                    console.error("Error: Server returned status", xhr.status);
                } 
            } catch (e) {
                document.getElementById('edit-hotel-name').textContent = "Data parse error.";
                console.error("JSON Parse Error:", e);
            }
        };

        xhr.onerror = function () {
            document.getElementById('edit-hotel-name').textContent = "Connection error.";
            console.error("Error: Network request failed.");
        };

        xhr.send();
    }

    function saveModifications(onSuccessCallback, onFailCallback) {
        const resId = document.getElementById('edit-res-id').value;
        const checkin = document.getElementById('edit-checkin').value;
        const checkout = document.getElementById('edit-checkout').value;
        const isChecked = document.getElementById('edit-toggle-box').checked;
        const csrfToken = document.getElementById('csrf-token').value;
        const currentStatus = document.getElementById('status').textContent.trim();

        let targetStatus = currentStatus;
        if (isChecked) {
            targetStatus = (currentStatus === "Confirmed") ? "Cancelled" : "Confirmed";
        }

        const payload = JSON.stringify({
            id: resId,
            check_in: checkin,
            check_out: checkout,
            cancel: isChecked,
            new_status: targetStatus,
            csrf_token: csrfToken
        });

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/update_reservation.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
                
            try {
                const data = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    if (data.success) {
                        hasUnsavedChanges = false;
                        document.getElementById('btn-save-trip').disabled = true;
                        alert("Changes saved successfully.");
                        
                        if (onSuccessCallback) onSuccessCallback();
                    } else {
                        alert("Error: " + data.message);
                        if (onFailCallback) onFailCallback();
                    }
                } else {
                    alert(data.message);
                    if (onFailCallback) onFailCallback();
                }
            } catch (e) {
                alert("Data parse error.");
                console.error("JSON Parse Error:", e);
                if (onFailCallback) onFailCallback();
            }
        };

        xhr.onerror = function () {
            alert("Connection error.");
            if (onFailCallback) onFailCallback();
        };

        xhr.send(payload);
    }

    loadTripData(selector.value);

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveModifications(
                function() {
                    loadTripData(currentReservationId);
                },
                function() {}
            );
        });
    }

    selector.addEventListener('change', function() {
        const nextId = this.value;

        if (hasUnsavedChanges) {
            const wantToSave = confirm("You left some changes unsaved. Do you want to save them?");
            
            if (wantToSave) {
                saveModifications(
                    function() {
                        currentReservationId = nextId;
                        loadTripData(nextId);
                    },
                    function() {
                        hasUnsavedChanges = false;
                        currentReservationId = nextId;
                        loadTripData(nextId);
                    });
            } else {
                hasUnsavedChanges = false;
                currentReservationId = nextId;
                loadTripData(nextId);
            }
        } else {
            currentReservationId = nextId;
            loadTripData(nextId);
        }
    });
});