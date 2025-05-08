<?php
include "../components/session_protect.php";
include "../components/config.php";

// protect page for admins only
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// handle adding new currency
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_currency'])) {
    $code = strtoupper(trim($_POST['currencyCode']));
    $symbol = trim($_POST['currencySymbol']);
    if (!preg_match('/^[A-Z]{3}$/', $code)) {
        $error = "Currency code must be exactly 3 letters.";
    }

    if (!empty($code) && !empty($symbol)) {
        // check if currency already exists
        $stmt = $pdo->prepare("SELECT * FROM Currency WHERE currencyCode = :code");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetch()) {
            $error = "Currency Code Already Exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO Currency (currencyCode, currencySymbol) VALUES (:code, :symbol)");
            $stmt->execute([
                'code' => $code,
                'symbol' => $symbol
            ]);
            $success = "Currency Added Successfully!";
        }
    } else {
        $error = "Please Fill In All Fields.";
    }
}

// handle deleting currency
if (isset($_GET['delete'])) {
    $deleteCode = $_GET['delete'];

    if ($deleteCode !== "GBP") { // protect GBP because its the defualt currency
        $stmt = $pdo->prepare("DELETE FROM Currency WHERE currencyCode = :code");
        $stmt->execute(['code' => $deleteCode]);
        $success = "Currency Deleted Successfully!";
    } else {
        $error = "Cannot Delete GBP Currency.";
    }
}

// fetch all currencies
$stmt = $pdo->query("SELECT * FROM Currency ORDER BY currencyCode ASC");
$currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/styles.css">
<?php
include "../components/header.php";
include "../components/sidebar_admin.php";
?>
<div class="main-content">
    <h2>Manage Currencies</h2>

    <?php if (isset($error)) echo "<p style='color:red; font-weight:bold;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green; font-weight:bold;'>$success</p>"; ?>

    <div class="card">
        <h3>Add New Currency</h3>
        <form method="POST" action="">
            <input type="text" name="currencyCode" placeholder="Currency Code (e.g., USD)" required>
            <input type="text" name="currencySymbol" placeholder="Currency Symbol (e.g., $)" required>
            <button type="submit" name="add_currency">Add Currency</button>
        </form>
    </div>

    <div class="card">
        <h3>Existing Currencies</h3>
        <table>
            <thead>
                <tr>
                    <th>Currency Code</th>
                    <th>Symbol</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currencies as $currency): ?>
                <tr>
                    <td><?php echo htmlspecialchars($currency['currencyCode']); ?></td>
                    <td><?php echo htmlspecialchars($currency['currencySymbol']); ?></td>
                    <td>
                        <?php if ($currency['currencyCode'] !== "GBP"): ?>
                            <a href="?delete=<?php echo htmlspecialchars($currency['currencyCode']); ?>" style="color: red;">Delete</a>
                        <?php else: ?>
                            <span style="color: gray;">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../components/footer.php"; ?>