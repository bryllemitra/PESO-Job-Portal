<?php
// Start session if necessary
session_start();
include '../includes/config.php'; // Include DB connection

// Include Dompdf library
require '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Check if user ID is set, otherwise exit
if (!isset($_SESSION['user_id'])) {
    exit('No user ID provided. Please log in.');
}

    $user_id = $_SESSION['user_id']; // Assuming you get the logged-in user's ID

    // Create a new Dompdf instance
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true); // Enable PHP inside the HTML
    $pdf = new Dompdf($options);

    $query = "
        SELECT u.*, b.name AS barangay_name 
        FROM users u 
        LEFT JOIN barangay b ON u.barangay = b.id
        WHERE u.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    if (!$user) {
        exit('User not found.');
    }


    // Fetch work experience
    $work_query = "SELECT * FROM work_experience WHERE user_id = ?";
    $work_stmt = $conn->prepare($work_query);
    $work_stmt->bind_param("i", $user_id);
    $work_stmt->execute();
    $work_result = $work_stmt->get_result();

    // Fetch skills (without proficiency)
    $skills_query = "SELECT s.skill_name FROM skills sk JOIN skill_list s ON sk.skill_id = s.id WHERE sk.user_id = ?";
    $skills_stmt = $conn->prepare($skills_query);
    $skills_stmt->bind_param("i", $user_id);
    $skills_stmt->execute();
    $skills_result = $skills_stmt->get_result();

    // Fetch education
    $education_query = "SELECT * FROM education WHERE user_id = ?";
    $education_stmt = $conn->prepare($education_query);
    $education_stmt->bind_param("i", $user_id);
    $education_stmt->execute();
    $education_result = $education_stmt->get_result();



    // Fetch character references
    $references_query = "SELECT * FROM `references` WHERE user_id = ?";
    $references_stmt = $conn->prepare($references_query);
    $references_stmt->bind_param("i", $user_id);
    $references_stmt->execute();
    $references_result = $references_stmt->get_result();


    // Fetch achievements
$achievements_query = "SELECT * FROM achievements WHERE user_id = ?";
$achievements_stmt = $conn->prepare($achievements_query);
$achievements_stmt->bind_param("i", $user_id);
$achievements_stmt->execute();
$achievements_result = $achievements_stmt->get_result();


// Fetch certificates
$certificates_query = "SELECT * FROM certificates WHERE user_id = ?";
$certificates_stmt = $conn->prepare($certificates_query);
$certificates_stmt->bind_param("i", $user_id);
$certificates_stmt->execute();
$certificates_result = $certificates_stmt->get_result();


// Fetch languages for the user with fluency level
$query_languages = "
    SELECT l.id, l.user_id, l.language_name, l.fluency
    FROM languages l
    WHERE l.user_id = ?
";
$stmt = $conn->prepare($query_languages);
$stmt->bind_param("i", $user_id);  // "i" for integer (user ID)
$stmt->execute();
$result_languages = $stmt->get_result();

// Fetch all language records into an array
$languages = [];
while ($row = $result_languages->fetch_assoc()) {
    $languages[] = $row;
}




// 1. Define all possible paths
$defaultImage = '../uploads/default/default_profile.png';
$imagePath = $defaultImage;

// 2. Check for user's uploaded file (multiple possible cases)
if (!empty($user['uploaded_file'])) {
    $uploadedFile = $user['uploaded_file'];
    
    // Case 1: Already full server path (e.g., '/var/www/html/uploads/filename.jpg')
    if (file_exists($uploadedFile)) {
        $imagePath = $uploadedFile;
    }
    // Case 2: Just filename (e.g., 'profile123.jpg')
    elseif (file_exists('../uploads/profile_user/'.$uploadedFile)) {
        $imagePath = '../uploads/profile_user/'.$uploadedFile;
    }
    // Case 3: Relative path from root (e.g., 'uploads/profile_user/profile123.jpg')
    elseif (file_exists('../'.$uploadedFile)) {
        $imagePath = '../'.$uploadedFile;
    }
    // Case 4: Absolute web path (e.g., '/JOB/uploads/profile123.jpg')
    elseif (file_exists($_SERVER['DOCUMENT_ROOT'].$uploadedFile)) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'].$uploadedFile;
    }
}

// 3. Final fallback if somehow all checks failed
if (!file_exists($imagePath)) {
    $imagePath = $defaultImage;
    error_log("Falling back to default image for user: ".$user_id);
}

// 4. Convert to base64
$imageData = @file_get_contents($imagePath);
if ($imageData === false) {
    error_log("Failed to read image at: ".$imagePath);
    $imageData = file_get_contents($defaultImage);
}
$base64 = 'data:image/'.pathinfo($imagePath, PATHINFO_EXTENSION).';base64,'.base64_encode($imageData);


