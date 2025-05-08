<?php
include "../components/config.php";
include "../components/load_currencies.php";
include "../components/session_protect.php";
include "../components/protect_user.php";

$currencies = loadCurrencies();

// fetch user accounts with currency info
$query = "SELECT Account.*, Currency.currencyCode FROM Account JOIN Currency ON Account.currencyID = Currency.currencyID WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fromAccountID = $_POST['fromAccountID'];
    $recipientName = trim($_POST['recipientName']);
    $recipientBank = trim($_POST['recipientBank']);
    $recipientCountry = trim($_POST['recipientCountry']);
    $amount = floatval($_POST['amount']);

    // fetch from account
    $query = "SELECT Account.*, Currency.currencyCode, Currency.currencyID FROM Account JOIN Currency ON Account.currencyID = Currency.currencyID WHERE accountID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $fromAccountID]);
    $fromAccount = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fromAccount) {
        $error = "Invalid Wallet Selected.";
    } elseif ($amount <= 0) {
        $error = "Invalid Amount.";
    } elseif ($fromAccount['balance'] < $amount) {
        $error = "Insufficient Funds.";
    } else {
        // deduct funds
        $newBalance = $fromAccount['balance'] - $amount;
        $update = "UPDATE Account SET balance = :balance WHERE accountID = :id";
        $stmt = $pdo->prepare($update);
        $stmt->execute([
            'balance' => $newBalance,
            'id' => $fromAccount['accountID']
        ]);

        // record transaction (external_transfer type)
        $insertTransaction = "INSERT INTO Transactions (fromAccountID, toAccountID, amount, currencyID, transactionDate, status, transactionType, exchangeRate)
                              VALUES (:fromAccountID, NULL, :amount, :currencyID, datetime('now'), 'Completed', :transactionType, 1)";
        $stmt = $pdo->prepare($insertTransaction);
        $stmt->execute([
            'fromAccountID' => $fromAccount['accountID'],
            'amount' => $amount,
            'currencyID' => $fromAccount['currencyID'],
            'transactionType' => 'external_transfer'
        ]);

        // check admins transaction limit
        $limitStmt = $pdo->query("SELECT transactionLimits FROM Admin LIMIT 1");
        $limit = $limitStmt->fetchColumn();

        if ($amount > $limit) {
            $flag = $pdo->prepare("INSERT INTO SuspiciousActivity (userID, activityType, dateDetected, status, evidenceRequested) 
                                VALUES (:userID, 'Transfer', datetime('now'), 'Under Review', 1)");
            $flag->execute(['userID' => $_SESSION['user_id']]);
        }

        $success = "External Transfer Completed Successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>External Transfer - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
<?php include "../components/sidebar_user.php"; ?>

<div class="main-content">
    <h2>Transfer To External Bank Account</h2>

    <div class="card">
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

        <form method="POST" action="">
            <label>From Wallet:</label>
            <select name="fromAccountID" required>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?php echo $account['accountID']; ?>">
                        <?php echo htmlspecialchars($account['currencyCode']) . " (" . htmlspecialchars($account['accountNumber']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Recipient Full Name:</label>
            <input type="text" name="recipientName" required>

            <label>Recipient Bank Name:</label>
            <input type="text" name="recipientBank" required>

            <label>Recipient Country:</label>
            <input type="text" name="recipientCountry" required>

            <label>Amount:</label>
            <input type="number" name="amount" step="0.01" min="0.01" required>

            <button type="submit">Transfer</button>
        </form>
    </div>
</div>

</div>

<?php include "../components/footer.php"; ?>

</body>
</html>