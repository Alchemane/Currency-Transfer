<?php
include "../components/session_protect.php";
include "../components/config.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// update limit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $limit = floatval($_POST['limit']);

    $stmt = $pdo->prepare("UPDATE Admin SET transactionLimits = :limit WHERE adminID = :adminID");
    $stmt->execute([
        'limit' => $limit,
        'adminID' => $_SESSION['admin_id']
    ]);

    $success = "Transfer limit updated to £" . number_format($limit, 2);
}

// fetch current limit
$stmt = $pdo->prepare("SELECT transactionLimits FROM Admin WHERE adminID = :adminID");
$stmt->execute(['adminID' => $_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$currentLimit = $admin['transactionLimits'];
?>
<?php
include "../components/header.php";
include "../components/sidebar_admin.php";
?>
<div class="dashboard-container">
    <div class="main-content">
    <link rel="stylesheet" href="../css/styles.css">
        <h2>Set Global Transfer Limit</h2>

        <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

        <form method="POST">
            <label>Maximum Transfer Per Transaction (£):</label>
            <input type="number" name="limit" step="0.01" value="<?php echo htmlspecialchars($currentLimit); ?>" required>
            <button type="submit">Update Limit</button>
        </form>
    </div>
</div>
<?php include "../components/footer.php"; ?>