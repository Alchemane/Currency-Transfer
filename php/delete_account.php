<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_SESSION['user_id'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT password FROM User WHERE userID = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $deleteStmt = $pdo->prepare("DELETE FROM User WHERE userID = ?"); // delte user!
        $deleteStmt->execute([$userID]);

        session_destroy();
        header("Location: ../index.php?account_deleted=1");
        exit;
    } else {
        $error = "Password incorrect. Please try again.";
    }
}
?>
<link rel="stylesheet" href="../css/styles.css">
<?php include "../components/header.php"; ?>
<?php include "../components/sidebar_user.php"; ?>
<div class="main-content">
    <h2>Delete Account</h2>
    <div class="card">
        <p>This action is permanent and cannot be undone. All your wallets, transactions, and data will be removed.</p>

        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Enter your password to confirm:</label>
            <input type="password" name="password" required>
            <button type="submit" style="background-color:#ff4d4d;">Delete Account Permanently</button>
        </form>
    </div>
</div>

<?php include "../components/footer.php"; ?>
