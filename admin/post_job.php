<?php
include '../includes/config.php';
include '../includes/header.php';

// Restrict access to admins and employers only
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employer')) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    You do not have permission to access this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' id='redirectBtn'>OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    <script type='text/javascript'>
        document.getElementById('redirectBtn').addEventListener('click', function() {
            window.location.href = '../index.php'; // Redirect to the home page when the button is clicked
        });
    </script>
    ";
    exit();
}





// Fetch job categories from the database in alphabetical order 
$category_stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$category_stmt->execute();
$categories = $category_stmt->get_result();

// Fetch job positions from the database in alphabetical order
$position_stmt = $conn->prepare("SELECT id, position_name FROM job_positions ORDER BY position_name ASC");
$position_stmt->execute();
$positions = $position_stmt->get_result();

// Handle job posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role'])) {
    $user_role = $_SESSION['role']; // Get user role (admin or employer)
    $uploader_username = $_SESSION['username']; // Get uploader's username from session
    $uploader_id = $_SESSION['user_id']; // Optionally, use user ID instead of username
    
    // Check if user is admin or employer
    if ($user_role === 'admin' || $user_role === 'employer') {
        // Sanitize input fields to prevent XSS
        $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
        $responsibilities = htmlspecialchars(trim($_POST['responsibilities']), ENT_QUOTES, 'UTF-8');
        $requirements = htmlspecialchars(trim($_POST['requirements']), ENT_QUOTES, 'UTF-8');
        $preferred_qualifications = htmlspecialchars(trim($_POST['preferred_qualifications']), ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');

        // Retrieve specific location (ensure it doesn't get set as NULL if empty)
        $specific_location = isset($_POST['specific_location']) && !empty(trim($_POST['specific_location'])) 
                              ? htmlspecialchars(trim($_POST['specific_location']), ENT_QUOTES, 'UTF-8') 
                              : null;

        // Ensure array input for categories and positions
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
        $positions = isset($_POST['positions']) ? array_map('intval', $_POST['positions']) : [];

       // Handle Thumbnail and Photo Uploads
$thumbnail_path = null;
$photo_path = null;

// Dynamically set upload directories based on user role
$thumbnail_dir = "../uploads/" . ($user_role === 'admin' ? 'admin_job_thumbnail/' : 'employer_job_thumbnail/');
$photo_dir = "../uploads/" . ($user_role === 'admin' ? 'admin_job_photo/' : 'employer_job_photo/');

// Function to handle file uploads
function uploadFile($file, $target_dir, $uploader_identifier) {
    if (!empty($file['name'])) {
        // Ensure the target directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
        }

        $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Additional file checks (size limit, file type, etc.)
        $max_size = 5 * 1024 * 1024; // 5MB max file size
        if (in_array($fileType, $allowed_types) && $file['size'] <= $max_size) {
            // Generate a unique file name using the uploader's identifier
            $original_name = pathinfo($file["name"], PATHINFO_FILENAME); // Get the original file name without extension
            $safe_file_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $original_name); // Sanitize file name
            $unique_file_name = $uploader_identifier . '_' . uniqid() . '.' . $fileType; // Add uploader identifier and unique ID

            $target_file = $target_dir . $unique_file_name;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return str_replace("../", "", $target_dir) . $unique_file_name; // Return relative path
            }
        }
    }
    return null;
}

// Upload the thumbnail and photo to their respective directories
$thumbnail_path = uploadFile($_FILES['thumbnail'], $thumbnail_dir, $uploader_username);
$photo_path = uploadFile($_FILES['photo'], $photo_dir, $uploader_username);

// Insert job post with a 'pending' status (for employer) or 'approved' status (for admin)
$status = ($user_role === 'admin') ? 'approved' : 'pending';  // Admin gets 'approved' status, others get 'pending'

