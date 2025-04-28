<?php
include "../components/session_protect.php";
include "../components/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch user status
$query = "SELECT userStatus FROM User WHERE userID = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['userStatus'] !== 'suspended') {
    header("Location: dashboard.php");
    exit;
}

// if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $reason = trim($_POST['reason']);

    if ($amount <= 0 || empty($reason)) {
        $error = "Please Fill In All Fields Correctly.";
    } else {
        // insert refund request
        $query = "INSERT INTO RefundRequest (userID, amount, reason, status) VALUES (:userID, :amount, :reason, 'pending')";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'userID' => $_SESSION['user_id'],
            'amount' => $amount,
            'reason' => $reason
        ]);

        $success = "Refund Request Submitted Successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Request Refund - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Request A Refund</h2>

        <div class="card">
        <h2 style="color:red;">Your account has been suspended.</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <form method="POST" action="">
                <label>Amount (£):</label>
                <input type="number" name="amount" step="0.01" min="0.01" required>

                <label>Reason For Refund:</label>
                <textarea name="reason" rows="4" required></textarea>

                <button type="submit">Submit Refund Request</button>
            </form>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>