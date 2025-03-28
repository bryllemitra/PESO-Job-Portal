<?php
session_start();
include '../includes/config.php'; // Include DB connection

// Check if the user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Sanitize and validate the incoming form data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $phone_number = $_POST['phone_number'];
    $civil_status = $_POST['civil_status'];
    $street_address = $_POST['street_address'];
    $barangay = $_POST['barangay']; // Barangay ID from the dropdown
    $city = $_POST['city'];
    $zip_code = $_POST['zip_code'];
    
    // NEW FIELDS: Sanitize and validate
    $weight = filter_var($_POST['weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $height = filter_var($_POST['height'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $nationality = htmlspecialchars(trim($_POST['nationality']));
    $religion = htmlspecialchars(trim($_POST['religion']));

    // Ensure the email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email address.";
        header("Location: profile.php"); // Redirect back to the profile page
        exit;
    }

    // Calculate the age from birth_date
    $age = calculateAge($birth_date);

    // Prepare the update query (now includes the new fields)
    $sql = "UPDATE users SET 
            email = ?, gender = ?, birth_date = ?, age = ?, phone_number = ?, civil_status = ?, 
            street_address = ?, barangay = ?, city = ?, zip_code = ?, 
            weight = ?, height = ?, nationality = ?, religion = ?
            WHERE id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters (added the new fields)
        $stmt->bind_param(
            "ssssssssssddssi", 
            $email, $gender, $birth_date, $age, $phone_number, $civil_status, 
            $street_address, $barangay, $city, $zip_code, 
            $weight, $height, $nationality, $religion, 
            $_SESSION['user_id']
        );

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Personal information updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update personal information.";
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database query error: " . $conn->error;
    }

    // Close the connection
    $conn->close();

    // Redirect back to the profile page
    header("Location: profile.php");
    exit;
} else {
    // Redirect if the user is not logged in or the form is not submitted correctly
    header("Location: login.php");
    exit;
}

// Function to calculate age from birth date
function calculateAge($birth_date) {
    $birth = new DateTime($birth_date);  // Convert birth_date to DateTime object
    $today = new DateTime(); // Current date
    $age = $today->diff($birth)->y; // Calculate the difference in years
    return $age;
}
?>