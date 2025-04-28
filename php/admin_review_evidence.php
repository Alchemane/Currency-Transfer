<?php
session_start();
include "../components/session_protect.php";
include "../components/config.php";
include "../components/header.php";
include "../components/sidebar_admin.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// fetch all pending evidence
$query = "
    SELECT e.*, sa.userID, sa.activityType, sa.dateDetected, u.firstName, u.lastName
    FROM Evidence e
    JOIN SuspiciousActivity sa ON e.activityID = sa.activityID
    JOIN User u ON sa.userID = u.userID
    WHERE e.status = 'Pending'
    ORDER BY e.dateSubmitted DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$evidences = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle approve / reject
if (isset($_GET['approve'])) {
    $evidenceID = $_GET['approve'];

    // mark evidence as approved
    $stmt = $pdo->prepare("UPDATE Evidence SET status = 'Approved' WHERE evidenceID = :id");
    $stmt->execute(['id' => $evidenceID]);

    // mark suspicious activity as approved too
    $stmt = $pdo->prepare("UPDATE SuspiciousActivity SET status = 'Approved' WHERE activityID = (SELECT activityID FROM Evidence WHERE evidenceID = :id)");
    $stmt->execute(['id' => $evidenceID]);

    header("Location: admin_review_evidence.php");
    exit;
}

if (isset($_GET['reject'])) {
    $evidenceID = $_GET['reject'];

    // mark evidence as rejected
    $stmt = $pdo->prepare("UPDATE Evidence SET status = 'Rejected' WHERE evidenceID = :id");
    $stmt->execute(['id' => $evidenceID]);

    // mark suspicious activity as rejected too
    $stmt = $pdo->prepare("UPDATE SuspiciousActivity SET status = 'Rejected' WHERE activityID = (SELECT activityID FROM Evidence WHERE evidenceID = :id)");
    $stmt->execute(['id' => $evidenceID]);

    // also suspend the user
    $stmt = $pdo->prepare("UPDATE User SET userStatus = 'Suspended' WHERE userID = (SELECT sa.userID FROM SuspiciousActivity sa JOIN Evidence e ON sa.activityID = e.activityID WHERE e.evidenceID = :id)");
    $stmt->execute(['id' => $evidenceID]);

    header("Location: admin_review_evidence.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Review Evidence - Admin - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_admin.php"; ?>

    <div class="main-content">
        <h2>Review Submitted Evidence</h2>

        <div class="card">
            <?php if (empty($evidences)): ?>
                <p>No Pending Evidence Submissions.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity Type</th>
                            <th>Date Detected</th>
                            <th>Evidence</th>
                            <th>Submitted On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evidences as $evidence): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($evidence['firstName'] . " " . $evidence['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($evidence['activityType']); ?></td>
                            <td><?php echo htmlspecialchars($evidence['dateDetected']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($evidence['evidenceType'])); ?></td>
                            <td><?php echo htmlspecialchars($evidence['dateSubmitted']); ?></td>
                            <td>
                                <a href="?approve=<?php echo $evidence['evidenceID']; ?>" style="color:green;">Approve</a> |
                                <a href="?reject=<?php echo $evidence['evidenceID']; ?>" style="color:red;">Reject</a>
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