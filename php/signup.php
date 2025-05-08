<?php
include "../components/session_protect.php";
include "../components/config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // check if email already exists
    $check = $pdo->prepare("SELECT userID FROM User WHERE email = :email");
    $check->execute(['email' => $email]);

    if ($check->fetch()) {
        $error = "An account with this email already exists.";
    } else {
        // hash password securely using bcrypt
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $query = "INSERT INTO User (firstName, lastName, address, userStatus, verificationStatus, email, password)
                      VALUES (:firstName, :lastName, :address, 'Active', 'Verified', :email, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'address' => $address,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            header("Location: login.php?signup=success");
            exit;
        } catch (PDOException $e) {
            $error = "Registration failed due to a server error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="auth-container">
    <h2>Sign Up</h2>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <input type="text" name="firstName" placeholder="First Name" required>
        <input type="text" name="lastName" placeholder="Last Name" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>