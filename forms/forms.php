<?php
session_start();

// Restrict access to logged-in users
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
                    You must be logged in to create a resume.
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

// Retrieve session data if it exists
$data = $_SESSION['resume_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];

// Clear errors from session after displaying them
unset($_SESSION['errors']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Generator</title>
    <link rel="stylesheet" href="/JOB/assets/resume.css">
</head>
<body>
    <div class="container">
        <h1>Application Form</h1>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="resumeForm" action="preview.php" method="GET">
            <!-- Basic Information -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection('basic-info')">Basic Information</div>
                <div class="section-content" id="basic-info">
                    <div class="form-grid">
                        <div>
                            <label for="surname">Surname</label>
                            <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($data['surname'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="middle_name">Middle name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($data['middle_name'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="suffix">Suffix</label>
                            <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($data['suffix'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="phone">Phone Number:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" pattern="\d{11}" title="Phone number must be exactly 11 digits" required>
                        </div>
                        <div>
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($data['address'] ?? ''); ?>" required>
                        </div>
                        <div>
                             <label for="tin">Tin:</label>
                             <input type="number" id="tin" name="tin" value="<?php echo htmlspecialchars($data['tin'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection('profile')">Profile</div>
                <div class="section-content" id="profile">
                    <div class="form-grid">
                        <div>
                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($data['age'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="dob">Date of Birth:</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($data['dob'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="pob">Place of Birth:</label>
                            <input type="text" id="pob" name="pob" value="<?php echo htmlspecialchars($data['pob'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="Male" <?php echo isset($data['gender']) && $data['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo isset($data['gender']) && $data['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo isset($data['gender']) && $data['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="marital_status">Marital Status:</label>
                            <select id="marital_status" name="marital_status" required>
                                <option value="Single" <?php echo isset($data['marital_status']) && $data['marital_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo isset($data['marital_status']) && $data['marital_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo isset($data['marital_status']) && $data['marital_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                        <div>
                            <label for="weight">Weight (kg):</label>
                            <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($data['weight'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="height">Height (cm):</label>
                            <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($data['height'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="nationality">Nationality:</label>
                            <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($data['nationality'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label for="religion">Religion:</label>
                            <input type="text" id="religion" name="religion" value="<?php echo htmlspecialchars($data['religion'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <label for="languages">Languages Spoken:</label>
                    <textarea id="languages" name="languages" rows="2" placeholder="Enter languages separated by commas..." required><?php echo htmlspecialchars($data['languages'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Professional Summary -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection('professional-summary')">Professional Summary</div>
                <div class="section-content" id="professional-summary">
                    <label for="profile">Profile:</label>
                    <textarea id="profile" name="profile" rows="4" placeholder="Provide a brief description of yourself..." required><?php echo htmlspecialchars($data['profile'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Disabilityy -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection('disability')">Disability</div>
                <div class="section-content" id="disability">
                    <div class="form-grid">
                        <div>
                            <label for="disability">Disability</label>
                            <textarea id="languages_proficiency" name="disability" rows="2" placeholder="ex: Visual, Speech, Mental, Hearing, Physical, etc..." required><?php echo htmlspecialchars($data['Disability'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Status / Type -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('employment-status')">Employment Status / Type</div>
    <div class="section-content" id="employment-status">
        <div class="form-grid">
            <div>
                <label for="employment_status">Employment Status:</label>
                <select id="employment_status" name="employment_status" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Employed" <?php echo isset($data['employment_status']) && $data['employment_status'] === 'Employed' ? 'selected' : ''; ?>>Employed</option>
                    <option value="Unemployed" <?php echo isset($data['employment_status']) && $data['employment_status'] === 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                    <option value="Others" <?php echo isset($data['employment_status']) && $data['employment_status'] === 'Others' ? 'selected' : ''; ?>>Others</option>
                </select>
            </div>
            <div id="employed-details" style="display: none;">
                <label for="employed_type">Type of Employment:</label>
                <select id="employed_type" name="employed_type">
                    <option value="" disabled selected>Select an option</option>
                    <option value="Wage Employed" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Wage Employed' ? 'selected' : ''; ?>>Wage Employed</option>
                    <option value="Self-employed" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Self-employed' ? 'selected' : ''; ?>>Self-employed</option>
                    <option value="Fisherman/Fisherfolk" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Fisherman/Fisherfolk' ? 'selected' : ''; ?>>Fisherman/Fisherfolk</option>
                    <option value="Vendor/Retailer" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Vendor/Retailer' ? 'selected' : ''; ?>>Vendor/Retailer</option>
                    <option value="Home-based worker" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Home-based worker' ? 'selected' : ''; ?>>Home-based worker</option>
                    <option value="Transport" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Transport' ? 'selected' : ''; ?>>Transport</option>
                    <option value="Domestic Worker" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Domestic Worker' ? 'selected' : ''; ?>>Domestic Worker</option>
                    <option value="Freelancer" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Freelancer' ? 'selected' : ''; ?>>Freelancer</option>
                    <option value="Artisan/Craft Worker" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Artisan/Craft Worker' ? 'selected' : ''; ?>>Artisan/Craft Worker</option>
                    <option value="Other" <?php echo isset($data['employed_type']) && $data['employed_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div id="unemployed-details" style="display: none;">
                <label for="unemployed_reason">Reason for Unemployment:</label>
                <select id="unemployed_reason" name="unemployed_reason">
                    <option value="" disabled selected>Select an option</option>
                    <option value="New Entrant/Fresh Graduate" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'New Entrant/Fresh Graduate' ? 'selected' : ''; ?>>New Entrant/Fresh Graduate</option>
                    <option value="Finished Contract" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Finished Contract' ? 'selected' : ''; ?>>Finished Contract</option>
                    <option value="Resigned" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Resigned' ? 'selected' : ''; ?>>Resigned</option>
                    <option value="Retired" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                    <option value="Terminated/Laid off due to calamity" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Terminated/Laid off due to calamity' ? 'selected' : ''; ?>>Terminated/Laid off due to calamity</option>
                    <option value="Terminated/Laid off (local)" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Terminated/Laid off (local)' ? 'selected' : ''; ?>>Terminated/Laid off (local)</option>
                    <option value="Terminated/Laid off (abroad)" <?php echo isset($data['unemployed_reason']) && $data['unemployed_reason'] === 'Terminated/Laid off (abroad)' ? 'selected' : ''; ?>>Terminated/Laid off (abroad)</option>
                </select>
            </div>
            <div>
                <label for="ofw_status">Are you an OFW?</label>
                <select id="ofw_status" name="ofw_status" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Yes" <?php echo isset($data['ofw_status']) && $data['ofw_status'] === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="No" <?php echo isset($data['ofw_status']) && $data['ofw_status'] === 'No' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div>
                <label for="former_ofw_status">Are you a former OFW?</label>
                <select id="former_ofw_status" name="former_ofw_status" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Yes" <?php echo isset($data['former_ofw_status']) && $data['former_ofw_status'] === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="No" <?php echo isset($data['former_ofw_status']) && $data['former_ofw_status'] === 'No' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div>
                <label for="4ps_beneficiary">Are you a 4Ps beneficiary?</label>
                <select id="4ps_beneficiary" name="4ps_beneficiary" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Yes" <?php echo isset($data['4ps_beneficiary']) && $data['4ps_beneficiary'] === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="No" <?php echo isset($data['4ps_beneficiary']) && $data['4ps_beneficiary'] === 'No' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Job Preference -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('job-preference')">Job Preference</div>
    <div class="section-content" id="job-preference">
        <div class="form-grid">
            <div>
                <label for="preferred_occupation">Preferred Occupation (List up to 3):</label>
                <textarea id="preferred_occupation" name="preferred_occupation" rows="2" placeholder="Enter occupations separated by commas..." required><?php echo htmlspecialchars($data['preferred_occupation'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="preferred_work_location_local">Preferred Work Location (Local):</label>
                <input type="text" id="preferred_work_location_local" name="preferred_work_location_local" value="<?php echo htmlspecialchars($data['preferred_work_location_local'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="preferred_work_location_overseas">Preferred Work Location (Overseas):</label>
                <input type="text" id="preferred_work_location_overseas" name="preferred_work_location_overseas" value="<?php echo htmlspecialchars($data['preferred_work_location_overseas'] ?? ''); ?>" required>
            </div>
        </div>
    </div>
</div>

<!-- Language / Dialect Proficiency -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('language-proficiency')">Language / Dialect Proficiency</div>
    <div class="section-content" id="language-proficiency">
        <div class="form-grid">
            <div>
                <label for="languages_proficiency">Languages/Dialects:</label>
                <textarea id="languages_proficiency" name="languages_proficiency" rows="2" placeholder="Enter languages/dialects separated by commas..." required><?php echo htmlspecialchars($data['languages_proficiency'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Educational Background -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('educational-background')">Educational Background</div>
    <div class="section-content" id="educational-background">
        <div class="form-grid">
            <div>
                <label for="currently_in_school">Currently in school?</label>
                <select id="currently_in_school" name="currently_in_school" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Yes" <?php echo isset($data['currently_in_school']) && $data['currently_in_school'] === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="No" <?php echo isset($data['currently_in_school']) && $data['currently_in_school'] === 'No' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div>
                <label for="educational_level">Educational Level:</label>
                <select id="educational_level" name="educational_level" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="Elementary" <?php echo isset($data['educational_level']) && $data['educational_level'] === 'Elementary' ? 'selected' : ''; ?>>Elementary</option>
                    <option value="Secondary (Non-K12)" <?php echo isset($data['educational_level']) && $data['educational_level'] === 'Secondary (Non-K12)' ? 'selected' : ''; ?>>Secondary (Non-K12)</option>
                    <option value="Secondary (K-12)" <?php echo isset($data['educational_level']) && $data['educational_level'] === 'Secondary (K-12)' ? 'selected' : ''; ?>>Secondary (K-12)</option>
                    <option value="Graduate/Post-Graduate Studies" <?php echo isset($data['educational_level']) && $data['educational_level'] === 'Graduate/Post-Graduate Studies' ? 'selected' : ''; ?>>Graduate/Post-Graduate Studies</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Technical/Vocational and Other Training -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('technical-training')">Technical/Vocational and Other Training</div>
    <div class="section-content" id="technical-training">
        <div class="form-grid">
            <div>
                <label for="training_courses">Training/Vocational Courses:</label>
                <textarea id="training_courses" name="training_courses" rows="2" placeholder="Enter courses separated by commas..." required><?php echo htmlspecialchars($data['training_courses'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="institution">Institution:</label>
                <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($data['institution'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="skills_acquired">Skills Acquired:</label>
                <textarea id="skills_acquired" name="skills_acquired" rows="2" placeholder="Enter skills separated by commas..." required><?php echo htmlspecialchars($data['skills_acquired'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Eligibility / Professional License -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('professional-license')">Eligibility / Professional License</div>
    <div class="section-content" id="professional-license">
        <div class="form-grid">
            <div>
                <label for="license_type">Type of License:</label>
                <input type="text" id="license_type" name="license_type" value="<?php echo htmlspecialchars($data['license_type'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="license_number">License Number:</label>
                <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($data['license_number'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="issuing_agency">Issuing Agency:</label>
                <input type="text" id="issuing_agency" name="issuing_agency" value="<?php echo htmlspecialchars($data['issuing_agency'] ?? ''); ?>" required>
            </div>
        </div>
    </div>
</div>

<!-- Work Experience -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('work-experience')">Work Experience</div>
    <div class="section-content" id="work-experience">
        <div class="form-grid">
            <div>
                <label for="most_recent_employment">Most Recent Employment:</label>
                <textarea id="most_recent_employment" name="most_recent_employment" rows="2" placeholder="Provide details about your most recent job..." required><?php echo htmlspecialchars($data['most_recent_employment'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Other Skills Acquired Without Certificate -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('other-skills')">Other Skills Acquired Without Certificate</div>
    <div class="section-content" id="other-skills">
        <div class="form-grid">
            <div>
                <label for="other_skills">Skills:</label>
                <textarea id="other_skills" name="other_skills" rows="2" placeholder="Enter skills separated by commas..." required><?php echo htmlspecialchars($data['other_skills'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Certification/Authorization -->
<div class="collapsible-section">
    <div class="section-header" onclick="toggleSection('certification')">Certification/Authorization</div>
    <div class="section-content" id="certification">
        <div class="form-grid">
            <div>
                <label for="signature">Signature of Applicant:</label>
                <input type="text" id="signature" name="signature" value="<?php echo htmlspecialchars($data['signature'] ?? ''); ?>" placeholder="Type your name as a signature" required>
            </div>
        </div>
    </div>
</div>



            <!-- Centered Preview Button -->
            <button type="submit">Preview Resume</button>
        </form>
        <button style="background-color: gray;" onclick="window.location.href='<?php echo ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employer') ? '/JOB/employers/profile.php' : '/JOB/pages/profile.php'; ?>';">
    Back
</button>

           
    </div>

    <script>
        // Function to toggle collapsible sections
        function toggleSection(sectionId) {
            const sectionContent = document.getElementById(sectionId);
            sectionContent.classList.toggle('active');
        }
    </script>
</body>
</html>