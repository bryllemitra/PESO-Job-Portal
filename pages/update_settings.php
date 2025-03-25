<?php
session_start();
include '../includes/config.php'; // Ensure the path is correct

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User not logged in'); // Stop if the user is not logged in
}

// Check if the form is submitted via AJAX (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form values
    $current_password = $_POST['current_password'];
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    // Get the current password from the database
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Prepare SQL query to fetch the current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the query returned a result
    if ($result->num_rows === 0) {
        echo "User not found!";
        exit;
    }

    $user = $result->fetch_assoc();

    // First, check if the current password is correct
    if (!$user || !password_verify($current_password, $user['password'])) {
        echo "Incorrect current password"; // If current password does not match
        exit;
    }

    // Check if the new password is the same as the current password
    if (!empty($new_password) && password_verify($new_password, $user['password'])) {
        echo "New password cannot be the same as the current password!";
        exit;
    }

    // Proceed to update username and password
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user['password']; // If no new password, keep the old one
    }

    // Update the username and password in the database
    $update_stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $new_username, $hashed_password, $user_id);
    $update_stmt->execute();

    // Update session data if username is changed
    $_SESSION['username'] = $new_username;

    // Send success response back to AJAX
    echo 'success';
}
?>
