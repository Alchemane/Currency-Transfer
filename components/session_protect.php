<?php
session_start();

// timeout duration (seconds)
$timeout_duration = 3600; // 1hr

// check if last activity is set
if (isset($_SESSION['LAST_ACTIVITY'])) {
    // if session is old, destroy it
    if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

// update last activity time
$_SESSION['LAST_ACTIVITY'] = time();
?>