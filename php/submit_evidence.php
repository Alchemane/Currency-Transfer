<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch suspicious activities that need evidence from this user
$query = "SELECT * FROM SuspiciousActivity WHERE userID = :userID AND evidenceRequested = 1 AND status = 'Under Review'";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle evidence submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['activityID']) && isset($_POST['evidence'])) {
    $activityID = $_POST['activityID'];
    $evidenceText = trim($_POST['evidence']);

    if (!empty($evidenceText)) {
        $stmt = $pdo->prepare("INSERT INTO Evidence (activityID, evidenceType, dateSubmitted, status) VALUES (:activityID, :evidenceType, datetime('now'), 'Pending')");
        $stmt->execute([
            'activityID' => $activityID,
            'evidenceType' => $evidenceText
        ]);

        $success = "Evidence submitted successfully!";
    } else {
        $error = "Please enter evidence details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Evidence - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Submit Evidence</h2>

        <div class="card">
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <?php if (empty($activities)): ?>
                <p>No evidence requested at the moment.</p>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="evidence-form">
                        <h3>Activity ID: <?php echo htmlspecialchars($activity['activityID']); ?></h3>
                        <form method="POST" action="">
                            <input type="hidden" name="activityID" value="<?php echo $activity['activityID']; ?>">
                            <label for="evidence">Evidence:</label>
                            <textarea name="evidence" rows="4" required></textarea>
                            <button type="submit">Submit Evidence</button>
                        </form>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>