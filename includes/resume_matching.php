<?php

include '../includes/config.php';  // Include DB connection

require_once '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Get user_id from session
} else {
    die("User is not logged in or user_id is missing.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {

    // Validate if the uploaded file is empty
    if ($_FILES["resume"]["size"] == 0) {
        echo "<script>
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Please select a file to upload.',
                });
              </script>";
    } else {
        // Define upload directory and file type
        $target_dir = "../uploads/resumes/";
        $fileType = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));

        // Only allow PDF files
        if ($fileType !== 'pdf') {
            echo "<script>
                    Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Only PDF files are allowed.',
                    });
                  </script>";
        }

        $target_file = $target_dir . "user_resume.pdf"; // Define the target path
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {

            // Parse the PDF file
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($target_file);
            $text = $pdf->getText();  // Extract text from PDF

            // Extract keywords from resume text
            $keywords = extractKeywords($text);  // This is where you'd extract relevant data (customize this)

            // Fetch and match personal data
            matchPersonalData($text);

            // Fetch and match skills
            matchSkills($keywords);

            // Fetch and match work experience
            matchWorkExperience($keywords);

            // Fetch and match education
            matchEducation($keywords);

            // Fetch and match references
            matchReferences($keywords);

            // Fetch and match achievements
            matchAchievements($keywords);

            // Fetch and match certificates
            matchCertificates($keywords);

            echo "<script>
                    Swal.fire({
                      icon: 'success',
                      title: 'Success!',
                      text: 'Resume uploaded and matched successfully.',
                    }).then(function() {
                        window.location.href = 'profile.php?id=$user_id'; // Redirect after upload
                    });
                  </script>";
        } else {
            echo "<script>
                    Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Sorry, there was an error uploading your file.',
                    });
                  </script>";
        }
    }
}

// Function to extract keywords from the resume text (customize this to your needs)
function extractKeywords($text) {
    // Example: you can use NLP techniques or basic keyword matching here
    $keywords = [];
    $keywords[] = 'PHP';  // example skill
    $keywords[] = 'JavaScript';  // example skill
    $keywords[] = 'SQL';  // example skill
    // Add more logic here to extract actual skills/experiences from $text
    return $keywords;
}

// Function to match and store personal data in the database
function matchPersonalData($text) {
    global $conn, $user_id;

    // Attempt to extract personal details (you can use regex, NLP, or custom patterns)
    $gender = extractGender($text);
    $birth_date = extractBirthDate($text);
    $age = extractAge($text);
    $phone_number = extractPhoneNumber($text);
    $place_of_birth = extractPlaceOfBirth($text);
    $civil_status = extractCivilStatus($text);  // Added missing function
    $address = extractAddress($text);  // Could be a string or array depending on implementation
    $weight = extractWeight($text);
    $height = extractHeight($text);
    $religion = extractReligion($text);
    $nationality = extractNationality($text);

    // Check if $address is an array or string and handle accordingly
    if (is_array($address)) {
        // Assuming $address array contains 'street' and 'city' as example
        $street = $address['street'] ?? NULL;
        $city = $address['city'] ?? NULL;
    } else {
        // If address is a single string
        $street = $address;
        $city = NULL;
    }

    // Update user data in the database (if not already present or needs updating)
    $update_query = "UPDATE users SET gender = ?, birth_date = ?, age = ?, phone_number = ?, place_of_birth = ?, civil_status = ?, street_address = ?, city = ?, weight = ?, height = ?, religion = ?, nationality = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssissssddsssi", $gender, $birth_date, $age, $phone_number, $place_of_birth, $civil_status, $street, $city, $weight, $height, $religion, $nationality, $user_id);
    $stmt->execute();
}



// Example function to extract civil status from resume (implement with regex or NLP)
function extractCivilStatus($text) {
    if (stripos($text, 'Single') !== false) {
        return 'Single';
    } elseif (stripos($text, 'Married') !== false) {
        return 'Married';
    } elseif (stripos($text, 'Divorced') !== false) {
        return 'Divorced';
    } elseif (stripos($text, 'Widowed') !== false) {
        return 'Widowed';
    }
    return NULL;
}
// Example function to extract skills from the resume (match skills with database or predefined list)
function matchSkills($keywords) {
    global $conn, $user_id;

    // Loop through the extracted skills from the resume
    foreach ($keywords as $skill) {
        $skill = trim($skill);

        // Check if skill already exists in the skills table for the user
        // The skills table uses a skill_id from the skill_list table, so we will check that
        $query = "
            SELECT sk.skill_id
            FROM skills sk
            JOIN skill_list s ON sk.skill_id = s.id
            WHERE sk.user_id = ? AND s.skill_name = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $skill);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Check if the skill exists in the skill_list table, if not, insert it
            $check_skill_query = "SELECT id FROM skill_list WHERE skill_name = ?";
            $check_stmt = $conn->prepare($check_skill_query);
            $check_stmt->bind_param("s", $skill);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                // Insert new skill into skill_list if not already present
                $insert_skill_query = "INSERT INTO skill_list (skill_name) VALUES (?)";
                $insert_stmt = $conn->prepare($insert_skill_query);
                $insert_stmt->bind_param("s", $skill);
                $insert_stmt->execute();

                // Get the inserted skill's id
                $skill_id = $insert_stmt->insert_id;
            } else {
                // If skill exists, get the id
                $row = $check_result->fetch_assoc();
                $skill_id = $row['id'];
            }

            // Now insert the skill into the skills table for the user
            $insert_query = "INSERT INTO skills (user_id, skill_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ii", $user_id, $skill_id);
            $stmt->execute();
        }
    }
}


