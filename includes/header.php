<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<input type="checkbox" id="menu-toggle" class="menu-checkbox">
<label for="menu-toggle" class="side-menu-icon">☰</label>
<header class="site-header">
    <div class="header-top">
        <ul class="utility-nav">
            <li class="logo-text">Booking</li>
            <li class="logo-track">
                <img src="images/suitcase.png" class="animate" alt="Booking logo" height="40">
            </li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php" class="auth-link">My Account</a></li>
                <li><a href="logout.php" class="auth-link">Log out</a></li>
            <?php else: ?>
                <li><a href="login.php" class="auth-link">Log in</a></li>
                <li><a href="sign-up.php" class="auth-link">Sign up</a></li>
            <?php endif; ?>
            </ul>
    </div>
    <nav class="header-bottom">
        <ul class="main-nav">
            <li><a class="nav-link" href="index.php"><span class="menu-icon home-icon"></span>Home</a></li>
            <li><a class="nav-link" href="discover.php"><span class="menu-icon discover-icon"></span>Discover</a></li>
            <li><a class="nav-link" href="compare.php"><span class="menu-icon compare-icon"></span>Info</a></li>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'owner'): ?>
                <li><a class="nav-link" href="accomodations.php"><span class="menu-icon properties-icon"></span>My Properties</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>