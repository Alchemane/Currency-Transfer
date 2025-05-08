<?php
include "../components/session_protect.php";
include "../components/config.php";

if (!isset($_GET['id'])) {
    die("No user ID provided.");
}

$userID = $_GET['id'];
$error = "";
$success = "";

// fetch existing user info
$stmt = $pdo->prepare("SELECT firstName, middleName, lastName, address, email FROM User WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName = trim($_POST['lastName']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($firstName) || empty($lastName) || empty($address) || empty($email)) {
        $error = "All fields except password are required.";
    } elseif (!empty($newPassword) || !empty($confirmPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } elseif (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters.";
        } else {
            // update everything INCLUDING password
            $update = $pdo->prepare("UPDATE User SET firstName = ?, middleName = ?, lastName = ?, address = ?, email = ?, password = ? WHERE userID = ?");
            $update->execute([
                $firstName,
                $middleName,
                $lastName,
                $address,
                $email,
                password_hash($newPassword, PASSWORD_DEFAULT),
                $userID
            ]);
            $success = "User info and password updated.";
        }
    } else {
        // update without changing password
        $update = $pdo->prepare("UPDATE User SET firstName = ?, middleName = ?, lastName = ?, address = ?, email = ? WHERE userID = ?");
        $update->execute([
            $firstName,
            $middleName,
            $lastName,
            $address,
            $email,
            $userID
        ]);
        $success = "User info updated.";
    }

    // refresh data
    $stmt = $pdo->prepare("SELECT firstName, middleName, lastName, address, email FROM User WHERE userID = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php
include "../components/header.php";
include "../components/sidebar_admin.php";
?>
<div class="dashboard-container">
    <div class="main-content">
    <link rel="stylesheet" href="../css/styles.css">
        <h2>Edit User (Admin)</h2>

        <div class="card">
            <?php if (!empty($error)) echo "<p style='color:red; font-weight:bold;'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p style='color:green; font-weight:bold;'>$success</p>"; ?>

            <form method="POST">
                <label>First Name:</label>
                <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>

                <label>Middle Name:</label>
                <input type="text" name="middleName" value="<?php echo htmlspecialchars($user['middleName']); ?>">

                <label>Last Name:</label>
                <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>

                <label>Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <hr>

                <label>New Password (optional):</label>
                <input type="password" name="newPassword">

                <label>Confirm New Password:</label>
                <input type="password" name="confirmPassword">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include "../components/footer.php"; ?>