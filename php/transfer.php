<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/currency_rates.php";
include "../components/load_currencies.php";
include "../components/protect_user.php";
$currencies = loadCurrencies();

// fetch users accounts
$query = "SELECT Account.*, Currency.currencyCode FROM Account JOIN Currency ON Account.currencyID = Currency.currencyID WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['fromAccountID'];
    $to = $_POST['toAccountID'];
    $amount = floatval($_POST['amount']);

    // fetch from and to accounts
    $query = "SELECT Account.*, Currency.currencyCode, Currency.currencyID 
          FROM Account 
          JOIN Currency ON Account.currencyID = Currency.currencyID 
          WHERE accountID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $from]);
    $fromAccount = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT Account.*, Currency.currencyCode, Currency.currencyID 
          FROM Account 
          JOIN Currency ON Account.currencyID = Currency.currencyID 
          WHERE accountNumber = :number";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['number' => $to]);
    $toAccount = $stmt->fetch(PDO::FETCH_ASSOC);

    // checks if either account in the transaction process is frozen or not active
    if (!$fromAccount || !$toAccount) {
        $error = "Invalid Account Details.";
    } elseif (strtolower($fromAccount['accountStatus']) !== 'active') {
        $error = "Your wallet is currently frozen. You cannot send money.";
    } elseif (strtolower($toAccount['accountStatus']) !== 'active') {
        $error = "The recipient's wallet is currently frozen. You cannot send money to it.";
    }
    // currency type validationn
    elseif (!isset($currencies[$fromAccount['currencyCode']]) || !isset($currencies[$toAccount['currencyCode']])) {
        $error = "Invalid Currency Type.";
    } 
    else {
        if ($fromAccount['balance'] < $amount) {
            $error = "Insufficient Funds.";
        } else {
            if ($fromAccount['currencyCode'] !== $toAccount['currencyCode']) {
                $rate = $currencyRates[$fromAccount['currencyCode']][$toAccount['currencyCode']] ?? null;
                if (!$rate) {
                    $error = "Currency Conversion Not Supported.";
                } else {
                    $convertedAmount = $amount * $rate;
                }
            } else {
                $convertedAmount = $amount;
            }

            if (!isset($error)) {

                $limitQuery = $pdo->query("SELECT transactionLimits FROM Admin WHERE adminID = 1");
                $limitData = $limitQuery->fetch(PDO::FETCH_ASSOC);
                $maxLimit = $limitData['transactionLimits'];

                if ($amount > $maxLimit) {
                    // continue with transfer, but flag it
                    $flag = $pdo->prepare("INSERT INTO SuspiciousActivity (userID, activityType, dateDetected, status, evidenceRequested) 
                                           VALUES (:userID, 'Transfer', datetime('now'), 'Under Review', 1)");
                    $flag->execute(['userID' => $_SESSION['user_id']]);
                }

                if (!isset($error)) {
                    // deduct from sender
                    $newFromBalance = $fromAccount['balance'] - $amount;
                    $updateFrom = "UPDATE Account SET balance = :balance WHERE accountID = :id";
                    $stmt = $pdo->prepare($updateFrom);
                    $stmt->execute([
                        'balance' => $newFromBalance,
                        'id' => $fromAccount['accountID']
                    ]);
                    $stmt = null;

                    // add to receiver
                    $newToBalance = $toAccount['balance'] + $convertedAmount;
                    $updateTo = "UPDATE Account SET balance = :balance WHERE accountID = :id";
                    $stmt = $pdo->prepare($updateTo);
                    $stmt->execute([
                        'balance' => $newToBalance,
                        'id' => $toAccount['accountID']
                    ]);
                    $stmt = null;

                    // transaction record
                    $insertTransaction = "INSERT INTO Transactions (fromAccountID, toAccountID, amount, currencyID, transactionDate, status, transactionType, exchangeRate)
                      VALUES (:fromAccountID, :toAccountID, :amount, :currencyID, datetime('now'), 'Completed', :transactionType, :exchangeRate)";
                    $stmt = $pdo->prepare($insertTransaction);
                    $stmt->execute([
                        'fromAccountID' => $fromAccount['accountID'],
                        'toAccountID' => $toAccount['accountID'],
                        'amount' => $convertedAmount,
                        'currencyID' => $toAccount['currencyID'],
                        'transactionType' => 'transfer',
                        'exchangeRate' => isset($rate) ? $rate : 1
                    ]);
                    $stmt = null;

                    $success = "Transfer Successful!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transfer Money - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Transfer Currency</h2>

        <div class="card">
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <form method="POST" action="">
                <label>From Wallet:</label>
                <select name="fromAccountID" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['accountID']; ?>" data-currency="<?php echo htmlspecialchars($account['currencyCode']); ?>">
                            <?php echo htmlspecialchars($account['currencyCode']); ?> (<?php echo htmlspecialchars($account['accountNumber']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Recipient Wallet Number:</label>
                <input type="text" name="toAccountID" placeholder="Recipient Account Number" required>

                <label>Amount (£):</label>
                <input type="number" name="amount" step="0.01" min="0.01" required>

                <button type="submit">Send Money</button>

                <div id="transfer-preview" style="margin-top: 15px; font-weight: bold;"></div>
            </form>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

<script>
    const currencies = <?php echo json_encode($currencies); ?>;
    const currencyRates = <?php echo json_encode($currencyRates); ?>;
    const accounts = <?php echo json_encode($accounts); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        setupTransferPreview(accounts, currencies, currencyRates);
    });
</script>
<script src="../js/transfer_preview.js"></script>
</body>
</html>