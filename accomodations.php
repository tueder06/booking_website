<?php
require_once 'includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property_id'])) {
    $delete_id = $_POST['delete_property_id'];
    
    $img_sql = "SELECT image_path FROM accomodations WHERE id = ? AND owner_id = ?";
    $old_image_path = null;
    
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($img_sql);
        $stmt->execute([$delete_id, $owner_id]);
        $old_image_path = $stmt->fetchColumn();
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($img_sql)) {
            $stmt->bind_param("ii", $delete_id, $owner_id);
            $stmt->execute();
            $stmt->bind_result($old_image_path);
            $stmt->fetch();
            $stmt->close();
        }
    }

    $delete_sql = "DELETE FROM accomodations WHERE id = ? AND owner_id = ?";
    $deleted = false;

    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($delete_sql);
        $deleted = $stmt->execute([$delete_id, $owner_id]);
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($delete_sql)) {
            $stmt->bind_param("ii", $delete_id, $owner_id);
            $deleted = $stmt->execute();
            $stmt->close();
        }
    }

    if ($deleted) {
        $success_msg = "Property deleted successfully.";
        
        if (!empty($old_image_path) && file_exists($old_image_path)) {
            unlink($old_image_path);
        }
    } else {
        $error_msg = "Failed to delete property.";
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_msg = "Property saved successfully!";
}

$properties = [];
$fetch_sql = "SELECT * FROM accomodations WHERE owner_id = ? ORDER BY id DESC";

if ($conn instanceof PDO) {
    $stmt = $conn->prepare($fetch_sql);
    $stmt->execute([$owner_id]);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($conn instanceof mysqli) {
    if ($stmt = $conn->prepare($fetch_sql)) {
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $stmt->close();
    }
}

$total_properties = count($properties);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/suitcase.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/accomodations.css">
    <script type="module" src="js/accomodations.js"></script>
    <title>Manage Properties</title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="dashboard-layout container">
        
        <div class="owner-header">
            <h2>Manage properties</h2>
            <a href="property_form.php?action=add" class="btn-primary add-new-btn">+ Add New Property</a>
        </div>

        <section class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Properties</h3>
                <p class="stat-value"><?= $total_properties ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Active Bookings</h3>
                <p class="stat-value">14</p> </div>
            
            <div class="stat-card accent">
                <h3>Est. Revenue</h3>
                <p class="stat-value">3,450 RON</p> <span class="stat-trend" style="color: white; opacity: 0.8;">This month</span>
            </div>
        </section>

        <section class="my-properties-section">
            <div class="section-title-wrapper">
                <h2>My Properties</h2>
            </div>
            
            <?php if ($success_msg): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <div class="properties-grid">
                
                <?php if (empty($properties)): ?>
                    <div class="empty-state">
                        <img src="images/suitcase.png" alt="Empty">
                        <h3>You don't have any properties yet.</h3>
                    </div>
                <?php else: ?>

                    <?php foreach ($properties as $prop): ?>
                        <article class="property-card">
                            <div class="property-image-container">
                                <img src="<?= htmlspecialchars($prop['image_path']) ?>" alt="<?= htmlspecialchars($prop['name']) ?>" onerror="this.src='images/placeholder-home.png'">
                            </div>
                            
                            <div class="property-info">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <h3><?= htmlspecialchars($prop['name']) ?></h3>
                                    <span class="property-rating"> <?= htmlspecialchars($prop['rating']) ?></span>
                                </div>
                                
                                <p class="location"><?= htmlspecialchars($prop['city']) ?> (<?= htmlspecialchars($prop['distance']) ?>km from center)</p>
                                <p class="property-type"><?= htmlspecialchars($prop['property_type']) ?> | <?= htmlspecialchars($prop['room_type']) ?></p>
                                
                                <p class="price"><strong><?= htmlspecialchars($prop['price']) ?> RON</strong> <span class="per-night">/ night</span></p>
                                
                                <div class="property-actions">
                                    <a href="property_form.php?action=edit&id=<?= $prop['id'] ?>" class="btn-action edit">
                                        <img src="images/modify.png" alt="Edit" width="16" onerror="this.style.display='none'"> Edit
                                    </a>
                                    
                                    <form action="accomodations.php" method="POST" class="delete-form">
                                        <input type="hidden" name="delete_property_id" value="<?= $prop['id'] ?>">
                                        <button type="submit" class="btn-action delete">
                                            <img src="images/delete-icon.png" alt="Del" width="16" onerror="this.style.display='none'"> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>
        
    </main>
    
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>