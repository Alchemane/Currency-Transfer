<?php
include "../components/session_protect.php";
session_unset();
session_destroy();
header("Location: login.php");
exit;