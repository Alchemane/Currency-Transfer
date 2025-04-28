<?php
if (isset($_SESSION['user_id'])) {
    $query = "SELECT userStatus FROM User WHERE userID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && strtolower($user['userStatus']) === 'suspended') {
        header("Location: refund_request.php");
        exit;
    }
} // this code prevents frozen users from accessing the website. only refunds.
?>
