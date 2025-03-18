<?php
session_start();
include '../includes/config.php';

// Check if the token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token and activate the user
    $stmt = $conn->prepare("SELECT id, first_name, email, is_verified FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $first_name, $email, $is_verified);
        $stmt->fetch();

        if ($is_verified == 1) {
            // Already verified
            $message = "Your email is already verified. You can login now!";
        } else {
            // Mark the user as verified
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            if ($update_stmt->execute()) {
                // Send SweetAlert2 message and redirect to login
                $message = "Your email has been successfully verified! You can now login.";
            } else {
                $message = "There was an error verifying your email. Please try again.";
            }
        }
    } else {
        $message = "Invalid verification link or token.";
    }

    $stmt->close();
} else {
    $message = "No token found. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        // Show SweetAlert2 notification
        Swal.fire({
            title: "<?php echo $message; ?>",
            icon: "success",
            showConfirmButton: true,
            confirmButtonText: "Login Now",
            preConfirm: () => {
                window.location.href = "login.php"; // Redirect to login page after the message
            }
        });
    </script>
</body>
</html>
