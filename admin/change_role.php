<?php
// Start output buffering
ob_start();

// Include configuration and header files
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href = '../pages/index.php';</script>";
    exit();
}

// Handle form submission
if (isset($_GET['id']) && isset($_POST['role'])) {
    $user_id = $_GET['id'];
    $new_role = $_POST['role'];

    // Validate the new role to include 'employer'
    if (!in_array($new_role, ['user', 'admin', 'employer'])) {
        die("Invalid role selected.");
    }

    // Update the user's role in the database
    $update_query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $new_role, $user_id);

    if ($stmt->execute()) {
        // Clear any existing output and display only the modal
        ob_clean(); // Clear the output buffer

        echo "
        <!-- Include Bootstrap CSS -->
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>

        <!-- Modal Structure -->
        <div class='modal fade show' id='successModal' tabindex='-1' aria-labelledby='successModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
            <div class='modal-dialog modal-dialog-centered'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='successModalLabel'>Success!</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href='user_list.php'\"></button>
                    </div>
                    <div class='modal-body'>
                        User role updated successfully!
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-primary' onclick=\"window.location.href='user_list.php'\">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add a backdrop for the modal -->
        <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>

        <!-- Include Bootstrap JS and Popper.js -->
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
        ";

        exit(); // Stop further execution of the script
    } else {
        // Clear any existing output and display only the modal
        ob_clean(); // Clear the output buffer

        echo "
        <!-- Include Bootstrap CSS -->
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>

        <!-- Modal Structure -->
        <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
            <div class='modal-dialog modal-dialog-centered'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='errorModalLabel'>Error!</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href='user_list.php'\"></button>
                    </div>
                    <div class='modal-body'>
                        Error updating role. Please try again.
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-danger' onclick=\"window.location.href='user_list.php'\">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add a backdrop for the modal -->
        <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>

        <!-- Include Bootstrap JS and Popper.js -->
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
        ";

        exit(); // Stop further execution of the script
    }
} else {
    $user_id = $_GET['id'] ?? null;
    if (!$user_id) {
        echo "No user ID provided.";
        exit();
    }

    // Get the user info for the form
    $query = "SELECT id, username, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change User Role</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/change_role.css">
    <!-- Custom Styles -->
    <style>
        /* Custom styles (if any) */
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Role for <?= htmlspecialchars($user['username']) ?></h2>
        <form method="POST" action="change_role.php?id=<?= $user['id'] ?>">
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <!-- Added the "employer" role here -->
                    <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="employer" <?= ($user['role'] == 'employer') ? 'selected' : '' ?>>Employer</option> <!-- New Employer option -->
                </select>
            </div>
            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-primary">Update Role</button>
                <a href="user_list.php" class="btn btn-light d-block">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
