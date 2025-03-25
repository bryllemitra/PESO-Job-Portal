<?php
include '../includes/config.php'; // Include DB connection

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to update your information.");
}

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];

// Get the posted data
$portfolio_url = $_POST['portfolio_url'] ?? null;
$linkedin_profile = $_POST['linkedin_profile'] ?? null;

// Begin the transaction to ensure both updates happen together
if ($_SESSION['user_id'] == $user_id) {
    $conn->begin_transaction();

    try {
        // Update Portfolio URL and LinkedIn Profile in the users table (only if the values are set)
        if ($portfolio_url !== null || $linkedin_profile !== null) {
            $query2 = "UPDATE users SET 
                       portfolio_url = COALESCE(?, portfolio_url), 
                       linkedin_profile = COALESCE(?, linkedin_profile) 
                       WHERE id = ?";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ssi", $portfolio_url, $linkedin_profile, $user_id);
            $stmt2->execute();
        }

        // Commit the transaction if both updates were successful
        $conn->commit();

        // Set success message in session
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Information updated successfully!'];

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating user information.'];
    }

    // Redirect to profile.php with session message
    header("Location: profile.php");
    exit;

} else {
    echo "Unauthorized action.";
}
?>
