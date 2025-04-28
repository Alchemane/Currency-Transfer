<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/header.php";
include "../components/sidebar_admin.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// suspend or unsuspend usetr
if (isset($_GET['suspend'])) {
    $userID = $_GET['suspend'];
    $stmt = $pdo->prepare("UPDATE User SET userStatus = 'suspended' WHERE userID = :id");
    $stmt->execute(['id' => $userID]);
    header("Location: admin_manage_users.php");
    exit;
}

if (isset($_GET['unsuspend'])) {
    $userID = $_GET['unsuspend'];
    $stmt = $pdo->prepare("UPDATE User SET userStatus = 'active' WHERE userID = :id");
    $stmt->execute(['id' => $userID]);
    header("Location: admin_manage_users.php");
    exit;
}

// fetch all users
$stmt = $pdo->query("SELECT userID, firstName, lastName, email, userStatus FROM User ORDER BY userID ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/styles.css">

<div class="main-content">
    <h2>Manage Users</h2>

    <div class="card">
        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($user['userStatus'])); ?></td>
                            <td>
                                <?php if ($user['userStatus'] === 'active'): ?>
                                    <a href="?suspend=<?php echo $user['userID']; ?>" style="color:red;">Suspend</a>
                                <?php else: ?>
                                    <a href="?unsuspend=<?php echo $user['userID']; ?>" style="color:green;">Unsuspend</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php include "../components/footer.php"; ?>
