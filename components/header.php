<?php
$baseUrl = '/currency%20transfer'; // dynamic!!!
?>

<nav>
    <a href="<?php echo $baseUrl; ?>/index.php" class="active">Home</a>
    <?php if (isset($_SESSION['role'])): ?>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="<?php echo $baseUrl; ?>/php/admin_dashboard.php">Admin Dashboard</a>
        <?php elseif ($_SESSION['role'] === 'user'): ?>
            <a href="<?php echo $baseUrl; ?>/php/dashboard.php">Dashboard</a>
        <?php endif; ?>
        <a href="<?php echo $baseUrl; ?>/php/logout.php">Logout</a>
    <?php else: ?>
        <a href="<?php echo $baseUrl; ?>/php/about.php">Why FluxPay?</a>
        <a href="<?php echo $baseUrl; ?>/php/signup.php">Sign Up</a>
        <a href="<?php echo $baseUrl; ?>/php/login.php">Login</a>
    <?php endif; ?>
</nav>