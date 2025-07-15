<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = $conn->real_escape_string($_POST['category']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['location']);

    $sql = "INSERT INTO complaints (user_id, category, subject, description, location) 
            VALUES ('$user_id', '$category', '$subject', '$description', '$location')";

    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php?success=1");
    } else {
        header("Location: dashboard.php?error=1");
    }
}
?>