$html = "
<html>
<head>
<style>
    @font-face {
        font-family: 'Lexend';
        src: url('/JOB/assets/fonts/lexend/Lexend-Light.ttf') format('truetype');
        font-weight: 300;
        font-style: normal;
    }

    body {
        font-family: 'Lexend', sans-serif;
        margin: 0;
        padding: 0;
        color: #333;
        line-height: 1.5;
        background-color: white;
    }

    .container {
        width: 90%;
        max-width: 600px;
        margin: 0 auto;
        padding: 30px 40px;
        background-color: white;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .header img {
        width: 170px;
        height: 170px;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        margin-bottom: 10px;
    }

    .header h2 {
        margin: 0;
        font-size: 24px;
        color: #222;
        letter-spacing: -0.5px;
        margin-bottom: 5px;
    }

    .header p {
        color: #666;
        font-size: 13px;
        margin: 3px 0;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin-top: 20px;
        color: #2b6cb0;
        padding-bottom: 3px;
        border-bottom: 2.5px solid #ebf8ff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px; /* Reduced from 12px */
    }

    .section-content {
        margin-bottom: 15px;
    }

    /* Education Section Styles */
    .education {
        margin-top: 10px;
    }

    .education .section-content {
        margin-top: 0;
        padding-top: 0;
    }

    .education-entry {
        margin-bottom: 10px;
        padding-bottom: 8px;
    }

    .education-entry:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .education-level {
        font-size: 14px;
        margin: 0 0 2px 0 !important;
        font-weight: 500;
    }

    .education-institution {
        font-size: 13px;
        margin: 0 0 2px 0 !important;
    }

    .education-address,
    .education-date {
        font-size: 12px;
        color: #555;
        margin: 0 0 2px 0 !important;
    }

    /* Other sections */
    .section-content p {
        font-size: 13px;
        margin: 4px 0; /* Tighter spacing */
    }

    .section-content ul {
        padding-left: 18px;
        margin: 6px 0; /* Tighter spacing */
    }

    .section-content li {
        margin-bottom: 3px; /* Tighter spacing */
    }

    .work-entry, .reference-entry {
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1.8px dashed #e2e8f0;
    }

    .work-entry:last-child, .reference-entry:last-child {
        border-bottom: none;
    }

    strong {
        color: #222;
        font-weight: 500;
    }

    @page {
        margin: 15mm;
        size: A4;
    }

    /* Modern touches */
    .work-entry p:first-child {
        font-size: 14px;
        margin-bottom: 3px;
    }

    .skills ul {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        list-style: none;
        padding-left: 0;
        margin: 6px 0;
    }

    .skills li {
        
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 12px;
    }

    .name {
    margin: 0;
    font-size: 28px;
    color: #1a202c;
    letter-spacing: -0.5px;
    margin-bottom: 10px;
    font-weight: 600;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-width: 400px;
    margin: 0 auto;
}

.contact-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #4a5568;
    font-size: 14px;
}

.contact-icon {
    width: 16px;
    height: 16px;
    fill: #2b6cb0;
}

