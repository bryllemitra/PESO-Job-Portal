<?php
session_start();
include '../includes/config.php';

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$barangay_query = "SELECT name FROM barangay ORDER BY name ASC";
$barangay_result = $conn->query($barangay_query);

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
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $ext_name = trim($_POST['ext_name']);
    $gender = trim($_POST['gender']);
    $birth_date = $_POST['birth_date'];
    $age = intval($_POST['age']);
    $phone_number = trim($_POST['phone_number']);
    $place_of_birth = trim($_POST['place_of_birth']);
    $civil_status = trim($_POST['civil_status']);
    $zip_code = trim($_POST['zip_code']);
    $street_address = trim($_POST['street_address']);
    $barangay = trim($_POST['barangay']);
    $city = trim($_POST['city']);

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
    if (!in_array($gender, ['Male', 'Female', 'Non-Binary', 'LGBTQ+', 'Other'])) {
        $errors[] = "Invalid gender selected.";
    }
    if (!preg_match('/^\d{11}$/', $phone_number)) {
        $errors[] = "Phone number must be exactly 11 digits.";
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

    // Insert data into the database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (
            email, password, username, first_name, middle_name, last_name, ext_name, gender, birth_date, age, phone_number, place_of_birth, civil_status, zip_code, street_address, barangay, city
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssisssssss", $email, $hashed_password, $username, $first_name, $middle_name, $last_name, $ext_name, $gender, $birth_date, $age, $phone_number, $place_of_birth, $civil_status, $zip_code, $street_address, $barangay, $city);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit();
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
        <form method="POST" action="" class="space-y-4">
            <!-- Add CSRF Token for Security -->
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
                        <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" required maxlength="50"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-user mr-2"></i> Last Name
                        </label>
                        <input type="text" id="last_name" name="last_name" placeholder="Last Name" required maxlength="50"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="ext_name" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-user-edit mr-2"></i> Extension Name (e.g., Jr., Sr.)
                        </label>
                        <input type="text" id="ext_name" name="ext_name" placeholder="Extension Name" maxlength="10"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-calendar-alt mr-2"></i> Birth Date
                        </label>
                        <input type="date" id="birth_date" name="birth_date" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-sort-numeric-up mr-2"></i> Age
                        </label>
                        <input type="number" id="age" name="age" placeholder="Age" min="1" max="120"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-venus-mars mr-2"></i> Gender
                        </label>
                        <select id="gender" name="gender" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Non-Binary">Non-Binary</option>
                            <option value="LGBTQ+">LGBTQ+</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="border-b border-gray-200 pb-4">
                <h4 class="text-lg font-semibold mb-4">Contact Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-phone mr-2"></i> Phone Number (11 digits)
                        </label>
                        <input type="text" id="phone_number" name="phone_number" placeholder="Phone Number" required maxlength="11"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-envelope mr-2"></i> Email
                        </label>
                        <input type="email" id="email" name="email" placeholder="Email" required maxlength="100"
                            oninput="this.value = this.value.toLowerCase();"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="place_of_birth" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-map-marker-alt mr-2"></i> Place of Birth
                        </label>
                        <input type="text" id="place_of_birth" name="place_of_birth" placeholder="Place of Birth" required maxlength="100"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="street_address" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-road mr-2"></i> Street Address
                        </label>
                        <input type="text" id="street_address" name="street_address" placeholder="Street Address" required maxlength="255"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-city mr-2"></i> City
                        </label>
                        <input type="text" id="city" name="city" placeholder="City" required maxlength="100"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="zip_code" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-mail-bulk mr-2"></i> ZIP Code
                        </label>
                        <input type="text" id="zip_code" name="zip_code" placeholder="ZIP Code" required maxlength="10"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>

            <!-- Account Credentials -->
            <div>
                <h4 class="text-lg font-semibold mb-4">Account Credentials</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

            <button type="submit"
                class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Register
            </button>
        </form>
    </div>
</div>

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
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>