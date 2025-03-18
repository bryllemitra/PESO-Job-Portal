<?php
// Include database connection and header
include '../includes/config.php';
include '../includes/header.php';

// Restrict access: Show modal and redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                </div>
                <div class='modal-body'>
                    You must be logged in to view this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick=\"window.location.href='login.php'\">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    ";
    exit();
}

// Check if the user is logged in and fetch their details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check user role
$user_role = $user['role'];

// Check if the user has already submitted a request
$request_check_query = "SELECT * FROM employer_requests WHERE user_id = ? AND (status = 'pending' OR status = 'rejected')";
$request_check_stmt = $conn->prepare($request_check_query);
$request_check_stmt->bind_param("i", $user_id);
$request_check_stmt->execute();
$request_check_result = $request_check_stmt->get_result();

// Handle the request submission for users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_role === 'user') {
    // Check if the user has already sent a request
    if ($request_check_result->num_rows > 0) {
        // If the request is pending, show a message and don't allow the form submission
        $message = "You have already sent a request. Please wait for the admin's approval.";
    } else {
        $request_message = $_POST['request_message'];
        $company_name = $_POST['company_name']; // Added company name

        // Handle multiple file uploads
        $proof_files = $_FILES['proof_file'];

        // Insert the request into the database
        $stmt = $conn->prepare("INSERT INTO employer_requests (user_id, request_message, company_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $request_message, $company_name); // Add company name in the insert query
        $stmt->execute();
        
        $request_id = $stmt->insert_id; // Get the inserted request's ID
        
        // Save each proof file
        $file_count = count($proof_files['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $file_name = $proof_files['name'][$i];
            $file_tmp = $proof_files['tmp_name'][$i];
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($file_name);

            if (move_uploaded_file($file_tmp, $target_file)) {
                // Insert the proof file into the employer_request_proofs table
                $stmt_proof = $conn->prepare("INSERT INTO employer_request_proofs (request_id, file_path) VALUES (?, ?)");
                $stmt_proof->bind_param("is", $request_id, $target_file);
                $stmt_proof->execute();
            }
        }

        $message = "Your request has been submitted successfully! Please wait for the admin's approval.";
    }
}

// Fetch rejection remark if the request is rejected
$rejection_message = '';
if ($request_check_result->num_rows > 0) {
    $request_row = $request_check_result->fetch_assoc();
    if ($request_row['status'] === 'rejected') {
        $rejection_message = $request_row['remark'];
    }
}

?>

<style>
.btn-outline-secondary:hover{
    background: transparent;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Soft shadow effect */
    border-color: #4c6ef5;
    color: #4c6ef5;
}

body{
    background-color: #f1f3f5;
}
 </style>

<div class="container py-5">
    <h2 class="text-center mb-4">
        <?php if ($user_role === 'admin'): ?>
            Recent Employer Requests
        <?php elseif ($user_role === 'employer'): ?>
            Welcome to the Employer Community!
        <?php elseif ($user_role === 'user'): ?>
            Become a Part of the Employer Community
        <?php endif; ?>
    </h2>

    <div class="row justify-content-center">
        <!-- Left Column: Content for Admin, Employer, or User -->
        <?php if ($user_role === 'admin'): ?>
            <!-- Admin-Specific Content: Display Recent Requests -->
            <div class="col-lg-8 col-md-10">
                <?php
                // Fetch recent employer requests for admin
                $requests_query = "SELECT * FROM employer_requests ORDER BY created_at DESC LIMIT 5"; // Limit to 5 most recent requests
                $requests_result = $conn->query($requests_query);
                ?>
                <?php if ($requests_result->num_rows > 0): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Recent Requests</h4>
                            <div class="list-group">
                                <?php while ($request = $requests_result->fetch_assoc()): ?>
                                    <?php
                                    // Fetch the user details based on the user_id in the request
                                    $user_query = "SELECT * FROM users WHERE id = ?";
                                    $user_stmt = $conn->prepare($user_query);
                                    $user_stmt->bind_param("i", $request['user_id']);
                                    $user_stmt->execute();
                                    $user_result = $user_stmt->get_result();
                                    $user_data = $user_result->fetch_assoc();
                                    ?>
                                    <div class="list-group-item p-4">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>ID:</strong> <?= htmlspecialchars($user_data['id']) ?><br>
                                                <strong>Name:</strong> <?= htmlspecialchars($user_data['first_name']) ?> <?= htmlspecialchars($user_data['last_name']) ?><br>
                                                <strong>Company Name:</strong> <?= htmlspecialchars($request['company_name']) ?><br>
                                                <small class="text-muted">Requested on: <?= date('M d, Y h:i A', strtotime($request['created_at'])) ?></small>
                                            </div>
                                            <div class="align-self-center">
                                                <a href="/JOB/admin/employer_approval.php?id=<?= $request['id'] ?>" class="btn btn-outline-secondary">View Request</a>
                                            </div>
                                        </div>

                                        <!-- Show rejection reason if rejected -->
                                        <?php if ($request['status'] === 'rejected' && !empty($request['remark'])): ?>
                                            <div class="mt-3">
                                                <strong class="text-danger">Rejection Reason:</strong> <?= htmlspecialchars($request['remark']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center">No recent requests found.</p>
                <?php endif; ?>
                <!-- Redirecting Admin to Admin Request Page -->
                <div class="text-center mt-4">
                    <a href="../admin/admin.php" class="btn btn-primary">Go to Admin Panel</a>
                </div>
            </div>
        <?php elseif ($user_role === 'employer'): ?>
            <!-- Employer-Specific Content: Display a Warm Welcome Message -->
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title mb-3">Congratulations, <?= htmlspecialchars($user['first_name']) ?>!</h3>
                        <p class="card-text">You’ve officially joined the employer community. You can now post job opportunities, manage applicants, and build your team!</p>
                        <a href="../employers/dashboard.php" class="btn btn-primary">Go to Your Dashboard</a>
                    </div>
                </div>
            </div>
        <?php elseif ($user_role === 'user'): ?>
            <!-- Regular User Content: Employer Request Form -->
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-success mb-4"><?= $message ?></div>
                        <?php elseif (isset($error)): ?>
                            <div class="alert alert-danger mb-4"><?= $error ?></div>
                        <?php elseif ($request_check_result->num_rows === 0): ?>
                            <!-- Show the request form only if no request is pending -->
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="request_message" class="form-label">Company Description</label>
                                    <textarea id="request_message" name="request_message" rows="5" class="form-control" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="proof_file" class="form-label">Upload Proof (e.g., business registration, etc.)</label>
                                    <input type="file" id="proof_file" name="proof_file[]" class="form-control" required multiple>
                                </div>

                                <!-- Dynamically added proof file inputs -->
                                <div id="additionalProofs"></div>

                                <button type="button" class="btn btn-secondary mt-3" id="addProofButton">+ Add Another Proof File</button>

                                <br><button type="submit" class="btn btn-primary btn-lg mt-4">Submit Request</button>
                            </form>
                        <?php elseif ($request_row['status'] === 'rejected'): ?>
                            <!-- Show rejection remark if the request was rejected -->
                            <div class="alert alert-danger text-center mb-4">
                                <strong>Request Rejected:</strong><br><br> <?= $rejection_message ?>
                            </div>
                            <!-- Button to allow the user to resend the request -->
                            <form method="POST" action="resubmit_request.php">
                                <input type="hidden" name="request_id" value="<?= $request_row['id'] ?>">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg mt-4">Resubmit Request</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Message when the user has already sent a request and is still pending -->
                            <div class="alert alert-warning mb-4">
                                You have already sent a request. Please wait for the admin's approval.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>




                <!-- JS for dynamically adding proof file inputs -->
                <script>
                    document.getElementById('addProofButton').addEventListener('click', function() {
                        var inputElement = document.createElement('input');
                        inputElement.type = 'file';
                        inputElement.name = 'proof_file[]'; 
                        inputElement.classList.add('form-control');
                        
                        var lineBreak = document.createElement('br');
                        
                        document.getElementById('additionalProofs').appendChild(inputElement);
                        document.getElementById('additionalProofs').appendChild(lineBreak);
                    });
                </script>

          

<?php include '../includes/footer.php'; ?>
