<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/header.php";
include "../components/sidebar_admin.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// freeze or unfreeze an account
if (isset($_GET['freeze'])) {
    $accountID = $_GET['freeze'];
    $stmt = $pdo->prepare("UPDATE Account SET accountStatus = 'frozen' WHERE accountID = :id");
    $stmt->execute(['id' => $accountID]);
    header("Location: admin_manage_accounts.php");
    exit;
}

if (isset($_GET['unfreeze'])) {
    $accountID = $_GET['unfreeze'];
    $stmt = $pdo->prepare("UPDATE Account SET accountStatus = 'active' WHERE accountID = :id");
    $stmt->execute(['id' => $accountID]);
    header("Location: admin_manage_accounts.php");
    exit;
}

// fetch all accounts
$query = "SELECT a.accountID, a.accountNumber, a.accountStatus, u.firstName, u.lastName, c.currencyCode
          FROM Account a
          JOIN User u ON a.userID = u.userID
          JOIN Currency c ON a.currencyID = c.currencyID
          ORDER BY a.accountID ASC";
$stmt = $pdo->query($query);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/styles.css">

<div class="main-content">
    <h2>Manage Accounts</h2>

    <div class="card">
        <?php if (empty($accounts)): ?>
            <p>No accounts found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Account Number</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['firstName'] . ' ' . $account['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($account['accountNumber']); ?></td>
                            <td><?php echo htmlspecialchars($account['currencyCode']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($account['accountStatus'])); ?></td>
                            <td>
                            <?php if (strtolower($account['accountStatus']) === 'active'): ?>
                                <a href="?freeze=<?php echo $account['accountID']; ?>" style="color:red;">Freeze</a>
                            <?php elseif (strtolower($account['accountStatus']) === 'frozen'): ?>
                                <a href="?unfreeze=<?php echo $account['accountID']; ?>" style="color:green;">Unfreeze</a>
                            <?php else: ?>
                                <span style="color:gray;">No Action</span>
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