// Function to match and store work experience in the database
function matchWorkExperience($experience_keywords) {
    global $conn, $user_id;

    foreach ($experience_keywords as $experience) {
        $experience = trim($experience);  // Clean up any unnecessary spaces or newlines

        // Updated query to check job description (or use job_title if applicable)
        $query = "SELECT * FROM work_experience WHERE user_id = ? AND job_description LIKE ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $experience);  // Assuming experience is a string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert experience if it doesn't exist
            $insert_query = "INSERT INTO work_experience (user_id, job_description) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("is", $user_id, $experience);
            $insert_stmt->execute();
        }
    }
}


// Function to match and store education in the database
function matchEducation($education_keywords) {
    global $conn, $user_id;

    foreach ($education_keywords as $education) {
        $education = trim($education);  // Clean up any unnecessary spaces or newlines

        // Updated query to check for course or institution (use whichever column makes sense)
        $query = "SELECT * FROM education WHERE user_id = ? AND course LIKE ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $education);  // Assuming education is a string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert education if it doesn't exist
            $insert_query = "INSERT INTO education (user_id, course) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("is", $user_id, $education);
            $insert_stmt->execute();
        }
    }
}


// Function to match and store references
function matchReferences($keywords) {
    global $conn, $user_id;

    // Loop through the extracted reference details
    foreach ($keywords as $reference) {
        // Ensure $reference is an array, if it's a string, try to extract the details
        if (is_array($reference)) {
            // If reference is already an array, extract each detail
            $reference_name = $reference['name'] ?? '';  // Default to empty string if not found
            $reference_position = $reference['position'] ?? '';
            $reference_workplace = $reference['workplace'] ?? '';
            $reference_contact_number = $reference['contact_number'] ?? '';
        } else {
            // If $reference is a string, you need to break it into its components (name, position, etc.)
            // Let's assume we can extract details based on the string format; adjust this as needed
            $reference_name = $reference;  // Assuming the reference is a name for now
            $reference_position = '';  // Need logic for position
            $reference_workplace = '';  // Need logic for workplace
            $reference_contact_number = '';  // Need logic for contact number
        }

        // Check if reference already exists in the database
        $query = "SELECT * FROM `references` WHERE user_id = ? AND name = ? AND contact_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $user_id, $reference_name, $reference_contact_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Insert reference if not already present
            $insert_query = "INSERT INTO `references` (user_id, name, position, workplace, contact_number) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issss", $user_id, $reference_name, $reference_position, $reference_workplace, $reference_contact_number);
            $stmt->execute();
        }
    }
}



// Function to match and store achievements
function matchAchievements($keywords) {
    global $conn, $user_id;

    // Loop through the extracted achievements
    foreach ($keywords as $achievement) {
        // Ensure $achievement is an array
        if (is_array($achievement)) {
            $achievement = array_map('trim', $achievement); // Clean up extra whitespace

            // Ensure each key exists in the $achievement array
            $award_name = $achievement['award_name'] ?? '';  // Default to empty if not found
            $organization = $achievement['organization'] ?? '';
            $award_date = $achievement['award_date'] ?? null;  // Allow null if no award date
            $proof_file = $achievement['proof_file'] ?? null;

            // Check if achievement already exists in the 'achievements' table
            $query = "SELECT * FROM achievements WHERE user_id = ? AND award_name = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user_id, $award_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                // Insert achievement if not already present
                $insert_query = "INSERT INTO achievements (user_id, award_name, organization, award_date, proof_file) 
                                 VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("issss", $user_id, $award_name, $organization, $award_date, $proof_file);
                $stmt->execute();
            }
        } else {
            // Log error if $achievement is not an array
            error_log("Expected an array for achievement, but got: " . var_export($achievement, true));
        }
    }
}


