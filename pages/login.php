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
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Initialize error message
    $error = '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Secure query to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store user details in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect user to their dashboard
                header("Location: ../pages/index.php");
                exit();
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "Invalid username or password!";
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
            <img src="/JOB/uploads/OFFICIAL.png" alt="Logo" class="w-36">
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
                    <i class="fas fa-user mr-2"></i> Username
                </label>
                <input type="text" id="username" name="username" required placeholder="Enter your username"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 items-center">
                    <i class="fas fa-lock mr-2"></i> Password
                </label>
                <input type="password" id="password" name="password" required placeholder="Enter your password"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <button type="submit"
                    class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
