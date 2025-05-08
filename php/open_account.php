<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/load_currencies.php"; //load currencies!
include "../components/protect_user.php";
$currencies = loadCurrencies();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currencyCode = $_POST['currencyType']; // user selected code, eg USD

    // fetch currencyID from Currency table
    $stmt = $pdo->prepare("SELECT currencyID FROM Currency WHERE currencyCode = :currencyCode");
    $stmt->execute(['currencyCode' => $currencyCode]);
    $currency = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($currency) {
        $currencyID = $currency['currencyID'];

        // generate random account number
        $accountNumber = 'ACCT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

        try {
            $query = "INSERT INTO Account (userID, currencyID, balance, accountStatus, dateOpened, dateLastUpdated, accountNumber)
                      VALUES (:userID, :currencyID, 0.00, 'Active', datetime('now'), datetime('now'), :accountNumber)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'userID' => $_SESSION['user_id'],
                'currencyID' => $currencyID,
                'accountNumber' => $accountNumber
            ]);

            $success = "New $currencyCode Wallet Created Successfully!";
        } catch (PDOException $e) {
            $error = "Failed To Create Wallet: " . $e->getMessage();
        }
    } else {
        $error = "Invalid Currency Selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Open New Account - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Open A New Currency Wallet</h2>

        <div class="card">
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <form method="POST" action="">
                <label>Select Currency:</label>
                <select name="currencyType" required>
                    <?php foreach ($currencies as $code => $symbol): ?>
                        <option value="<?php echo htmlspecialchars($code); ?>">
                            <?php echo htmlspecialchars($code); ?> (<?php echo htmlspecialchars($symbol); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Create Account</button>
            </form>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>