// Prepare and execute the INSERT statement for the job post
$insert_stmt = $conn->prepare("INSERT INTO jobs (title, description, responsibilities, requirements, preferred_qualifications, location, specific_location, thumbnail, photo, status, employer_id) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("ssssssssssi", $title, $description, $responsibilities, $requirements, $preferred_qualifications, $location, $specific_location, $thumbnail_path, $photo_path, $status, $_SESSION['user_id']);

if ($insert_stmt->execute()) {
    $job_id = $conn->insert_id; // Get last inserted job ID

    // Insert into job_categories (Many-to-Many Relationship)
    if (!empty($categories)) {
        $category_stmt = $conn->prepare("INSERT INTO job_categories (job_id, category_id) VALUES (?, ?)");
        foreach ($categories as $category_id) {
            $category_stmt->bind_param("ii", $job_id, $category_id);
            $category_stmt->execute();
        }
    }

    // Insert into job_positions_jobs (Many-to-Many Relationship)
    if (!empty($positions)) {
        $position_stmt = $conn->prepare("INSERT INTO job_positions_jobs (job_id, position_id) VALUES (?, ?)");
        foreach ($positions as $position_id) {
            $position_stmt->bind_param("ii", $job_id, $position_id);
            $position_stmt->execute();
        }
    }

            // Different modals for admin and employer
            if ($user_role === 'admin') {
                // Admin modal for successful job post (approved and published)
                echo "
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
                <div class='modal fade show' id='successModal' tabindex='-1' aria-labelledby='successModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
                    <div class='modal-dialog modal-dialog-centered'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='successModalLabel'>Success!</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href='../pages/browse.php'\"></button>
                            </div>
                            <div class='modal-body'>
                                Job posted successfully and is now live on the browse page!
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-primary' onclick=\"window.location.href='../pages/browse.php'\">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
                ";
                exit();
            } else {
                // Employer modal for successful job post (pending approval)
                echo "
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
                <div class='modal fade show' id='pendingModal' tabindex='-1' aria-labelledby='pendingModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
                    <div class='modal-dialog modal-dialog-centered'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='pendingModalLabel'>Success!</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href='../pages/browse.php'\"></button>
                            </div>
                            <div class='modal-body'>
                                Job posted successfully! It is currently pending admin approval.
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-primary' onclick=\"window.location.href='../pages/browse.php'\">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
                ";
                exit();
            }
        } else {
            echo "<script>alert('Error posting job. Please try again.');</script>";
        }
    }
}

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/JOB/assets/post_job.css">
    <style>
        textarea {
            resize: none;
            overflow: hidden;
        }

        body{
            margin-top:24px;
            background-color: #f1f3f5 !important;
        }

        
    
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex items-center justify-center p-6">
    
    <div class="mt-4 max-w-4xl w-full bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-[#1976d2] mb-6">Post a New Job</h1>
        
        <form action="post_job.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Job Title -->
                <div>
                    <label class="block font-medium text-gray-700">Job Title</label>
                    <input type="text" name="title" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                </div>
                
                                <!-- Job Location -->
                                <div>
                    <label class="block font-medium text-gray-700">Specific Location (Optional)</label>
                    <input type="text" name="specific_location" placeholder="e.g., Building name, Street, Floor" value="<?php echo isset($_POST['specific_location']) ? htmlspecialchars($_POST['specific_location']) : ''; ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]">

                    <label class="block font-medium text-gray-700 mt-3">Barangay Location</label>
                    <select name="location" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <option value="">Select a location</option>
                        <?php
                        $query = "SELECT name FROM barangay ORDER BY name ASC";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_POST['location']) && $_POST['location'] == $row['name']) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($row['name']) . "\" $selected>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>


                <!-- Job Description -->
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700">Job Description</label>
                    <textarea name="description" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required></textarea>
                </div>
                
                <!-- Job Responsibilities -->
                <div>
                    <label class="block font-medium text-gray-700">Responsibilities</label>
                    <textarea name="responsibilities" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required></textarea>
                </div>
                
                <!-- Job Requirements -->
                <div>
                    <label class="block font-medium text-gray-700">Requirements</label>
                    <textarea name="requirements" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required></textarea>
                </div>
                
                <!-- Preferred Qualifications -->
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700">Preferred Qualifications</label>
                    <textarea name="preferred_qualifications" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]"></textarea>
                </div>
                
                <!-- Job Categories -->
                <div>
                    <label class="block font-medium text-gray-700">Categories</label>
                    <select name="categories[]" multiple class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-gray-500">Hold CTRL (or CMD on Mac) to select multiple.</small>
                </div>
                
                <!-- Job Positions -->
                <div>
                    <label class="block font-medium text-gray-700">Positions</label>
                    <select name="positions[]" multiple class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <?php while ($pos = $positions->fetch_assoc()): ?>
                            <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['position_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-gray-500">Hold CTRL (or CMD on Mac) to select multiple.</small>
                </div>
                <!-- Job Thumbnail -->
<div>
    <label class="block font-medium text-gray-700">Job Thumbnail</label>
    <input type="file" name="thumbnail" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" accept="image/*" required>
</div>

<!-- Attach Photo -->
<div>
    <label class="block font-medium text-gray-700">Attach Photo</label>
    <input type="file" name="photo" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" accept="image/*" required>
</div>

            </div><br>
        
    
    


            <!-- Submit Button -->
            <div class="flex justify-between">
                <button type="submit" class="w-full py-2 px-4 bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                <i class="fas fa-upload me-2"></i>Post Job
                </button>
                <button onclick="goBack()" type="button" class="ml-4 w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
            </div>
        </form>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Go back to the previous page in the browser history
        }

        // Auto-expand textarea functionality
        document.querySelectorAll('.auto-expand').forEach(textarea => {
            textarea.addEventListener('input', function () {
                this.style.height = 'auto'; // Reset height to recalculate
                this.style.height = this.scrollHeight + 'px'; // Set height to fit content
            });
        });
    </script>


</body>
</html>