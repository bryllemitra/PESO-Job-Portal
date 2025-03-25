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
$company_name = $_POST['company_name'] ?? null;
$company_description = $_POST['company_description'] ?? null;
$location = $_POST['location'] ?? null;
$company_website = $_POST['company_website'] ?? null;
$portfolio_url = $_POST['portfolio_url'] ?? null;
$linkedin_profile = $_POST['linkedin_profile'] ?? null;

// Begin the transaction to ensure both updates happen together
if ($_SESSION['user_id'] == $user_id) {
    $conn->begin_transaction();

    try {
        // Check if employer data already exists
        $checkEmployerQuery = "SELECT * FROM employers WHERE user_id = ?";
        $stmtCheckEmployer = $conn->prepare($checkEmployerQuery);
        $stmtCheckEmployer->bind_param("i", $user_id);
        $stmtCheckEmployer->execute();
        $resultEmployer = $stmtCheckEmployer->get_result();
        $isEmployerNew = false;

        // If no employer data, insert it, else update
        if ($resultEmployer->num_rows == 0) {
            // Insert new employer data
            $isEmployerNew = true;
            if ($company_name !== null || $company_description !== null || $location !== null || $company_website !== null) {
                $query = "INSERT INTO employers (user_id, company_name, company_description, location, company_website) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $user_id, $company_name, $company_description, $location, $company_website);
                $stmt->execute();
            }
        } else {
            // Update employer data
            if ($company_name !== null || $company_description !== null || $location !== null || $company_website !== null) {
                $query = "UPDATE employers SET 
                          company_name = COALESCE(?, company_name), 
                          company_description = COALESCE(?, company_description), 
                          location = COALESCE(?, location), 
                          company_website = COALESCE(?, company_website) 
                          WHERE user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssi", $company_name, $company_description, $location, $company_website, $user_id);
                $stmt->execute();
            }
        }

        // Check if user data exists for portfolio and LinkedIn
        $checkUserQuery = "SELECT * FROM users WHERE id = ?";
        $stmtCheckUser = $conn->prepare($checkUserQuery);
        $stmtCheckUser->bind_param("i", $user_id);
        $stmtCheckUser->execute();
        $resultUser = $stmtCheckUser->get_result();
        $isUserNew = false;

        // If no user data, insert it, else update
        if ($resultUser->num_rows == 0) {
            // Insert new user data
            $isUserNew = true;
            if ($portfolio_url !== null || $linkedin_profile !== null) {
                $query2 = "INSERT INTO users (id, portfolio_url, linkedin_profile) 
                           VALUES (?, ?, ?)";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bind_param("iss", $user_id, $portfolio_url, $linkedin_profile);
                $stmt2->execute();
            }
        } else {
            // Update user data
            if ($portfolio_url !== null || $linkedin_profile !== null) {
                $query2 = "UPDATE users SET 
                           portfolio_url = COALESCE(?, portfolio_url), 
                           linkedin_profile = COALESCE(?, linkedin_profile) 
                           WHERE id = ?";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bind_param("ssi", $portfolio_url, $linkedin_profile, $user_id);
                $stmt2->execute();
            }
        }

        // Commit the transaction if both updates were successful
        $conn->commit();

        // Set success message in session
        if ($isEmployerNew || $isUserNew) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Information added successfully!'];
        } else {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Information updated successfully!'];
        }

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error saving/updating employer or user information.'];
    }

    // Redirect to profile.php with session message
    header("Location: profile.php");
    exit;

} else {
    echo "Unauthorized action.";
}
?>
