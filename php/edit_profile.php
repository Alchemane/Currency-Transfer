<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/protect_user.php";

$userID = $_SESSION['user_id'];
$error = "";
$success = "";

// fetch current user details
$query = "SELECT firstName, middleName, lastName, address, email, password FROM User WHERE userID = :userID";
$stmt = $pdo->prepare($query);
$stmt->execute(['userID' => $userID]);
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
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // check required fields
    if (empty($firstName) || empty($lastName) || empty($address) || empty($email) || empty($currentPassword)) {
        $error = "All fields except new password are required.";
    }
    // check if current password is correct
    elseif (!password_verify($currentPassword, $user['password'])) {
        $error = "Current password is incorrect.";
    }
    // if password fields are filled then validate them
    elseif (!empty($newPassword) || !empty($confirmPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } elseif (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters.";
        } else {
            // Update all + password
            $updateQuery = "UPDATE User SET firstName = :firstName, middleName = :middleName, lastName = :lastName, address = :address, email = :email, password = :password WHERE userID = :userID";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'address' => $address,
                'email' => $email,
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'userID' => $userID
            ]);
            $success = "Profile and password updated successfully!";
        }
    } else {
        // update all except password
        $updateQuery = "UPDATE User SET firstName = :firstName, middleName = :middleName, lastName = :lastName, address = :address, email = :email WHERE userID = :userID";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'address' => $address,
            'email' => $email,
            'userID' => $userID
        ]);
        $success = "Profile updated successfully!";
    }

    // refresh user data
    $stmt = $pdo->prepare($query);
    $stmt->execute(['userID' => $userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
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
            <?php if (!empty($error)) echo "<p style='color:red; font-weight:bold;'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p style='color:green; font-weight:bold;'>$success</p>"; ?>

            <form method="POST" action="">
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
                <label>Current Password (required to save changes):</label>
                <input type="password" name="currentPassword" required>

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
</body>
</html>