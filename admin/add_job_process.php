<?php
include '../includes/config.php';
include '../includes/restrictions.php';
include '../includes/restrictions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO jobs (title, description, category) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $category);
    $stmt->execute();

    header("Location: jobs.php");
    exit();
}
?>
