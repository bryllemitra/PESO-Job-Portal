<?php
session_start();
include '../includes/config.php';

// Ensure user is not already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/index.php");
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $username_or_email = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Initialize error message
    $error = '';

    // Validate inputs
    if (empty($username_or_email) || empty($password)) {
        $error = "Please enter both username/email and password.";
    } else {
        // Check if the input is an email or username
        if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
            // Input is an email
            $stmt = $conn->prepare("SELECT id, username, password, role, is_verified FROM users WHERE email = ?");
            $stmt->bind_param("s", $username_or_email);
        } else {
            // Input is a username
            $stmt = $conn->prepare("SELECT id, username, password, role, is_verified FROM users WHERE username = ?");
            $stmt->bind_param("s", $username_or_email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Check if email is verified
            if ($user['is_verified'] == 0) {
                $error = "You still need to verify your email address. Please check your inbox.";
            } else {
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Store user details in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Set a success message based on the role
                    $message = '';
                    
                    if ($_SESSION['role'] == 'admin') {
                        $message = "Welcome back Admin! Manage and oversee all platform activities from here.";
                        header("Location: ../admin/admin.php?message=" . urlencode($message));
                    } elseif ($_SESSION['role'] == 'employer') {
                        $message = "Welcome " . $user['username'] . "! You may start posting job opportunities and manage your applicants.";
                        header("Location: ../employers/dashboard.php?message=" . urlencode($message));
                    } else {
                        // For user (applicant), check if profile is complete
                        
                        // Query to check if required profile fields are filled
                        $profile_check_stmt = $conn->prepare("SELECT first_name, last_name, birth_date, gender, phone_number, street_address FROM users WHERE id = ?");
                        $profile_check_stmt->bind_param("i", $_SESSION['user_id']);
                        $profile_check_stmt->execute();
                        $profile_result = $profile_check_stmt->get_result();
                        $profile_data = $profile_result->fetch_assoc();

                        // Check if any important fields are missing
                        $is_profile_complete = true;
                        $incomplete_message = "Welcome, " . $user['username'] . "! To get started, please complete your profile information so we can help you find the best opportunities.";
                        // Check profile fields
                        if (empty($profile_data['first_name']) || empty($profile_data['last_name']) || empty($profile_data['birth_date']) || empty($profile_data['gender']) || empty($profile_data['phone_number']) || empty($profile_data['street_address'])) {
                            $is_profile_complete = false;
                        }

                        // Check for work experience entries
                        $work_experience_check_stmt = $conn->prepare("SELECT id FROM work_experience WHERE user_id = ?");
                        $work_experience_check_stmt->bind_param("i", $_SESSION['user_id']);
                        $work_experience_check_stmt->execute();
                        $work_experience_result = $work_experience_check_stmt->get_result();
                        if ($work_experience_result->num_rows == 0) {
                            $is_profile_complete = false;
                        }

                        // Check for skills entries
                        $skills_check_stmt = $conn->prepare("SELECT id FROM skills WHERE user_id = ?");
                        $skills_check_stmt->bind_param("i", $_SESSION['user_id']);
                        $skills_check_stmt->execute();
                        $skills_result = $skills_check_stmt->get_result();
                        if ($skills_result->num_rows == 0) {
                            $is_profile_complete = false;
                        }

                        // Check for education entries
                        $education_check_stmt = $conn->prepare("SELECT id FROM education WHERE user_id = ?");
                        $education_check_stmt->bind_param("i", $_SESSION['user_id']);
                        $education_check_stmt->execute();
                        $education_result = $education_check_stmt->get_result();
                        if ($education_result->num_rows == 0) {
                            $is_profile_complete = false;
                        }

                        if ($is_profile_complete) {
                            // If profile is complete, redirect to browse page
                            $message = "Welcome back, " . $user['username'] . "! You're all set and ready to explore exciting opportunities. Start browsing available positions now!";
                            header("Location: ../pages/browse.php?message=" . urlencode($message));
                        } else {
                            // If profile is incomplete, redirect to profile page
                            $message = $incomplete_message;
                            header("Location: ../pages/profile.php?message=" . urlencode($message));
                        }
                    }
                    exit();
                } else {
                    $error = "Incorrect password!";
                }
            }
        } else {
            $error = "Username/email not found!";
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Include Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f1f3f5 !important;
        }
    </style>
</head>
<body class="flex items-center justify-center bg-gray-100 min-h-screen">

<div class="flex flex-col md:flex-row w-full max-w-4xl h-auto md:h-[500px] rounded-lg overflow-hidden shadow-lg">
    <!-- Left Section -->
    <div class="bg-blue-900 flex flex-col justify-center px-8 py-6 text-white md:w-2/5">
        <h2 class="text-xl md:text-2xl font-semibold mb-4 text-center md:text-left">Let's sign you in...</h2><br>
        <p class="text-3xl md:text-5xl font-bold text-center md:text-left mb-4">
            WELCOME <span class="text-red-500">BACK!</span>
        </p><br>
        <p class="text-sm md:text-base text-center md:text-left">
            New user? 
            <a href="register.php" class="text-yellow-400 font-bold hover:underline">Sign up here</a>
        </p>
        <p class="text-sm md:text-base text-center md:text-left mt-2">
            Seeking a job? 
            <a href="index.php" class="text-yellow-400 font-bold hover:underline">Browse here</a>
        </p>
    </div>

    <!-- Login Form -->
    <div class="bg-white flex flex-col justify-center px-10 py-8 md:w-3/5">
        <div class="flex justify-center mb-6">
            <img src="/JOB/uploads/PESO LOGO/OFFICIAL.png" alt="Logo" class="w-36">
        </div>
        <?php if (!empty($error)): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="text-green-500 text-sm mb-4"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 items-center">
                    <i class="fas fa-user mr-2"></i> Username/Email
                </label>
                <input type="text" id="username" name="username" required placeholder="Enter your username or email"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 items-center">
                    <i class="fas fa-lock mr-2"></i> Password
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required placeholder="Enter your password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <i id="togglePassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer"></i>
                </div>
            </div>
            <button type="submit"
                    class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Login
            </button>
        </form>

    </div>
</div>

<script>
    // Toggle password visibility
    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
        // Toggle the type attribute of the password field
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;

        // Toggle the icon
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
</script>

</body>
</html>

