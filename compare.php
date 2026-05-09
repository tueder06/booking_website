<?php
require_once 'includes/init.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/suitcase.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/compare.css">
    <!-- <script type="module" src="js/compare.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/compare.js"></script>
    <title>Compare Rooms - Booking</title>
</head>
<body class="dashboard-layout">
    <?php require_once 'includes/header.php'; ?>
    <main class="container">
        <div class="page-header">
            <h1>Room Benefits Comparison</h1>
            <p>Compare facilities, capacities, and pricing to find your perfect stay.</p>
        </div>

        <section class="table-container">
            <div class="page-header">
                <h2>Room Benefits Comparison</h2>
            </div>
            
            <table id="compare-table" class="data-matrix">
                </table>
            <!-- <table id="compare-table" class="data-matrix vertical-mode">
                </table> -->
        </section>

        <section class="details-section">
            <h2>Important Stay Details</h2>
            <p>To avoid future issues, please read the following policies carefully:</p>

            <ul class="compare-tree">
                <li>Standard & Deluxe Policies
                    <ul class="nested-sublist">
                        <li>Cancellation: Free up to 24h before arrival.</li>
                        <li>Breakfast: Included in the room price if offered.</li>
                        <li>Extra beds: Not available for these room types.</li>
                    </ul>
                </li>
                <li>Family & Penthouse Extras
                    <ul class="nested-sublist">
                        <li>Airport Transfer: Optional (+100 RON).</li>
                        <li>Kitchenette: Fully stocked with basic spices and utensils.</li>
                        <li>Laundry: 24/7 express service available.</li>
                    </ul>
                </li>
                <li>General Hotel Rules
                    <ul class="nested-sublist">
                        <li>Quiet hours: Between 22:00 and 08:00.</li>
                        <li>Smoking: Prohibited in all indoor areas (balcony only).</li>
                        <li>Parking: Subject to availability upon check-in.</li>
                    </ul>
                </li>
            </ul>
        </section>
    </main>
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>