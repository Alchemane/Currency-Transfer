<?php
include "../components/session_protect.php";
include "../components/config.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// fetch suspicious activities
$query = "SELECT sa.*, u.firstName, u.lastName
          FROM SuspiciousActivity sa
          JOIN User u ON sa.userID = u.userID
          WHERE sa.status = 'Under Review'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// approve or reject suspicious activity manually
if (isset($_GET['approve'])) {
    $activityID = $_GET['approve'];

    $stmt = $pdo->prepare("UPDATE SuspiciousActivity SET status = 'Approved' WHERE activityID = :id");
    $stmt->execute(['id' => $activityID]);
    header("Location: admin_suspicious.php");
    exit;
}

if (isset($_GET['reject'])) {
    $activityID = $_GET['reject'];

    // suspend user if evidence rejected
    $query = "SELECT userID FROM SuspiciousActivity WHERE activityID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $activityID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare("UPDATE User SET userStatus = 'Suspended' WHERE userID = :id");
        $stmt->execute(['id' => $user['userID']]);
    }

    $stmt = $pdo->prepare("UPDATE SuspiciousActivity SET status = 'Rejected' WHERE activityID = :id");
    $stmt->execute(['id' => $activityID]);

    header("Location: admin_suspicious.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Suspicious Activity - Admin - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_admin.php"; ?>

    <div class="main-content">
        <h2>Suspicious Activities</h2>

        <div class="card">
            <?php if (empty($activities)): ?>
                <p>No Suspicious Activities Under Review.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity Type</th>
                            <th>Date Detected</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['firstName'] . ' ' . $activity['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($activity['activityType']); ?></td>
                            <td><?php echo htmlspecialchars($activity['dateDetected']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($activity['status'])); ?></td>
                            <td>
                                <a href="?approve=<?php echo $activity['activityID']; ?>">Approve</a> |
                                <a href="?reject=<?php echo $activity['activityID']; ?>">Reject</a>
                            </td>
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