// Function to match and store certificates
function matchCertificates($keywords) {
    global $conn, $user_id;

    // If the keywords are just strings (certificate names), we need to define how to extract other details
    foreach ($keywords as $certificate) {
        $certificate = trim($certificate);

        // Assuming we can extract issuing_organization, issue_date, and certificate_file from somewhere else
        $certificate_name = $certificate;
        $issuing_organization = 'Unknown';  // Example fallback value; replace as necessary
        $issue_date = '2023-01-01';         // Example fallback date; replace as necessary
        $certificate_file = 'unknown.pdf';  // Example fallback filename; replace as necessary

        // Check if certificate already exists by certificate_name and issuing_organization
        $query = "SELECT * FROM certificates WHERE user_id = ? AND certificate_name = ? AND issuing_organization = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $user_id, $certificate_name, $issuing_organization);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert certificate if not present
            $insert_query = "INSERT INTO certificates (user_id, certificate_name, issuing_organization, issue_date, certificate_file) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issss", $user_id, $certificate_name, $issuing_organization, $issue_date, $certificate_file);
            $stmt->execute();
        }
    }
}



function extractGender($text) {
    // Basic gender extraction using string matching (this can be expanded based on your needs)
    if (stripos($text, 'Male') !== false) {
        return 'Male';
    } elseif (stripos($text, 'Female') !== false) {
        return 'Female';
    } elseif (stripos($text, 'Non-Binary') !== false) {
        return 'Non-Binary';
    } elseif (stripos($text, 'LGBTQ+') !== false) {
        return 'LGBTQ+';
    } elseif (stripos($text, 'Other') !== false) {
        return 'Other';
    }
    return NULL;  // Return NULL if no match is found
}

function extractBirthDate($text) {
    // Regex to match dates in the format YYYY-MM-DD or similar (this can be adjusted to your format)
    if (preg_match('/\b(\d{4})[-\/](\d{2})[-\/](\d{2})\b/', $text, $matches)) {
        return $matches[0];  // Return the date in the matched format
    }
    return NULL;  // Return NULL if no date is found
}

function extractAge($text) {
    // Regex to extract age, e.g., "Age: 25" or similar
    if (preg_match('/Age:\s*(\d{1,2})/', $text, $matches)) {
        return (int)$matches[1];  // Return age as an integer
    }
    return NULL;  // Return NULL if no age is found
}

function extractPlaceOfBirth($text) {
    // Simple check for common place-of-birth patterns (this can be expanded based on format)
    if (stripos($text, 'Place of Birth:') !== false) {
        preg_match('/Place of Birth: (.+?)(?=\n|$)/', $text, $matches);
        return $matches[1] ?? NULL;  // Return the place of birth if found, otherwise NULL
    }
    return NULL;  // Return NULL if no place of birth is found
}

function extractPhoneNumber($text) {
    // Regex to match phone numbers (e.g., formats like (123) 456-7890, 123-456-7890)
    if (preg_match('/\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $text, $matches)) {
        return $matches[0];  // Return the matched phone number
    }
    return NULL;  // Return NULL if no phone number is found
}


function extractAddress($text) {
    // Simple pattern to match address; could return an array with parts or just the full address
    if (preg_match('/\d{1,5}\s\w+\s\w+/', $text, $matches)) {
        // Assuming the first match is the full address; you can split it further if needed
        $address = $matches[0];  
        // If you want to split it into street, city, etc., you can do so here.
        return ['street' => $address]; // You can expand this as per your needs
    }
    return NULL;  // Return NULL if no address is found
}

function extractWeight($text) {
    // Simple weight extraction, e.g., "Weight: 75 kg" or "75 lbs"
    if (preg_match('/Weight:\s*(\d+)\s*(kg|lbs)/i', $text, $matches)) {
        return (float)$matches[1];  // Return the weight as a float
    }
    return NULL;  // Return NULL if no weight is found
}

function extractHeight($text) {
    // Simple height extraction (you can modify it to include more formats)
    if (preg_match('/Height:\s*(\d+)\s*(cm|ft|in)/i', $text, $matches)) {
        return $matches[0];  // Return the matched height string
    }
    return NULL;  // Return NULL if no height is found
}

function extractReligion($text) {
    // Check for keywords like 'Religion' and return if found
    if (stripos($text, 'Religion') !== false) {
        preg_match('/Religion:\s*(.+?)(?=\n|$)/', $text, $matches);
        return $matches[1] ?? NULL;
    }
    return NULL;  // Return NULL if no religion is found
}

function extractNationality($text) {
    // Check for keywords like 'Nationality' and return if found
    if (stripos($text, 'Nationality') !== false) {
        preg_match('/Nationality:\s*(.+?)(?=\n|$)/', $text, $matches);
        return $matches[1] ?? NULL;
    }
    return NULL;  // Return NULL if no nationality is found
}

?>
