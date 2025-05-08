<?php
include "../components/session_protect.php";
include "../components/config.php";

// get user's account IDs
$query = "SELECT accountID FROM Account WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$accountIDs = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'accountID');

// fetch related transactions
if (!empty($accountIDs)) {
    $placeholders = implode(',', array_fill(0, count($accountIDs), '?'));
    $query = "SELECT t.*, c.currencyCode, c.currencySymbol 
          FROM Transactions t
          JOIN Currency c ON t.currencyID = c.currencyID
          WHERE fromAccountID IN ($placeholders) OR toAccountID IN ($placeholders)
          ORDER BY transactionDate DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge($accountIDs, $accountIDs));
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transfer History - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Transfer History</h2>

        <div class="card">
            <?php if (empty($transactions)): ?>
                <p>No Transactions Yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transactionDate']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['fromAccountID']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['toAccountID']); ?></td>
                                <td>£<?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($transaction['currencySymbol']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($transaction['status'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>
