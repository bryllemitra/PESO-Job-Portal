<?php
session_start();
include '../includes/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files (make sure these are included)
require '../vendor/autoload.php';  // If using Composer, otherwise include manually

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Sanitize inputs
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    
    // Validate inputs
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Check if email or username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email or username already exists.";
    }
    $stmt->close();

    // Insert data into the database if no errors
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $verification_token = bin2hex(random_bytes(16));  // Generate a unique token for email verification
        $stmt = $conn->prepare("INSERT INTO users (
            email, password, username, first_name, last_name, verification_token, is_verified
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $is_verified = 0; // Account not verified yet

        // Bind the parameters (NULL values are handled automatically for omitted fields)
        $stmt->bind_param("ssssssi", $email, $hashed_password, $username, $first_name, $last_name, $verification_token, $is_verified);

        if ($stmt->execute()) {
            // Send the confirmation email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'venardjhoncsalido@gmail.com';  // Your Gmail address
                $mail->Password = 'kcao mhcd axdw dpda';  // Use the app password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('venardjhoncsalido@gmail.com', 'PESO Job Portal');
                $mail->addAddress($email);  // User email address

                // Construct the verification URL
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $domain = $_SERVER['HTTP_HOST']; // Get the domain (e.g., localhost or your live domain)
                $verification_url = $protocol . '://' . $domain . '/JOB/pages/verify.php?token=' . $verification_token;

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Email Confirmation';
                $mail->Body    = 'Hi ' . htmlspecialchars($first_name) . ',<br><br>Please confirm your email address by clicking the link below:<br><a href="' . $verification_url . '">Confirm Email</a>';

                $mail->send();
                $_SESSION['success_message'] = "Registration successful! Please check your email to confirm your account.";
                header("Location: login.php");
                exit();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                $errors[] = "There was an error sending the confirmation email. Please try again.";
            }
        } else {
            error_log("Database error: " . $stmt->error);
            $errors[] = "An error occurred while registering. Please try again.";
        }
        $stmt->close();
    }
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F0F8FF !important;
        }
    </style>
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <style>

    </style>
</head>
<body class="flex items-center justify-center bg-gray-100 min-h-screen">

<div class="flex flex-col md:flex-row w-full max-w-7xl h-auto md:h-[800px] rounded-lg overflow-hidden shadow-lg">
    <!-- Left Section -->
    <div class="bg-blue-900 flex flex-col justify-center px-8 py-6 text-white md:w-2/5">
        <h2 class="text-xl md:text-2xl font-semibold mb-4 text-center md:text-left">Take the First Step...</h2><br>
        <p class="text-3xl md:text-5xl font-bold text-center md:text-left">
            FIND YOUR <span class="text-red-500">PLACE,</span>
        </p>
        <p class="text-3xl md:text-5xl font-bold text-center md:text-right mb-4">
        KICKSTART <span class="text-red-500">YOUR CAREER!</span>
        </p><br>
        <p class="text-sm md:text-base text-center md:text-center">
            Already have an account? 
            <a href="login.php" class="text-yellow-400 font-bold hover:underline">Sign in here</a>
        </p>

    </div>

    <!-- Registration Form -->
    <div class="bg-white flex flex-col justify-center px-10 py-8 md:w-3/5 space-y-6">
        <div class="flex justify-center mb-6">
            <img src="/JOB/uploads/OFFICIAL.png" alt="Logo" class="w-36 rotating-logo">
        </div>
        <?php if (!empty($errors)): ?>
            <div class="text-red-500 text-sm mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4" id="registrationForm">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

    <!-- Basic Information -->
    <div class="border-b border-gray-200 pb-4">
        <h4 class="text-lg font-semibold mb-4">Basic Information</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-user mr-2"></i> First Name
                </label>
                <input type="text" id="first_name" name="first_name" placeholder="First Name" required maxlength="50"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-user mr-2"></i> Middle Name
                </label>
                <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" maxlength="50"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-user mr-2"></i> Last Name
                </label>
                <input type="text" id="last_name" name="last_name" placeholder="Last Name" required maxlength="50"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
        </div>
    </div>

    <!-- Account Credentials -->
    <div>
        <h4 class="text-lg font-semibold mb-4">Account Credentials</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-user-tag mr-2"></i> Username
                </label>
                <input type="text" id="username" name="username" placeholder="Username" required maxlength="50"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-envelope mr-2"></i> Email
                </label>
                <input type="email" id="email" name="email" placeholder="Email" required maxlength="100"
                    oninput="this.value = this.value.toLowerCase();"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <span id="emailError" class="text-red-500 text-sm hidden">Please enter a valid email address.</span>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-lock mr-2"></i> Password
                </label>
                <input type="password" id="password" name="password" placeholder="Password" required minlength="8"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-lock mr-2"></i> Confirm Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required minlength="8"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
        </div>
    </div>

    <!-- Register Button -->
    <button type="submit"
        class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Register
    </button>
</form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- JavaScript for Age and Birth Date Synchronization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const birthDateInput = document.getElementById('birth_date');
            const ageInput = document.getElementById('age');

            // Calculate age based on birth date
            birthDateInput.addEventListener('change', function () {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDifference = today.getMonth() - birthDate.getMonth();
                if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageInput.value = age > 0 ? age : '';
            });

            // Calculate birth year based on age
            ageInput.addEventListener('input', function () {
                const age = parseInt(this.value);
                if (age > 0) {
                    const today = new Date();
                    const birthYear = today.getFullYear() - age;
                    const birthMonth = today.getMonth() + 1; // Months are zero-based
                    const birthDay = today.getDate();
                    const formattedBirthDate = `${birthYear}-${String(birthMonth).padStart(2, '0')}-${String(birthDay).padStart(2, '0')}`;
                    birthDateInput.value = formattedBirthDate;
                } else {
                    birthDateInput.value = '';
                }
            });
        });


        // JavaScript for front-end email validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const emailField = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const email = emailField.value;

    // Simple email regex for validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        // Show error message if email is invalid
        emailError.classList.remove('hidden');
        e.preventDefault();  // Prevent form submission
    } else {
        emailError.classList.add('hidden');
    }
});
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>