/* Caption Styles */
.caption-container {
    background-color: #f8fafc;
    padding: 15px;
    margin: 20px 0;
    border-radius: 0 4px 4px 0;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.caption-icon {
    font-size: 20px;
    margin-top: 2px;
    color: #2b6cb0;
}

.caption {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #4a5568;
    font-style: italic;
    text-align: left;
}
</style>
</head>
<body>
<div class='container'>
<div class='header'>
    <!-- Profile Picture -->
    <img src='" . $base64 . "' alt='Profile Picture' class='profile-picture'>
    
    <!-- Name -->
    <h2 class='name'>" . htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'] . ' ' . $user['ext_name']) . "</h2>
    
    <!-- Contact Info -->
    <div class='contact-info'>
        <div class='contact-item'>
            <svg class='contact-icon' viewBox='0 0 24 24'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>
            <span>" . htmlspecialchars($user['street_address'] . ', ' . $user['barangay_name'] . ', ' . $user['city']) . "</span>
        </div>
        <div class='contact-item'>
            <svg class='contact-icon' viewBox='0 0 24 24'><path d='M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z'/></svg>
            <span>" . htmlspecialchars($user['phone_number']) . "</span>
        </div>
        <div class='contact-item'>
            <svg class='contact-icon' viewBox='0 0 24 24'><path d='M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z'/></svg>
            <span>" . htmlspecialchars($user['email']) . "</span>
        </div>
    </div>
</div>

<div class='caption-container'>
    <p class='caption'>" . htmlspecialchars($user['caption']) . "</p>
</div>

<!-- ===== Personal Information Section ===== -->
<div class='personal-info'>
    <div class='section-title'>Personal Information</div>
    <div class='section-content'>
        <p><strong>Birth Date:</strong> " . (!empty($user['birth_date']) ? htmlspecialchars(date('F j, Y', strtotime($user['birth_date']))) : "Not specified") . "</p>
        <p><strong>Age:</strong> " . (!empty($user['age']) ? htmlspecialchars($user['age']) : "N/A") . "</p>
        <p><strong>Gender:</strong> " . (!empty($user['gender']) ? htmlspecialchars($user['gender']) : "Not specified") . "</p>
        <p><strong>Weight:</strong> " . (!empty($user['weight']) ? htmlspecialchars(round($user['weight'])) . " kg" : "N/A") . "</p>
        <p><strong>Height:</strong> " . (!empty($user['height']) ? htmlspecialchars(round($user['height'])) . " cm" : "N/A") . "</p>
        <p><strong>Civil Status:</strong> " . (!empty($user['civil_status']) ? htmlspecialchars($user['civil_status']) : "Not specified") . "</p>
        <p><strong>Religion:</strong> " . (!empty($user['religion']) ? htmlspecialchars($user['religion']) : "Not specified") . "</p>
        <p><strong>Nationality:</strong> " . (!empty($user['nationality']) ? htmlspecialchars($user['nationality']) : "Not specified") . "</p>
        <p><strong>Languages:</strong> ";

        // Check if there are any languages and join them with a comma
        if (!empty($languages)) {
            $language_names = array_map(function($language) {
                return htmlspecialchars($language['language_name']);
            }, $languages);
            $html .= implode(', ', $language_names);
        } else {
            $html .= "Not specified";
        }

        $html .= "</p>
    </div>
</div>

<!-- ===== Skills Section ===== -->
<div class='skills'>
    <div class='section-title'>Skills</div>
    <div class='section-content'>
        <ul>";

while ($skill = $skills_result->fetch_assoc()) {
    $html .= "<li>" . htmlspecialchars($skill['skill_name']) . "</li>";
}

$html .= "</ul>
    </div>
</div>

<!-- ===== Achievements Section ===== -->
<br><div class='achievements'>
    <div class='section-title'>Achievements & Awards</div>
    <div class='section-content'>";

if ($achievements_result->num_rows > 0) {
    while ($achievement = $achievements_result->fetch_assoc()) {
        $html .= "
        <div class='achievement-entry'>
            <p><strong style='font-size:15px;'>" . htmlspecialchars($achievement['award_name']) . "</strong></p>
            <p>" . htmlspecialchars($achievement['organization']) . "</p>
            <p>" . date('F Y', strtotime($achievement['award_date'])) . "</p>";
        

        
        $html .= "</div>";
    }
} else {
    $html .= "<p>No achievements added yet.</p>";
}

$html .= "</div>
</div>

<br><div class='certificates'>
    <div class='section-title'>Training & Certificates</div>
    <div class='section-content'>";

// Check if there are certificates
if ($certificates_result->num_rows > 0) {
    // Loop through the certificates and display them
    while ($certificate = $certificates_result->fetch_assoc()) {
        $html .= "
        <div class='certificate-entry'>
            <p><strong style='font-size:15px;'>" . htmlspecialchars($certificate['certificate_name']) . "</strong></p>
            <p>" . htmlspecialchars($certificate['issuing_organization']) . "</p>
            <p>" . date('F Y', strtotime($certificate['issue_date'])) . "</p>";



        $html .= "</div>";
    }
} else {
    $html .= "<p>No training or certificates added yet.</p>";
}

$html .= "</div></div>

<!-- ===== Work Experience Section ===== -->
<br><div class='work-experience'>
    <div class='section-title'>Work Experience</div>
    <div class='section-content'>";

while ($work = $work_result->fetch_assoc()) {
    // Convert dates to the required format (month name, year - month name, year)
    $start_date = date("F, Y", strtotime($work['start_date']));
    
    // Handle end date: If it's empty, use 'Present' or just the start date
    if (!empty($work['end_date'])) {
        $end_date = date("F, Y", strtotime($work['end_date']));
    } else {
        $end_date = 'Present';
    }

    // Initialize the address and location text
    $location_text = '';
    $address = '';
    
    // Prepare the full job information
    $job_info = "<strong style='font-size:15px;'>" . htmlspecialchars($work['company_name']) . "</strong><br>" .
                htmlspecialchars($work['job_title']) . " (" . htmlspecialchars($work['employment_type']) . ")";

    // Location handling
    if ($work['job_location'] == 'overseas') {
        if (!empty($work['country'])) {
            $location_text = "Overseas at " . ucfirst(htmlspecialchars($work['country']));
        }
        
        if (!empty($work['address'])) {
            $address = "<p><strong></strong> " . htmlspecialchars($work['address']) . "</p>";
        }
    } else {
        if ($work['job_location'] == 'local' && !empty($work['address'])) {
            $address = "<p><strong></strong> " . htmlspecialchars($work['address']) . "</p>";
        }
    }

    // Combine all information for the work entry
    $html .= "
        <div class='work-entry'>
            <p>" . $job_info . "</p>";

    if (!empty($location_text)) {
        $html .= "<p>$location_text</p>";
    }

    if (!empty($address)) {
        $html .= $address;
    }

    $html .= "<p>$start_date - $end_date</p><br>
        </div>";
}

$html .= "</div>
</div>

<!-- ===== Education Section ===== -->
<div class='education'>
    <div class='section-title'>Education</div>
    <div class='section-content'>";

while ($education = $education_result->fetch_assoc()) {
    $education_level = ucfirst($education['education_level']);
    $institution = htmlspecialchars($education['institution']);
    $address = htmlspecialchars($education['address']);
    $completion_year = $education['status'] == 'Completed' ? $education['completion_year'] : $education['expected_completion_date'];

    $html .= "
        <div class='education-entry'>
            <p class='education-level'><strong style='font-size:15px;'>$education_level</strong></p>
            <p class='education-institution'>$institution</p>
            <p class='education-address'>$address</p>
            <p class='education-date'>$completion_year</p>
        </div>";
}

$html .= "</div>
</div>

<!-- ===== Job Preferences Section ===== -->
<div class='job-preferences'>
    <div class='section-title'>Job Preferences</div>
    <div class='section-content'>";

$job_preferences_query = "
    SELECT jp.work_type, jp.job_location, jp.employment_type, 
           GROUP_CONCAT(p.position_name ORDER BY p.position_name ASC) AS preferred_positions
    FROM job_preferences jp
    LEFT JOIN job_preferences_positions jp_pos ON jp.id = jp_pos.job_preference_id
    LEFT JOIN job_positions p ON jp_pos.position_id = p.id
    WHERE jp.user_id = ?
    GROUP BY jp.id
";
$job_preferences_stmt = $conn->prepare($job_preferences_query);
$job_preferences_stmt->bind_param("i", $user_id);
$job_preferences_stmt->execute();
$job_preferences_result = $job_preferences_stmt->get_result();

if ($job_pref = $job_preferences_result->fetch_assoc()) {
    $preferred_positions = !empty($job_pref['preferred_positions']) ? $job_pref['preferred_positions'] : 'Not specified';
    $preferred_positions_display = str_replace(',', ', ', $preferred_positions);

    $html .= "  
        <p>Preferred Occupation: $preferred_positions_display</p>
        <p>Work Type: " . htmlspecialchars($job_pref['work_type']) . "</p>
        <p>Work Location: " . htmlspecialchars($job_pref['job_location']) . "</p>
        <p>Employment Type: " . htmlspecialchars($job_pref['employment_type']) . "</p>";
}

$html .= "</div>
</div>

<!-- ===== References Section ===== -->
<br><div class='references'>
    <div class='section-title'>Character References</div>
    <div class='section-content'>";

while ($reference = $references_result->fetch_assoc()) {
    $html .= "
        <div class='reference-entry'>
            <p><strong style='font-size:15px;'>" . htmlspecialchars($reference['name']) . "</strong></p>
            <p>" . htmlspecialchars($reference['position']) . "</p>
            <p>" . htmlspecialchars($reference['workplace']) . "</p>
            <p>" . htmlspecialchars($reference['contact_number']) . "</p>
        </div><br>";
}

$html .= "</div>
        </div>

    </div>
</body>

</html>";

// Load HTML to Dompdf
$pdf->loadHtml($html);

// Set paper size and margins
$pdf->setPaper('A4', 'portrait');
$pdf->set_option('margin-top', 20);   // Top margin
$pdf->set_option('margin-right', 20); // Right margin
$pdf->set_option('margin-bottom', 20); // Bottom margin
$pdf->set_option('margin-left', 20);  // Left margin

// Render PDF
$pdf->render();

// Construct the file name using the user's first and last name
$file_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . '.pdf');

// Output PDF (force download) with dynamic file name
header("Content-type: application/pdf");
header("Content-Disposition: attachment; filename=$file_name");
echo $pdf->output();
exit();
?>
