<?php
$password = "amadeus_2045"; // manual password hash generator for login and database fix
echo password_hash($password, PASSWORD_DEFAULT);
?>