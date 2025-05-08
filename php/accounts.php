<?php
include "../components/session_protect.php";
include "../components/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch user accounts
$query = "SELECT Account.*, Currency.currencyCode 
          FROM Account 
          JOIN Currency ON Account.currencyID = Currency.currencyID 
          WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Wallets - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Your wallets</h2>

        <div class="card">
            <?php if (empty($accounts)): ?>
                <p>No wallets yet. Add one!</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($accounts as $account): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($account['currencyCode']); ?> Wallet</strong> - 
                            <?php echo htmlspecialchars(number_format($account['balance'], 2)); ?> 
                            (account: <?php echo htmlspecialchars($account['accountNumber']); ?>)
                            <br>
                            <a href="delete_wallet.php?id=<?php echo $account['accountID']; ?>" class="button" style="background-color: #ff4d4d; margin-top: 6px;">Delete Wallet</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>