<?php
$password = 'rahul123'; // Your chosen password
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password Hash: " . $hash;
?>