<?php
$password = 'rahul69'; // Your chosen password
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password Hash: " . $hash;
?>