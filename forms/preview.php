<?php
session_start();


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
// Function to validate phone number
function validatePhoneNumber($phone) {
    return preg_match('/^\d{11}$/', $phone); // Phone number must be exactly 11 digits
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Retrieve query parameters
$data = [
    'surname' => trim($_GET['surname'] ?? ''),
    'first_name' => trim($_GET['first_name'] ?? ''),
    'middle_name' => trim($_GET['middle_name'] ?? ''),
    'suffix' => trim($_GET['suffix'] ?? ''),
    'email' => trim($_GET['email'] ?? ''),
    'phone' => trim($_GET['phone'] ?? ''),
    'address' => trim($_GET['address'] ?? ''),
    'age' => trim($_GET['age'] ?? ''),
    'dob' => trim($_GET['dob'] ?? ''),
    'pob' => trim($_GET['pob'] ?? ''),
    'gender' => trim($_GET['gender'] ?? ''),
    'marital_status' => trim($_GET['marital_status'] ?? ''),
    'weight' => trim($_GET['weight'] ?? ''),
    'height' => trim($_GET['height'] ?? ''),
    'nationality' => trim($_GET['nationality'] ?? ''),
    'religion' => trim($_GET['religion'] ?? ''),
    'languages' => trim($_GET['languages'] ?? ''),
    'profile' => trim($_GET['profile'] ?? ''),
    'tin' => trim($_GET['tin'] ?? ''),
    'disability' => trim($_GET['disability'] ?? ''),
    'house_number' => trim($_GET['house_number'] ?? ''),
    // New fields
    'disability' => trim($_GET['disability'] ?? ''),
    'employment_status' => trim($_GET['employment_status'] ?? ''),
    'employed_type' => trim($_GET['employed_type'] ?? ''),
    'unemployed_reason' => trim($_GET['unemployed_reason'] ?? ''),
    'ofw_status' => trim($_GET['ofw_status'] ?? ''),
    'former_ofw_status' => trim($_GET['former_ofw_status'] ?? ''),
    '4ps_beneficiary' => trim($_GET['4ps_beneficiary'] ?? ''),
    'preferred_occupation' => trim($_GET['preferred_occupation'] ?? ''),
    'preferred_work_location_local' => trim($_GET['preferred_work_location_local'] ?? ''),
    'preferred_work_location_overseas' => trim($_GET['preferred_work_location_overseas'] ?? ''),
    'languages_proficiency' => trim($_GET['languages_proficiency'] ?? ''),
    'currently_in_school' => trim($_GET['currently_in_school'] ?? ''),
    'educational_level' => trim($_GET['educational_level'] ?? ''),
    'training_courses' => trim($_GET['training_courses'] ?? ''),
    'institution' => trim($_GET['institution'] ?? ''),
    'skills_acquired' => trim($_GET['skills_acquired'] ?? ''),
    'license_type' => trim($_GET['license_type'] ?? ''),
    'license_number' => trim($_GET['license_number'] ?? ''),
    'issuing_agency' => trim($_GET['issuing_agency'] ?? ''),
    'most_recent_employment' => trim($_GET['most_recent_employment'] ?? ''),
    'other_skills' => trim($_GET['other_skills'] ?? ''),
    'signature' => trim($_GET['signature'] ?? ''),
];



// If all validations pass, store sanitized data in the session
$_SESSION['resume_data'] = $data;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Preview</title>
    <link rel="stylesheet" href="/JOB/assets/preview_resume.css">
</head>
<body>
    <div class="resume-container">
        <!-- Header Section -->
        <div class="header">
    <div class="name-container">
        <h1><?php echo htmlspecialchars($data['surname']); ?></h1>
        <h1><?php echo htmlspecialchars($data['first_name']); ?></h1>
        <h1><?php echo htmlspecialchars($data['middle_name']); ?></h1>
        <?php if (!empty($data['suffix'])): ?>
            <h1><?php echo htmlspecialchars($data['suffix']); ?></h1>
        <?php endif; ?>
    </div>
    <p><?php echo nl2br(htmlspecialchars($data['profile'])); ?></p>
</div>


        <!-- Contact Information -->
        <div class="contact-info">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" alt="Email Icon">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10z"/>
                </svg>
                <?php echo htmlspecialchars($data['email']); ?>
            </span>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" alt="Phone Icon">
                    <path d="M6.62 10.79c1.44 2.83 3.76 5.17 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                </svg>
                <?php echo htmlspecialchars($data['phone']); ?>
            </span>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" alt="Address Icon">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                </svg>
                <?php echo htmlspecialchars($data['address']); ?>
            </span>
        </div>

        <!-- Profile Section -->
        <div class="section">
            <div class="section-title">Profile</div>
            <table>
                <tr>
                    <th>Age</th>
                    <td><?php echo htmlspecialchars($data['age']); ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?php echo htmlspecialchars($data['dob']); ?></td>
                </tr>
                <tr>
                    <th>Place of Birth</th>
                    <td><?php echo htmlspecialchars($data['pob']); ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td><?php echo htmlspecialchars($data['gender']); ?></td>
                </tr>
                <tr>
                    <th>Marital Status</th>
                    <td><?php echo htmlspecialchars($data['marital_status']); ?></td>
                </tr>
                <tr>
                    <th>Weight</th>
                    <td><?php echo htmlspecialchars($data['weight']); ?> kg</td>
                </tr>
                <tr>
                    <th>Height</th>
                    <td><?php echo htmlspecialchars($data['height']); ?> cm</td>
                </tr>
                <tr>
                    <th>Nationality</th>
                    <td><?php echo htmlspecialchars($data['nationality']); ?></td>
                </tr>
                <tr>
                    <th>Religion</th>
                    <td><?php echo htmlspecialchars($data['religion']); ?></td>
                </tr>
                <tr>
                    <th>Languages</th>
                    <td><?php echo htmlspecialchars($data['languages']); ?></td>
                </tr>
                <tr>
                    <th>Tin</th>
                    <td><?php echo htmlspecialchars($data['tin']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Disability Section -->
        <div class="section">
            <div class="section-title">Disability</div>
            <table>
                <tr>
                    <th>Disability</th>
                    <td><?php echo htmlspecialchars($data['disability']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Employment Status -->
        <div class="section">
            <div class="section-title">Employment Status</div>
            <table>
                <tr>
                    <th>Status</th>
                    <td><?php echo htmlspecialchars($data['employment_status']); ?></td>
                </tr>
                <?php if ($data['employment_status'] === 'Employed'): ?>
                <tr>
                    <th>Type</th>
                    <td><?php echo htmlspecialchars($data['employed_type']); ?></td>
                </tr>
                <?php elseif ($data['employment_status'] === 'Unemployed'): ?>
                <tr>
                    <th>Reason</th>
                    <td><?php echo htmlspecialchars($data['unemployed_reason']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>OFW Status</th>
                    <td><?php echo htmlspecialchars($data['ofw_status']); ?></td>
                </tr>
                <tr>
                    <th>Former OFW Status</th>
                    <td><?php echo htmlspecialchars($data['former_ofw_status']); ?></td>
                </tr>
                <tr>
                    <th>4Ps Beneficiary</th>
                    <td><?php echo htmlspecialchars($data['4ps_beneficiary']); ?></td>
                </tr>
                <tr>
                    <th>Household ID No.</th>
                    <td><?php echo htmlspecialchars($data['house_number']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Job Preference -->
        <div class="section">
            <div class="section-title">Job Preference</div>
            <table>
                <tr>
                    <th>Preferred Occupation</th>
                    <td><?php echo htmlspecialchars($data['preferred_occupation']); ?></td>
                </tr>
                <tr>
                    <th>Preferred Work Location (Local)</th>
                    <td><?php echo htmlspecialchars($data['preferred_work_location_local']); ?></td>
                </tr>
                <tr>
                    <th>Preferred Work Location (Overseas)</th>
                    <td><?php echo htmlspecialchars($data['preferred_work_location_overseas']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Language / Dialect Proficiency -->
        <div class="section">
            <div class="section-title">Language / Dialect Proficiency</div>
            <table>
                <tr>
                    <th>Languages/Dialects</th>
                    <td><?php echo htmlspecialchars($data['languages_proficiency']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Educational Background -->
        <div class="section">
            <div class="section-title">Educational Background</div>
            <table>
                <tr>
                    <th>Currently in School</th>
                    <td><?php echo htmlspecialchars($data['currently_in_school']); ?></td>
                </tr>
                <tr>
                    <th>Educational Level</th>
                    <td><?php echo htmlspecialchars($data['educational_level']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Technical/Vocational and Other Training -->
        <div class="section">
            <div class="section-title">Technical/Vocational and Other Training</div>
            <table>
                <tr>
                    <th>Courses</th>
                    <td><?php echo htmlspecialchars($data['training_courses']); ?></td>
                </tr>
                <tr>
                    <th>Institution</th>
                    <td><?php echo htmlspecialchars($data['institution']); ?></td>
                </tr>
                <tr>
                    <th>Skills Acquired</th>
                    <td><?php echo htmlspecialchars($data['skills_acquired']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Eligibility / Professional License -->
        <div class="section">
            <div class="section-title">Eligibility / Professional License</div>
            <table>
                <tr>
                    <th>Type of License</th>
                    <td><?php echo htmlspecialchars($data['license_type']); ?></td>
                </tr>
                <tr>
                    <th>License Number</th>
                    <td><?php echo htmlspecialchars($data['license_number']); ?></td>
                </tr>
                <tr>
                    <th>Issuing Agency</th>
                    <td><?php echo htmlspecialchars($data['issuing_agency']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Work Experience -->
        <div class="section">
            <div class="section-title">Work Experience</div>
            <table>
                <tr>
                    <th>Most Recent Employment</th>
                    <td><?php echo htmlspecialchars($data['most_recent_employment']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Other Skills Acquired Without Certificate -->
        <div class="section">
            <div class="section-title">Other Skills Acquired Without Certificate</div>
            <table>
                <tr>
                    <th>Skills</th>
                    <td><?php echo htmlspecialchars($data['other_skills']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Certification/Authorization -->
        <div class="section">
            <div class="section-title">Certification/Authorization</div>
            <table>
                <tr>
                    <th>Signature</th>
                    <td><?php echo htmlspecialchars($data['signature']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="back-button" onclick="window.location.href='forms.php'">Back to Form</button>
            <button class="download-button" onclick="window.location.href='generate.php'">Download Resume</button>
        </div>
    </div>
</body>
</html>