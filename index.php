<?php
include "components/session_protect.php";

$deletionMessage = "";
if (isset($_GET['account_deleted']) && $_GET['account_deleted'] == 1) {
    $deletionMessage = "Your account has been deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome to FluxPay</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include "components/header.php"; ?>

<section class="hero">
    <h1>Welcome to FluxPay</h1>
    <p>Send and receive money internationally with ease and security.</p>

    <?php if (!empty($deletionMessage)): ?>
        <p style="color:red; text-align:center;"><?php echo $deletionMessage; ?></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION['role'])): ?>
        <a href="php/signup.php" class="button">Get started</a>
        <a href="php/login.php" class="button">Already have an account?</a>
    <?php else: ?>
        <a href="php/dashboard.php" class="button">Go to your dashboard</a>
    <?php endif; ?>
</section>

<?php include "components/footer.php"; ?>

</body>
</html>