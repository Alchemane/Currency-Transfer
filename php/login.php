<?php
include "../components/session_protect.php";
include "../components/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // check if the user is an admin
    $query = "SELECT adminID, password FROM Admin WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['adminID'];
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit;
    }

    // check if the user is a normal user
    $query = "SELECT userID, password, userStatus FROM User WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['role'] = 'user';

        if (strtolower($user['userStatus']) === 'suspended') {
            header("Location: refund_request.php");
            exit;
        } else {
            header("Location: dashboard.php");
            exit;
        }
    }

    // invalid credentials
    $error = "The email or password is incorrect.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - FluxPay</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include "../components/header.php"; ?>

<div class="auth-container">
    <h2>Login</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

<?php include "../components/footer.php"; ?>

</body>
</html>