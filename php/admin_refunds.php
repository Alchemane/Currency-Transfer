<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/header.php";
include "../components/sidebar_admin.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// fetch all refund requests
$query = "SELECT rr.*, u.firstName, u.lastName FROM RefundRequest rr JOIN User u ON rr.userID = u.userID ORDER BY rr.requestDate DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle approve / reject actions
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $stmt = $pdo->prepare("UPDATE RefundRequest SET status = 'approved' WHERE requestID = :id");
    $stmt->execute(['id' => $id]);
    header("Location: admin_refunds.php");
    exit;
}

if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    $stmt = $pdo->prepare("UPDATE RefundRequest SET status = 'rejected' WHERE requestID = :id");
    $stmt->execute(['id' => $id]);
    header("Location: admin_refunds.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Refund Requests - Admin - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_admin.php"; ?>

    <div class="main-content">
        <h2>Refund Requests</h2>

        <div class="card">
            <?php if (empty($refunds)): ?>
                <p>No Refund Requests.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($refunds as $refund): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($refund['firstName'] . ' ' . $refund['lastName']); ?></td>
                                <td>£<?php echo htmlspecialchars(number_format($refund['amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($refund['reason']); ?></td>
                                <td><?php echo htmlspecialchars($refund['requestDate']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($refund['status'])); ?></td>
                                <td>
                                    <?php if ($refund['status'] === 'pending'): ?>
                                        <a href="?approve=<?php echo $refund['requestID']; ?>">Approve</a> |
                                        <a href="?reject=<?php echo $refund['requestID']; ?>">Reject</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
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