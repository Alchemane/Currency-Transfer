<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch user info
$query = "SELECT firstName, lastName, userStatus FROM User WHERE userID = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// fetch users accounts
$query = "SELECT a.balance, a.accountNumber, c.currencyCode 
          FROM Account a 
          JOIN Currency c ON a.currencyID = c.currencyID 
          WHERE a.userID = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $_SESSION['user_id']]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Dashboard - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Welcome Back, <?php echo htmlspecialchars($user['firstName'] . " " . $user['lastName']); ?></h2>

        <?php if ($user['userStatus'] === 'suspended'): ?>
            <div class="card" style="background-color: #ffe0e0;">
                <p><strong>Your Account Is Suspended.</strong> You can <a href="refund_request.php" class="button">Request a Refund Here</a>.</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Your Wallets</h3>
            <?php if (empty($accounts)): ?>
                <p>You Have No Wallets Yet. Create One!</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($accounts as $account): ?>
                        <li>
                            <?php echo htmlspecialchars($account['currencyCode']); ?> Wallet -
                            <?php echo htmlspecialchars(number_format($account['balance'], 2)); ?> 
                            (Account: <?php echo htmlspecialchars($account['accountNumber']); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <a class="button" href="transfer.php">Transfer Money</a>
            <a class="button" href="open_account.php">Open New Wallet</a>
            <a class="button" href="edit_profile.php">Edit Profile</a>
            <a class="button" href="transfer_external.php">External Transfer</a>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>