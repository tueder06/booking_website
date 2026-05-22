document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('book-now-btn')) {
        const button = e.target;
        const accId = button.getAttribute('data-id');
        
        const checkInVal = document.getElementById('check-in').value;
        const checkOutVal = document.getElementById('check-out').value;
        const csrfToken = document.getElementById('csrf-token').value;

        if (!checkInVal || !checkOutVal) {
            alert("Please select both Check-in and Check-out dates in the sidebar.");
            return;
        }

        const checkInDate = new Date(checkInVal);
        const checkOutDate = new Date(checkOutVal);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (checkInDate < today) {
            alert("Check-in date cannot be in the past.");
            return;
        }
        if (checkOutDate <= checkInDate) {
            alert("Check-out must be at least one day after check-in.");
            return;
        }

        button.textContent = "Booking...";
        button.disabled = true;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/make_reservation.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                button.textContent = "Book Now";
                button.disabled = false;

                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        alert("Success! Reservation created.");
                    } else {
                        alert(res.message)
                    }
                } catch (error) {
                    console.log(error.message)
                    alert("Server error. Could not read the response.");
                }
            }
        };
        
        xhr.send(JSON.stringify({
            accommodation_id: accId,
            check_in: checkInVal,
            check_out: checkOutVal,
            csrf_token: csrfToken
        }));
    }
});