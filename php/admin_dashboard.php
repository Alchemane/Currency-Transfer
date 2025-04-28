<?php
include "../components/session_protect.php";
include "../components/config.php";
include "../components/header.php";
include "../components/sidebar_admin.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<div class="main-content">
    <link rel="stylesheet" href="../css/styles.css">
    <h2>Admin Dashboard</h2>

    <div class="card">
        <h3>Quick Actions</h3>
        <ul>
            <li><a class="button" href="admin_refunds.php">Manage Refund Requests</a></li>
            <li><a class="button" href="admin_suspicious.php">Review Suspicious Activity</a></li>
            <li><a class="button" href="admin_limits.php">Set Transfer Limits</a></li>
            <li><a class="button" href="admin_currencies.php">Manage Currencies</a></li>
            <li><a class="button" href="admin_manage_users.php">Manage Users</a></li>
            <li><a class="button" href="admin_manage_accounts.php">Manage Accounts</a></li>
        </ul>
    </div>
</div>