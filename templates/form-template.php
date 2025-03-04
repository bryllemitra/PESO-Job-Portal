<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Resume</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .resume-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center; /* Center align all elements in the header */
            margin-bottom: 20px;
        }
        .photo-placeholder {
            width: 150px; /* Larger photo size for 2x2 picture */
            height: 150px;
            background: #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            border-radius: 50%;
            margin: 0 auto 10px auto; /* Center the photo and add space below it */
        }
        .header-info h1 {
            margin: 0;
            font-size: 24px;
            margin-bottom: 10px; /* Space between name and contact info */
        }
        .contact-info {
            display: flex;
            justify-content: center; /* Center the contact info horizontally */
            gap: 20px; /* Space between email, phone, and address */
            font-size: 14px;
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .profile-item {
            margin: 5px 0;
        }
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .skill {
            background:rgb(255, 255, 255);
            color: black;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="resume-container">
        <!-- Header Section -->
        <div class="header">
            <!-- Photo Placeholder -->
            <div class="photo-placeholder"></div>
            
            <!-- Name -->
            <h1><?php echo htmlspecialchars($data['name'] ?? ''); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($data['profile'] ?? '')); ?></p>
            
            <!-- Contact Info (Email, Phone, Address) -->
            <div class="contact-info">
                <span><?php echo htmlspecialchars($data['email'] ?? ''); ?> | </span>
                <span><?php echo htmlspecialchars($data['phone'] ?? ''); ?> | </span>
                <span><?php echo htmlspecialchars($data['address'] ?? ''); ?></span>
            </div>
        </div>

        <!-- Profile Section -->
        <div class="section">
            <div class="section-title">Profile</div>
            <div class="profile-item"><strong>Age:</strong> <?php echo htmlspecialchars($data['age'] ?? ''); ?></div>
            <div class="profile-item"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($data['dob'] ?? ''); ?></div>
            <div class="profile-item"><strong>Place of Birth:</strong> <?php echo htmlspecialchars($data['pob'] ?? ''); ?></div>
            <div class="profile-item"><strong>Gender:</strong> <?php echo htmlspecialchars($data['gender'] ?? ''); ?></div>
            <div class="profile-item"><strong>Marital Status:</strong> <?php echo htmlspecialchars($data['marital_status'] ?? ''); ?></div>
            <div class="profile-item"><strong>Weight:</strong> <?php echo htmlspecialchars($data['weight'] ?? ''); ?> kg</div>
            <div class="profile-item"><strong>Height:</strong> <?php echo htmlspecialchars($data['height'] ?? ''); ?> cm</div>
            <div class="profile-item"><strong>Nationality:</strong> <?php echo htmlspecialchars($data['nationality'] ?? ''); ?></div>
            <div class="profile-item"><strong>Religion:</strong> <?php echo htmlspecialchars($data['religion'] ?? ''); ?></div>
            <div class="profile-item"><strong>Languages:</strong> <?php echo htmlspecialchars($data['languages'] ?? ''); ?></div>
        </div>

        <!-- Employment Status -->
        <div class="section">
            <div class="section-title">Employment Status</div>
            <div class="profile-item"><strong>Status:</strong> <?php echo htmlspecialchars($data['employment_status'] ?? ''); ?></div>
            <?php if ($data['employment_status'] === 'Employed'): ?>
                <div class="profile-item"><strong>Type:</strong> <?php echo htmlspecialchars($data['employed_type'] ?? ''); ?></div>
            <?php elseif ($data['employment_status'] === 'Unemployed'): ?>
                <div class="profile-item"><strong>Reason:</strong> <?php echo htmlspecialchars($data['unemployed_reason'] ?? ''); ?></div>
            <?php endif; ?>
            <div class="profile-item"><strong>OFW Status:</strong> <?php echo htmlspecialchars($data['ofw_status'] ?? ''); ?></div>
            <div class="profile-item"><strong>Former OFW Status:</strong> <?php echo htmlspecialchars($data['former_ofw_status'] ?? ''); ?></div>
            <div class="profile-item"><strong>4Ps Beneficiary:</strong> <?php echo htmlspecialchars($data['4ps_beneficiary'] ?? ''); ?></div>
        </div>

        <!-- Job Preference -->
        <div class="section">
            <div class="section-title">Job Preference</div>
            <div class="profile-item"><strong>Preferred Occupation:</strong> <?php echo htmlspecialchars($data['preferred_occupation'] ?? ''); ?></div>
            <div class="profile-item"><strong>Preferred Work Location (Local):</strong> <?php echo htmlspecialchars($data['preferred_work_location_local'] ?? ''); ?></div>
            <div class="profile-item"><strong>Preferred Work Location (Overseas):</strong> <?php echo htmlspecialchars($data['preferred_work_location_overseas'] ?? ''); ?></div>
        </div>

        <!-- Language / Dialect Proficiency -->
        <div class="section">
            <div class="section-title">Language / Dialect Proficiency</div>
            <div class="profile-item"><strong>Languages/Dialects:</strong> <?php echo htmlspecialchars($data['languages_proficiency'] ?? ''); ?></div>
        </div>

        <!-- Educational Background -->
        <div class="section">
            <div class="section-title">Educational Background</div>
            <div class="profile-item"><strong>Currently in School:</strong> <?php echo htmlspecialchars($data['currently_in_school'] ?? ''); ?></div>
            <div class="profile-item"><strong>Educational Level:</strong> <?php echo htmlspecialchars($data['educational_level'] ?? ''); ?></div>
        </div>

        <!-- Technical/Vocational and Other Training -->
        <div class="section">
            <div class="section-title">Technical/Vocational and Other Training</div>
            <div class="profile-item"><strong>Courses:</strong> <?php echo htmlspecialchars($data['training_courses'] ?? ''); ?></div>
            <div class="profile-item"><strong>Institution:</strong> <?php echo htmlspecialchars($data['institution'] ?? ''); ?></div>
            <div class="profile-item"><strong>Skills Acquired:</strong> <?php echo htmlspecialchars($data['skills_acquired'] ?? ''); ?></div>
        </div>

        <!-- Eligibility / Professional License -->
        <div class="section">
            <div class="section-title">Eligibility / Professional License</div>
            <div class="profile-item"><strong>Type of License:</strong> <?php echo htmlspecialchars($data['license_type'] ?? ''); ?></div>
            <div class="profile-item"><strong>License Number:</strong> <?php echo htmlspecialchars($data['license_number'] ?? ''); ?></div>
            <div class="profile-item"><strong>Issuing Agency:</strong> <?php echo htmlspecialchars($data['issuing_agency'] ?? ''); ?></div>
        </div>

        <!-- Work Experience -->
        <div class="section">
            <div class="section-title">Work Experience</div>
            <div class="profile-item"><strong>Most Recent Employment:</strong> <?php echo htmlspecialchars($data['most_recent_employment'] ?? ''); ?></div>
        </div>

        <!-- Other Skills Acquired Without Certificate -->
        <div class="section">
            <div class="section-title">Other Skills Acquired Without Certificate</div>
            <div class="profile-item"><strong>Skills:</strong> <?php echo htmlspecialchars($data['other_skills'] ?? ''); ?></div>
        </div>

        <!-- Certification/Authorization -->
        <div class="section">
            <div class="section-title">Certification/Authorization</div>
            <div class="profile-item"><strong>Signature:</strong> <?php echo htmlspecialchars($data['signature'] ?? ''); ?></div>
        </div>
    </div>

</body>
</html>