<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

$userID = $_SESSION['user_id'];
$error = "";
$success = "";

if (isset($_GET['id'])) {
    $accountID = $_GET['id'];

    // Validate that the account belongs to the user
    $stmt = $pdo->prepare("SELECT balance FROM Account WHERE accountID = ? AND userID = ?");
    $stmt->execute([$accountID, $userID]);
    $account = $stmt->fetch();

    if (!$account) {
        $error = "Account not found or does not belong to you.";
    } elseif ($account['balance'] > 0) {
        $error = "Cannot delete a wallet that still has a balance. You must transfer entire balance out of the wallet first.";
    } else {
        // Safe to delete
        $deleteStmt = $pdo->prepare("DELETE FROM Account WHERE accountID = ?");
        $deleteStmt->execute([$accountID]);
        $success = "Wallet deleted successfully.";
        header("Location: accounts.php?wallet_deleted=1");
        exit;
    }
} else {
    $error = "No account ID provided.";
}
?>
<link rel="stylesheet" href="../css/styles.css">
<?php include "../components/header.php"; ?>
<?php include "../components/sidebar_user.php"; ?>

<div class="main-content">
    <h2>Delete Wallet</h2>

    <div class="card">
        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
            <a href="accounts.php" class="button">Back to Wallets</a>
        <?php endif; ?>
    </div>
</div>

<?php include "../components/footer.php"; ?>