<?php
// Include database connection and header
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';

// Get the request ID from the URL parameter
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID is provided, show a message and exit
if ($request_id == 0) {
    echo "Invalid request ID. Please go back to the employer requests list.";
    exit(); // Stop further script execution
}

// Fetch the specific employer request with the provided ID
$query = "SELECT er.*, u.first_name, u.last_name FROM employer_requests er
          JOIN users u ON er.user_id = u.id
          WHERE er.id = ?"; // Don't filter by status here
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $request_id); // Bind the request ID to the query
$stmt->execute();
$request_result = $stmt->get_result();

// Check if the request exists
if ($request_result->num_rows == 0) {
    echo "No such request found.";
    exit(); // Stop further script execution
}

// Fetch the request details (this should be only one result)
$request = $request_result->fetch_assoc();
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS (for modals) -->
 <!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="/JOB/assets/dropdown.css">

<style>
.btn-outline-primary:hover {
    background: #ffffff;  /* White background on hover */
    border-color: #28a745;  /* Green border */
    color: #28a745;  /* Green text */
    box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);  /* Light green shadow */
    transform: scale(1.05);  /* Slight zoom on hover */
}

.btn-outline-danger:hover {
    background: #ffffff;  /* White background on hover */
    border-color: #dc3545;  /* Red border */
    color: #dc3545;  /* Red text */
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);  /* Light red shadow */
    transform: scale(1.05);  /* Slight zoom on hover */
}



.btn-outline-secondary:hover{
    background: transparent;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Soft shadow effect */
    border-color: #4c6ef5;
    color: #4c6ef5;
}

.btn-light:hover{
    background: transparent;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Soft shadow effect */
    border-color: #4c6ef5;
    color: #4c6ef5;
}


body {
            background-color: #f1f3f5 !important;
        }

</style>


<div class="container py-5">
    <h2 class="text-center mb-4">Employer Request Details</h2>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Request Information Section -->
                    <div class="mb-4 mt-4">
                        <div><strong>Applicant:</strong> <?= htmlspecialchars($request['first_name']) ?> <?= htmlspecialchars($request['last_name']) ?></div>
                        <div><strong>Company Name:</strong> <?= htmlspecialchars($request['company_name']) ?></div>
                        <div><strong>Company Details:</strong>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($request['request_message'])) ?></p>
                        </div>
                        <div><small class="text-muted">Requested on: <?= date('M d, Y h:i A', strtotime($request['created_at'])) ?></small></div>
                    </div>

                    <!-- Proof Files Section -->
                    <div class="mb-4">
                        <small>Attached Files:</small><br>
                        <?php
                        // Fetch additional proof files for this request
                        $proof_query = "SELECT * FROM employer_request_proofs WHERE request_id = ?";
                        $proof_stmt = $conn->prepare($proof_query);
                        $proof_stmt->bind_param("i", $request['id']);
                        $proof_stmt->execute();
                        $proof_result = $proof_stmt->get_result();

                        if ($proof_result->num_rows > 0) {
                            while ($proof = $proof_result->fetch_assoc()) {
                                // Display the attached file and remove it after the request is accepted/rejected
                                echo '<a href="' . htmlspecialchars($proof['file_path']) . '" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">View Attached File</a><br>';
                            }
                        } else {
                            echo '<span>No additional proof files available.</span>';
                        }
                        ?>
                    </div>



                    <!-- Request Status Section -->
                    <div class="text-center">
                        <?php if ($request['status'] == 'pending'): ?>
                            <button data-request-id="<?= $request['id'] ?>" class="btn btn-outline-primary btn-approve mb-2">Approve Request</button>
                            <button data-request-id="<?= $request['id'] ?>" class="btn btn-outline-danger btn-reject mb-2">Reject Request</button>
                        <?php elseif ($request['status'] == 'accepted'): ?>
                            <!-- Display message when the request is accepted -->
                            <div class="alert alert-success mb-4">This request has already been accepted.</div>
                            <?php
                                // Automatically delete attached files after accepting the request
                                // Fetch the attached files
                                $proof_query = "SELECT * FROM employer_request_proofs WHERE request_id = ?";
                                $proof_stmt = $conn->prepare($proof_query);
                                $proof_stmt->bind_param("i", $request['id']);
                                $proof_stmt->execute();
                                $proof_result = $proof_stmt->get_result();

                                while ($proof = $proof_result->fetch_assoc()) {
                                    $file_path = $proof['file_path'];
                                    if (file_exists($file_path)) {
                                        unlink($file_path);  // Delete file from the server
                                    }
                                }

                                // Optionally, delete the proof records from the database
                                $delete_query = "DELETE FROM employer_request_proofs WHERE request_id = ?";
                                $delete_stmt = $conn->prepare($delete_query);
                                $delete_stmt->bind_param("i", $request['id']);
                                $delete_stmt->execute();
                            ?>
                        <?php elseif ($request['status'] == 'rejected'): ?>
                            <!-- Display message when the request is rejected -->
                            <div class="alert alert-danger mb-4">This request has already been rejected.</div>
                        <?php endif; ?>
                    </div>

                                        <!-- Rejection Reason -->
                                        <?php if ($request['status'] === 'rejected' && !empty($request['remark'])): ?>
                        <div class="alert alert-danger mb-4 text-center">
                            <strong>Rejection Reason:</strong> <?= htmlspecialchars($request['remark']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button Section -->
    <div class="text-center mt-4">
        <a href="javascript:history.back()" class="btn btn-light">Back</a>
    </div>
</div>



<!-- Modal for Rejecting Request -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Employer Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" method="POST">
                    <div class="mb-3">
                        <label for="remark" class="form-label">Reason for Rejection</label>
                        <textarea id="remark" name="remark" class="form-control" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="request_id" id="rejectRequestId">
                    <button type="submit" class="btn btn-primary">Submit Rejection</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Approving Request (Simple Confirmation) -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Employer Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this employer request? Please note that they will automatically be set as an employer once approved.</p>
                <form id="approveForm" method="POST">
                    <input type="hidden" name="request_id" id="approveRequestId">
                    <button type="submit" class="btn btn-primary">Yes, Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>


// Function to escape HTML entities (prevents XSS)
function escapeHtml(str) {
    return str.replace(/[&<>"'/]/g, function (char) {
        switch (char) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#039;';
            case '/': return '&#x2F;';
        }
    });
}

