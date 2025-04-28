<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch user's GBP account
$query = "SELECT * FROM Account WHERE userID = :userID AND currencyID = (SELECT currencyID FROM Currency WHERE currencyCode = 'GBP')";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$gbpAccount = $stmt->fetch(PDO::FETCH_ASSOC);

if ($gbpAccount && strtolower($gbpAccount['accountStatus']) !== 'active') {
    $error = "Your GBP wallet is frozen. You cannot deposit funds.";
    $gbpAccount = null; // hide deposit form
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $gbpAccount) {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } else {
        // update account balance
        $newBalance = $gbpAccount['balance'] + $amount;

        $update = "UPDATE Account SET balance = :balance, dateLastUpdated = datetime('now') WHERE accountID = :accountID";
        $stmt = $pdo->prepare($update);
        $stmt->execute([
            'balance' => $newBalance,
            'accountID' => $gbpAccount['accountID']
        ]);

        $success = "Deposit successful! New balance: £" . number_format($newBalance, 2);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Deposit Funds - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Deposit Into GBP Wallet</h2>

        <div class="card">
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <?php if ($gbpAccount): ?>
                <form method="POST" action="">
                    <label>Amount to Deposit (£):</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required>
                    <button type="submit">Deposit</button>
                </form>
            <?php else: ?>
                <p>You don't have a GBP wallet yet. Please create one first.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>