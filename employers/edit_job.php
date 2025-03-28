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

// Validate Job ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Invalid Job ID</h5>
                </div>
                <div class='modal-body'>
                    No valid job ID was provided. Please select a job to edit.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick=\"window.location.href='job_list.php'\">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    ";
    exit();
}

$id = $_GET['id'];

// Fetch job details with categories & positions
$stmt = $conn->prepare("
    SELECT 
        j.*, 
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS categories, 
        GROUP_CONCAT(DISTINCT p.position_name ORDER BY p.position_name SEPARATOR ', ') AS positions
    FROM jobs j
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN categories c ON jc.category_id = c.id
    LEFT JOIN job_positions p ON j.id = p.category_id
    WHERE j.id = ?
    GROUP BY j.id
");
$stmt->bind_param("i", $id);
$stmt->execute();
$jobResult = $stmt->get_result();
$job = $jobResult->fetch_assoc();

if (!$job) {
    die("Job not found.");
}

// Fetch job categories from the database in alphabetical order
$category_stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$category_stmt->execute();
$categories = $category_stmt->get_result();

// Fetch job positions from the database in alphabetical order
$position_stmt = $conn->prepare("SELECT id, position_name FROM job_positions ORDER BY position_name ASC");
$position_stmt->execute();
$positions = $position_stmt->get_result();

// Define allowed image file types
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

// Handle job update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['role'])) {
    $user_role = $_SESSION['role']; // Get user role (admin or employer)
    $uploader_username = $_SESSION['username']; // Get uploader's username from session
    $uploader_id = $_SESSION['user_id']; // Optionally, use user ID instead of username

    // Ensure that admin and employer can edit
    if ($user_role === 'admin' || $user_role === 'employer') {
        // Trim and validate inputs (no premature encoding)
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $responsibilities = trim($_POST['responsibilities']);
        $requirements = trim($_POST['requirements']);
        $preferred_qualifications = trim($_POST['preferred_qualifications']);
        $category_ids = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : []; // Ensure array input
        $position_ids = isset($_POST['positions']) ? array_map('intval', $_POST['positions']) : []; // Ensure array input
        $location = trim($_POST['location']);
        $specific_location = isset($_POST['specific_location']) && !empty(trim($_POST['specific_location']))
        ? trim($_POST['specific_location'])
        : null;
        // Convert selected categories and positions into comma-separated values (for internal processing)
        $category_list = implode(',', $category_ids);
        $position_list = implode(',', $position_ids);

        // Set directory paths based on user role
if ($user_role === 'admin') {
    // Admin directories
    $thumbnail_target_dir = "../uploads/admin_job_thumbnail/";
    $photo_target_dir = "../uploads/admin_job_photo/";
} else {
    // Employer directories
    $thumbnail_target_dir = "../uploads/employer_job_thumbnail/";
    $photo_target_dir = "../uploads/employer_job_photo/";
}

// Ensure the target directories exist, if not, create them
if (!is_dir($thumbnail_target_dir)) {
    mkdir($thumbnail_target_dir, 0777, true); // Create directory if it doesn't exist
}
if (!is_dir($photo_target_dir)) {
    mkdir($photo_target_dir, 0777, true); // Create directory if it doesn't exist
}

// Function to handle file uploads with unique naming
function uploadFile($file, $target_dir, $uploader_identifier) {
    if (!empty($file['name'])) {
        // Allowed file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // File type and size validation
        $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
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

// Track old file paths for deletion later
$old_thumbnail_path = $job['thumbnail'];
$old_photo_path = $job['photo'];

// Handle thumbnail upload
$thumbnail_path = $old_thumbnail_path; // Default to existing thumbnail if no new file is uploaded
if (!empty($_FILES['thumbnail']['name'])) {
    $thumbnail_path = uploadFile($_FILES['thumbnail'], $thumbnail_target_dir, $uploader_username);
    if (!$thumbnail_path) {
        echo "<script>alert('Error uploading thumbnail image.');</script>";
    } else {
        // Delete the old thumbnail file if it exists
        if ($old_thumbnail_path && file_exists("../" . $old_thumbnail_path)) {
            unlink("../" . $old_thumbnail_path);
        }
    }
}

// Handle photo upload
$photo_path = $old_photo_path; // Default to existing photo if no new file is uploaded
if (!empty($_FILES['photo']['name'])) {
    $photo_path = uploadFile($_FILES['photo'], $photo_target_dir, $uploader_username);
    if (!$photo_path) {
        echo "<script>alert('Error uploading job photo.');</script>";
    } else {
        // Delete the old photo file if it exists
        if ($old_photo_path && file_exists("../" . $old_photo_path)) {
            unlink("../" . $old_photo_path);
        }
    }
}


        // Set the job status based on current job status and user role
        if ($job['status'] === 'approved') {
            // If the job is already approved, keep it as 'approved'
            $status = 'approved';
        } else {
            // If it's not approved yet, set it as 'pending' if an employer is editing
            $status = ($user_role === 'admin') ? 'approved' : 'pending';
        }

        // Prepare the update query
        $query = "
            UPDATE jobs 
            SET title = ?, description = ?, responsibilities = ?, requirements = ?, 
                preferred_qualifications = ?, location = ?, specific_location = ?, 
                thumbnail = ?, photo = ?, status = ?
            WHERE id = ?
        ";

        // Prepare the statement
        $update_stmt = $conn->prepare($query);



        // Bind parameters
        $update_stmt->bind_param(
            "ssssssssssi", 
            $title, 
            $description, 
            $responsibilities, 
            $requirements, 
            $preferred_qualifications,  // Nullable
            $location, 
            $specific_location, 
            $thumbnail_path, 
            $photo_path,  // Nullable
            $status,
            $id
        );

        // Execute the update statement
        if ($update_stmt->execute()) {
            // Clear previous category and position associations
            $delete_categories_stmt = $conn->prepare("DELETE FROM job_categories WHERE job_id = ?");
            $delete_categories_stmt->bind_param("i", $id);
            $delete_categories_stmt->execute();

            $delete_positions_stmt = $conn->prepare("DELETE FROM job_positions_jobs WHERE job_id = ?");
            $delete_positions_stmt->bind_param("i", $id);
            $delete_positions_stmt->execute();

            // Insert the selected categories
            foreach ($category_ids as $category_id) {
                $insert_category_stmt = $conn->prepare("INSERT INTO job_categories (job_id, category_id) VALUES (?, ?)");
                $insert_category_stmt->bind_param("ii", $id, $category_id);
                $insert_category_stmt->execute();
            }

            // Insert the selected positions
            foreach ($position_ids as $position_id) {
                $insert_position_stmt = $conn->prepare("INSERT INTO job_positions_jobs (job_id, position_id) VALUES (?, ?)");
                $insert_position_stmt->bind_param("ii", $id, $position_id);
                $insert_position_stmt->execute();
            }

            // Modal for successful update (admin or employer)
            echo "
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
            <div class='modal fade show' id='successModal' tabindex='-1' aria-hidden='false' style='display: block;'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Success!</h5>
                            <button type='button' class='btn-close' onclick=\"window.location.href='job_list.php'\"></button>
                        </div>
                        <div class='modal-body'>
                            Job updated successfully!
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-primary' onclick=\"window.location.href='job_list.php'\">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class='modal-backdrop fade show'></div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
            ";
            exit();
        } else {
            echo "<script>alert('Error updating job. Please try again.');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Post</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/JOB/assets/edit_job.css">
    <style>
        textarea {
            resize: none;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex items-center justify-center p-6 mt-4">
    
    <div class="max-w-4xl w-full bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-[#1976d2] mb-6">Edit Job Post</h1>
        
        <form action="edit_job.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block font-medium text-gray-700">Job Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                </div>
                
                <!-- Job Location -->
                <div>
                    <label class="block font-medium text-gray-700">Specific Location (Optional)</label>
                    <input type="text" name="specific_location" placeholder="e.g., Building name, Street, Floor" 
                        value="<?php echo isset($_POST['specific_location']) ? htmlspecialchars($_POST['specific_location']) : ''; ?>" 
                        class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]">

                    <label class="block font-medium text-gray-700 mt-3">Barangay Location</label>
                    <select name="location" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <option value="">Select a location</option>
                        <?php
                        $query = "SELECT name FROM barangay ORDER BY name ASC";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            // Pre-select if the location was already chosen
                            $selected = (isset($_POST['location']) && $_POST['location'] == $row['name']) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($row['name']) . "\" $selected>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700">Job Description</label>
                    <textarea name="description" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700">Responsibilities</label>
                    <textarea name="responsibilities" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required><?= htmlspecialchars($job['responsibilities']) ?></textarea>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700">Requirements</label>
                    <textarea name="requirements" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required><?= htmlspecialchars($job['requirements']) ?></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700">Preferred Qualifications</label>
                    <textarea name="preferred_qualifications" rows="2" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]"> <?= htmlspecialchars($job['preferred_qualifications']) ?></textarea>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700">Categories</label>
                    <select name="categories[]" multiple class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <?php 
                        $selected_categories = explode(',', $job['categories']);
                        while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selected_categories) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-gray-500">Hold CTRL (or CMD on Mac) to select multiple.</small>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700">Positions</label>
                    <select name="positions[]" multiple class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" required>
                        <?php 
                        $selected_positions = explode(',', $job['positions']);
                        while ($pos = $positions->fetch_assoc()): ?>
                            <option value="<?= $pos['id'] ?>" <?= in_array($pos['id'], $selected_positions) ? 'selected' : '' ?>><?= htmlspecialchars($pos['position_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-gray-500">Hold CTRL (or CMD on Mac) to select multiple.</small>
                </div>
                
                <div>
                <label class="block font-medium text-gray-700">Job Thumbnail</label>
                <!-- Display the current thumbnail if exists -->
                <?php if (!empty($job['thumbnail'])): ?>
                    <div class="mb-2">
                        <img src="../<?= $job['thumbnail'] ?>" alt="Current Thumbnail" class="w-32 h-32 object-cover rounded-md" />
                    </div>
                <?php endif; ?>
                <input type="file" name="thumbnail" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" accept="image/*">
            </div>

        <!-- Attach Photo -->
        <div>
            <label class="block font-medium text-gray-700">Attach Photo</label>
            <!-- Display the current photo if exists -->
            <?php if (!empty($job['photo'])): ?>
                <div class="mb-2">
                    <img src="../<?= $job['photo'] ?>" alt="Current Photo" class="w-32 h-32 object-cover rounded-md" />
                </div>
            <?php endif; ?>
            <input type="file" name="photo" class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1976d2]" accept="image/*">
        </div>
            </div>
            
            <div class="flex justify-between mt-6">
                <button type="submit" class="w-full py-2 px-4 bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                    <i class="fas fa-save me-2"></i>Update Job
                </button>
                <button onclick="goBack()" type="button" class="ml-4 w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
            </div>
        </form>
    </div>

    <script>
        // Auto-expand textarea functionality
        document.querySelectorAll('.auto-expand').forEach(textarea => {
            // Set initial height based on content
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

            // Add event listener for dynamic expansion
            textarea.addEventListener('input', function () {
                this.style.height = 'auto'; // Reset height to recalculate
                this.style.height = this.scrollHeight + 'px'; // Set height to fit content
            });
        });

        function goBack() {
            window.history.back(); // Go back to the previous page in the browser history
        }
    </script>


</body>
</html>