// Reject Request Button Click
document.querySelectorAll('.btn-reject').forEach(button => {
    button.addEventListener('click', function() {
        const requestId = this.getAttribute('data-request-id');
        document.getElementById('rejectRequestId').value = requestId;
        $('#rejectModal').modal('show');
    });
});

// Handle Reject Form Submission
$('#rejectForm').submit(function(e) {
    e.preventDefault();
    
    const requestId = $('#rejectRequestId').val();
    const remark = $('#remark').val();

    $.ajax({
        type: 'POST',
        url: 'reject_request.php', // Ensure this URL points to the correct PHP handler
        data: { request_id: requestId, remark: remark },
        dataType: 'json', // Expecting JSON response from the server
        success: function(response) {
            const message = response.status === 'success' 
                ? 'The employer request has been successfully rejected.' 
                : 'There was an error while rejecting the request. Please try again.';
            const sanitizedMessage = escapeHtml(message);

            // SweetAlert success message
            Swal.fire({
                icon: response.status === 'success' ? 'success' : 'error',
                title: response.status === 'success' ? 'Request Rejected!' : 'Error!',
                text: sanitizedMessage, // Ensure message is sanitized
                confirmButtonText: 'Okay',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload(); // Optionally reload the page
                }
            });
        },
        error: function(xhr, status, error) {
            // Handle AJAX request failure
            Swal.fire({
                icon: 'error',
                title: 'AJAX Error!',
                text: 'Failed to process your request. Please try again later.',
                confirmButtonText: 'OK'
            });
        }
    });
});

// Approve Request Button Click
document.querySelectorAll('.btn-approve').forEach(button => {
    button.addEventListener('click', function() {
        const requestId = this.getAttribute('data-request-id');
        document.getElementById('approveRequestId').value = requestId;
        $('#approveModal').modal('show');
    });
});

// Handle Approve Form Submission
$('#approveForm').submit(function(e) {
    e.preventDefault();
    
    const requestId = $('#approveRequestId').val();

    $.ajax({
        type: 'POST',
        url: 'approve_request.php', // Ensure this URL points to the correct PHP handler
        data: { request_id: requestId },
        dataType: 'json', // Expecting JSON response from the server
        success: function(response) {
            const message = response.status === 'success' 
                ? 'The employer request has been successfully approved and the role has been updated.'
                : 'There was an error while approving the request. Please try again.';
            const sanitizedMessage = escapeHtml(message);

            // SweetAlert success message
            Swal.fire({
                icon: response.status === 'success' ? 'success' : 'error',
                title: response.status === 'success' ? 'Request Approved!' : 'Error!',
                text: sanitizedMessage, // Ensure message is sanitized
                confirmButtonText: 'OK'
            }).then((result) => {
                location.reload(); // Reload the page to reflect the new role
            });
        },
        error: function(xhr, status, error) {
            // Handle AJAX request failure
            Swal.fire({
                icon: 'error',
                title: 'AJAX Error!',
                text: 'Failed to process your request. Please try again later.',
                confirmButtonText: 'OK'
            });
        }
    });
});


</script>



<?php include '../includes/footer.php'; ?>
