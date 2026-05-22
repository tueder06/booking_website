$(document).ready(function() {
    const $selector = $('#reservation-selector');
    const $form = $('#manage-trip-form');
    
    if (!$selector.length) return;

    let hasUnsavedChanges = false;
    let currentReservationId = $selector.val();

    function markAsChanged() {
        hasUnsavedChanges = true;
        $('#btn-save-trip').prop('disabled', false);
    }

    $('#edit-checkin, #edit-checkout, #edit-toggle-box').on('change', markAsChanged);

    function loadTripData(reservationId) {
        if (!reservationId) return;

        $('#edit-hotel-name, #edit-hotel-city').text("Loading...");
        $('#btn-save-trip').prop('disabled', true);

        $.ajax({
            url: `includes/get_reservation.php?id=${reservationId}`,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    const trip = data.trip;
                    
                    $('#edit-hotel-name').text(trip.hotel_name);
                    $('#edit-hotel-city').text(trip.city);
                    $('#edit-checkin').val(trip.check_in);
                    $('#edit-checkout').val(trip.check_out);
                    $('#edit-total-price').text(`Total: ${trip.total_price} RON`);
                    
                    const $checkBox = $('#edit-toggle-box');
                    $checkBox.prop('checked', false);
                    
                    const $checkBoxLabel = $('#edit-toggle-label');
                    const $status = $('#status');
                    $status.text(trip.status);
                    
                    if (trip.status.trim() === "Confirmed") {
                        $status.addClass("confirmed").removeClass("cancelled");
                        $checkBox.addClass("confirm").removeClass("cancel");
                        $checkBoxLabel.addClass("confirm").removeClass("cancel").text("Confirm Booking");
                    } else {
                        $status.addClass("cancelled").removeClass("confirmed");
                        $checkBox.addClass("cancel").removeClass("confirm");
                        $checkBoxLabel.addClass("cancel").removeClass("confirm").text("Cancel Booking");
                    }

                    const $imgEl = $('#edit-hotel-img');
                    if ($imgEl.length) {
                        $imgEl.attr('src', trip.image ? trip.image : 'images/placeholder-home.webp');
                    }

                    $('#edit-res-id').val(trip.id);
                    $('#btn-save-trip').prop('disabled', false);
                } else {
                    $('#edit-hotel-name').text("Loading error.");
                    console.error("Backend Error:", data.message);
                }
            },
            error: function(xhr, status, error) {
                $('#edit-hotel-name').text("Connection or parse error.");
                console.error("AJAX Error:", error);
            }
        });
    }

    function saveModifications(onSuccessCallback, onFailCallback) {
        const resId = $('#edit-res-id').val();
        const checkin = $('#edit-checkin').val();
        const checkout = $('#edit-checkout').val();
        const isChecked = $('#edit-toggle-box').prop('checked');
        const csrfToken = $('#csrf-token').val();
        const currentStatus = $('#status').text().trim();
        
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

        $.ajax({
            url: 'includes/update_reservation.php',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            data: payload,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    hasUnsavedChanges = false;
                    $('#btn-save-trip').prop('disabled', true);
                    alert("Changes saved successfully.");
                    
                    if (onSuccessCallback) onSuccessCallback();
                } else {
                    alert("Error: " + data.message);
                    if (onFailCallback) onFailCallback();
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = "Connection error.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
                if (onFailCallback) onFailCallback();
            }
        });
    }

    loadTripData(currentReservationId);

    if ($form.length) {
        $form.on('submit', function(e) {
            e.preventDefault();
            saveModifications(
                function() {
                    loadTripData(currentReservationId);
                },
                function() {}
            );
        });
    }

    $selector.on('change', function() {
        const nextId = $(this).val();

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
                    }
                );
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