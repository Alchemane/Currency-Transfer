<?php
include "../components/config.php";
include "../components/session_protect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// fetch current user details
$query = "SELECT firstName, middleName, lastName, address FROM User WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName = trim($_POST['lastName']);
    $address = trim($_POST['address']);

    if (empty($firstName) || empty($lastName) || empty($address)) {
        $error = "Please fill in all required fields.";
    } else {
        $updateQuery = "UPDATE User SET firstName = :firstName, middleName = :middleName, lastName = :lastName, address = :address WHERE userID = :userID";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'address' => $address,
            'userID' => $_SESSION['user_id']
        ]);
        $success = "Profile updated successfully!";
        
        // refresh $user data
        $stmt = $pdo->prepare($query);
        $stmt->execute(['userID' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="dashboard-container">
    <?php include "../components/sidebar_user.php"; ?>

    <div class="main-content">
        <h2>Edit Profile</h2>

        <div class="card">
            <?php if (isset($error)) echo "<p style='color:red; font-weight:bold;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green; font-weight:bold;'>$success</p>"; ?>

            <form method="POST" action="">
                <label>First Name:</label>
                <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>

                <label>Middle Name:</label>
                <input type="text" name="middleName" value="<?php echo htmlspecialchars($user['middleName']); ?>">

                <label>Last Name:</label>
                <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>

                <label>Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

                <button type="submit">Save Changes</button>
            </form>
        </div>

    </div>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>