<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

?>

<?php include "../components/header.php"; ?>
<?php include "../components/sidebar_user.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Settings - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="dashboard-container">
<div class="main-content">
    <h2>Account Settings</h2>

    <div class="card">
        <p>If you'd like to permanently delete your account, click below.</p>
        <a href="../php/delete_account.php" class="button" style="background-color:#ff4d4d;">Delete My Account</a>
    </div>
    
</div>
</div>

<?php include "../components/footer.php"; ?>
</body>
</html>