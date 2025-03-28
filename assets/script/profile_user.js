



// EDUCATION 
document.addEventListener('DOMContentLoaded', function() {
    // ADD EDUCATION MODAL - Education Level Change
    document.getElementById('education_level').addEventListener('change', function() {
        var level = this.value;

        // Show/Hide course-related fields based on education level
        if (level === 'college' || level === 'graduate' || level === 'vocational') {
            document.getElementById('course_group').style.display = 'block';
            document.getElementById('course_highlights_group').style.display = 'block';
        } else {
            document.getElementById('course_group').style.display = 'none';
            document.getElementById('course_highlights_group').style.display = 'none';
        }
    });

    // ADD EDUCATION MODAL - Status Change
    document.getElementById('status').addEventListener('change', function() {
        updateCompletionFields(this.value);
    });

    // EDIT EDUCATION MODAL - Status Change
    document.getElementById('edit_status').addEventListener('change', function() {
        updateCompletionFields(this.value, 'edit_');
    });
});

// Shared function to update completion fields
function updateCompletionFields(status, prefix = '') {
    const completionGroup = document.getElementById(`${prefix}completion_year_group`);
    const expectedGroup = document.getElementById(`${prefix}expected_completion_group`);
    
    if (status === 'Completed') {
        completionGroup.style.display = 'block';
        expectedGroup.style.display = 'none';
    } else {
        completionGroup.style.display = 'none';
        expectedGroup.style.display = 'block';
    }
}

// EDIT EDUCATION MODAL - Open Modal Function
function openEditModal(education) {
    console.log("Education data:", education);

    // Pre-fill the form with education data
    document.getElementById('edit_education_id').value = education.id;
    document.getElementById('edit_institution').value = education.institution;
    document.getElementById('edit_status').value = education.status;
    document.getElementById('edit_education_level').value = education.education_level;

    // Show/hide fields based on education level
    if (education.education_level === 'college' || education.education_level === 'graduate' || education.education_level === 'vocational') {
        document.getElementById('edit_course_group').style.display = 'block';
        document.getElementById('edit_course_highlights_group').style.display = 'block';
    } else {
        document.getElementById('edit_course_group').style.display = 'none';
        document.getElementById('edit_course_highlights_group').style.display = 'none';
    }

    // Initialize completion fields based on status
    updateCompletionFields(education.status, 'edit_');

    // Pre-fill additional fields
    if (education.education_level === 'college' || education.education_level === 'graduate' || education.education_level === 'vocational') {
        document.getElementById('edit_course').value = education.course || '';
        document.getElementById('edit_course_highlights').value = education.course_highlights || '';
    }
    if (education.status === 'Completed') {
        document.getElementById('edit_completion_year').value = education.completion_year || '';
    } else {
        document.getElementById('edit_expected_completion_date').value = education.expected_completion_date || '';
    }

    // Open the modal
    new bootstrap.Modal(document.getElementById('editEducationModal')).show();
}

// Function to open the Delete Confirmation Modal
function openDeleteEduModal(educationId) {
    // Set the delete link with the education ID
    document.getElementById('confirmDeleteButton').href = `delete_education.php?id=${educationId}`;

    // Open the modal
    $('#deleteConfirmationModal').modal('show');
}




// SKILL SECTION
document.getElementById('skillSearch').addEventListener('input', function() {
    const query = this.value;
    if (query.length >= 2) { // Start searching after 2 characters
        fetch(`search_skills.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('skillResults');
                resultsDiv.innerHTML = ''; // Clear previous results
                data.forEach(skill => {
                    const skillItem = document.createElement('div');
                    skillItem.className = 'dropdown-item p-2';
                    skillItem.textContent = `${skill.skill_name}`; // Display skill with category
                    skillItem.onclick = () => selectSkill(skill.id, `${skill.skill_name} (${skill.category_name})`);
                    resultsDiv.appendChild(skillItem);
                });
            })
            .catch(error => {
                console.error('Error fetching skills:', error);
            });
    }
});

// Select a skill from the dropdown
function selectSkill(skillId, skillName) {
    document.getElementById('skillSearch').value = skillName;
    document.getElementById('skillResults').innerHTML = ''; // Clear dropdown
    selectedSkillId = skillId; // Store selected skill ID globally
}

// Add the selected skill to the user's profile
function addSkill() {
    const proficiency = document.getElementById('proficiencyLevel').value;
    if (selectedSkillId) {
        fetch('add_skill.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ skill_id: selectedSkillId, proficiency: proficiency })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Use SweetAlert for success
                Swal.fire({
                    icon: 'success',
                    title: 'Skill Added!',
                    text: 'Your skill has been successfully added.',
                }).then(() => {
                    location.reload(); // Refresh the page after SweetAlert is closed
                });
            } else {
                // Use SweetAlert for error
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Add Skill',
                    text: 'There was an error adding your skill.',
                });
            }
        });
    } else {
        // Use SweetAlert to notify user about no skill selected
        Swal.fire({
            icon: 'warning',
            title: 'No Skill Selected',
            text: 'Please select a skill to add.',
        });
    }
}

// Delete a skill after confirmation
function deleteSkill(skillId) {
    fetch('delete_skill.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skill_id: skillId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Use SweetAlert for success
            Swal.fire({
                icon: 'success',
                title: 'Skill Deleted',
                text: 'Your skill has been successfully deleted.',
            }).then(() => {
                location.reload(); // Refresh the page to reflect the deletion
            });
        } else {
            // Use SweetAlert for error
            Swal.fire({
                icon: 'error',
                title: 'Failed to Delete Skill',
                text: 'There was an error deleting your skill.',
            });
        }
    });
}

// Store the skill ID to be deleted
let skillToDelete = null;

// Function to open the delete skill modal and store the skill ID
function openDeleteSkillModal(skillId) {
    skillToDelete = skillId; // Store the skill ID for later use
    // Open the modal
    $('#deleteSkillModal').modal('show');
}

// Function to delete the skill after confirming in the modal
document.getElementById('confirmDeleteSkillBtn').addEventListener('click', function() {
    if (skillToDelete !== null) {
        deleteSkill(skillToDelete);
        // Close the modal after confirming
        $('#deleteSkillModal').modal('hide');
    }
});


let selectedLanguageId = null; // Variable to hold the selected language ID

// Function to handle adding a language
function addLanguage() {
    const fluency = document.getElementById('fluency').value;
    
    // Check if a language is selected
    if (selectedLanguageId) {
        fetch('add_language.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ language_id: selectedLanguageId, fluency: fluency })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - Use SweetAlert to notify user
                Swal.fire({
                    icon: 'success',
                    title: 'Language Added!',
                    text: 'Your language has been successfully added.',
                }).then(() => {
                    location.reload(); // Refresh the page after SweetAlert is closed
                });
            } else {
                // Error - Use SweetAlert for failure
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Add Language',
                    text: 'There was an error adding your language.',
                });
            }
        });
    } else {
        // Warning - Use SweetAlert for no language selected
        Swal.fire({
            icon: 'warning',
            title: 'No Language Selected',
            text: 'Please select a language to add.',
        });
    }
}

// Function to handle the language selection
document.getElementById('language').addEventListener('change', function () {
    selectedLanguageId = this.value; // Store the selected language ID
});

// Store the language ID to be deleted
let languageToDelete = null;

// Function to open the delete language modal and store the language ID
function openDeleteLanguageModal(languageId) {
    languageToDelete = languageId; // Store the language ID for later use
    // Open the modal
    $('#deleteLanguageModal').modal('show');
}

// Function to delete the language after confirming in the modal
document.getElementById('confirmDeleteLanguageBtn').addEventListener('click', function() {
    if (languageToDelete !== null) {
        deleteLanguage(languageToDelete);
        // Close the modal after confirming
        $('#deleteLanguageModal').modal('hide');
    }
});

// Delete a language after confirmation
function deleteLanguage(languageId) {
    fetch('delete_language.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language_id: languageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success - Use SweetAlert to notify user
            Swal.fire({
                icon: 'success',
                title: 'Language Deleted',
                text: 'Your language has been successfully deleted.',
            }).then(() => {
                location.reload(); // Refresh the page after SweetAlert is closed
            });
        } else {
            // Error - Use SweetAlert for failure
            Swal.fire({
                icon: 'error',
                title: 'Failed to Delete Language',
                text: 'There was an error deleting your language.',
            });
        }
    });
}


// WORK EXPERIENCE COUNTRY SECTION
document.addEventListener("DOMContentLoaded", function() {
    // Add Modal Elements
    const jobLocationField = document.getElementById("job_location");
    const countryDiv = document.getElementById("country-div");
    const countryInput = document.getElementById("country");
    const addForm = document.querySelector('#addWorkExperienceModal form'); // Get the add modal form element

    // Edit Modal Elements
    const editJobLocationField = document.getElementById("edit_job_location");
    const editCountryDiv = document.getElementById("edit_country_div");
    const editCountryInput = document.getElementById("edit_country");
    const editForm = document.querySelector('#editWorkExperienceModal form'); // Get the edit modal form element

    // Function to toggle country field visibility for the Add Modal
    function toggleCountryField() {
        if (jobLocationField.value === "overseas") {
            countryDiv.style.display = "block";
            countryInput.setAttribute("required", "true");
        } else {
            countryDiv.style.display = "none";
            countryInput.removeAttribute("required");
        }
    }

    // Function to toggle country field visibility for the Edit Modal
    function toggleEditCountryField() {
        if (editJobLocationField.value === "overseas") {
            editCountryDiv.style.display = "block";
            editCountryInput.setAttribute("required", "true");
        } else {
            editCountryDiv.style.display = "none";
            editCountryInput.removeAttribute("required");
        }
    }

    // Run the toggle functions when the page loads to set the correct initial state
    toggleCountryField();
    toggleEditCountryField();

    // Add event listeners for location change to toggle country field visibility
    jobLocationField.addEventListener("change", toggleCountryField);
    editJobLocationField.addEventListener("change", toggleEditCountryField);

    // Form validation before submission for the Add Modal
    addForm.addEventListener("submit", function(event) {
        if (jobLocationField.value === "overseas" && !countryInput.value.trim()) {
            event.preventDefault(); // Prevent form submission

            // Highlight country input field
            countryInput.classList.add("is-invalid");

            // Show SweetAlert error message
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please input the country when job location is overseas!',
                confirmButtonText: 'OK'
            });

            return false; // Stop further form submission
        }

        // If country field is valid, remove the highlight
        countryInput.classList.remove("is-invalid");
    });

    // Form validation before submission for the Edit Modal
    editForm.addEventListener("submit", function(event) {
        if (editJobLocationField.value === "overseas" && !editCountryInput.value.trim()) {
            event.preventDefault(); // Prevent form submission

            // Highlight country input field
            editCountryInput.classList.add("is-invalid");

            // Show SweetAlert error message
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please input the country when job location is overseas!',
                confirmButtonText: 'OK'
            });

            return false; // Stop further form submission
        }

        // If country field is valid, remove the highlight
        editCountryInput.classList.remove("is-invalid");
    });
});


document.addEventListener("DOMContentLoaded", function() {
    const currentlyWorkingCheckbox = document.getElementById("currently_working");
    const endDateField = document.getElementById("end_date"); // End date input field

    // Function to toggle End Date field required status
    function toggleEndDateRequired() {
        if (currentlyWorkingCheckbox.checked) {
            endDateField.removeAttribute("required"); // Make End Date optional if checked
        } else {
            endDateField.setAttribute("required", "true"); // Make End Date required if unchecked
        }
    }

    // Initialize the End Date field based on the checkbox state
    toggleEndDateRequired();

    // Add event listener to the checkbox to toggle the End Date required status
    currentlyWorkingCheckbox.addEventListener("change", toggleEndDateRequired);

    // Form validation before submission
    const addForm = document.querySelector('#addWorkExperienceModal form'); // Get the add modal form element
    addForm.addEventListener("submit", function(event) {
        if (!currentlyWorkingCheckbox.checked && !endDateField.value.trim()) {
            event.preventDefault(); // Prevent form submission

            // Highlight End Date field
            endDateField.classList.add("is-invalid");

            // Show SweetAlert error message
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'End date is required if you are not currently working here.',
                confirmButtonText: 'OK'
            });

            return false; // Stop further form submission
        }

        // If End Date field is valid, remove the highlight
        endDateField.classList.remove("is-invalid");
    });

    // For the Edit Modal (if necessary)
    const editCurrentlyWorkingCheckbox = document.getElementById("edit_currently_working");
    const editEndDateField = document.getElementById("edit_end_date");

    function toggleEditEndDateRequired() {
        if (editCurrentlyWorkingCheckbox.checked) {
            editEndDateField.removeAttribute("required");
        } else {
            editEndDateField.setAttribute("required", "true");
        }
    }

    toggleEditEndDateRequired();
    editCurrentlyWorkingCheckbox.addEventListener("change", toggleEditEndDateRequired);

    const editForm = document.querySelector('#editWorkExperienceModal form');
    editForm.addEventListener("submit", function(event) {
        if (!editCurrentlyWorkingCheckbox.checked && !editEndDateField.value.trim()) {
            event.preventDefault();

            editEndDateField.classList.add("is-invalid");

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'End date is required if you are not currently working here.',
                confirmButtonText: 'OK'
            });

            return false;
        }

        editEndDateField.classList.remove("is-invalid");
    });
});




//DELETE ACHIEVEMENT MODAL
function openDeleteAchievementModal(achievementId) {
    // Set the achievement ID in the hidden input field
    document.getElementById('delete_achievement_id').value = achievementId;
    // Open the delete modal
    $('#deleteAchievementModal').modal('show');
}


    // EDIT ACHIEVEMENT MODAL
    function openEditAchievementModal(achievementId, awardName, organization, awardDate, proofFile) {
        // Set the fields in the modal with the current achievement data
        document.getElementById('edit_achievement_id').value = achievementId;
        document.getElementById('edit_award_name').value = awardName;
        document.getElementById('edit_organization').value = organization;
        document.getElementById('edit_award_date').value = awardDate;
        
        // If proof_file is provided, handle it (optional file input doesn't populate automatically)
        if (proofFile) {
            document.getElementById('edit_proof_file').setAttribute('data-proof-file', proofFile); // Optional attribute to remember the existing file
        }

        // Show the modal
        var editModal = new bootstrap.Modal(document.getElementById('editAchievementModal'));
        editModal.show();
    }


    // EDIT CERTIFICATE MODAL
    function openEditCertificateModal(certificate_id, certificate_name, issuing_organization, issue_date, certificate_file) {
    // Pre-fill the modal with the certificate data
    document.getElementById("certificate_id").value = certificate_id;
    document.getElementById("certificate_name_edit").value = certificate_name;
    document.getElementById("issuing_organization_edit").value = issuing_organization;
    document.getElementById("issue_date_edit").value = issue_date;

    // If there's a file already attached, show it (optional)
    if (certificate_file) {
        document.getElementById("certificate_file_edit").setAttribute("data-existing-file", certificate_file);
    }

    // Open the modal
    var myModal = new bootstrap.Modal(document.getElementById("editCertificateModal"));
    myModal.show();
}

function openDeleteCertificateModal(certificateId) {
    // Set the certificate ID to the hidden input field in the modal
    document.getElementById('delete_certificate_id').value = certificateId;

    // Show the delete confirmation modal
    $('#deleteCertificateModal').modal('show');
}



//EDIT AND DELETE REFERENCES 

// Function to populate the edit modal with existing reference data
function editReference(reference_id, reference_name, position, workplace, contact_number) {
    document.getElementById('reference_id').value = reference_id;
    document.getElementById('reference_name_edit').value = reference_name;
    document.getElementById('position_edit').value = position;
    document.getElementById('workplace_edit').value = workplace;
    document.getElementById('contact_number_edit').value = contact_number;

    // Show the modal
    $('#editReferenceModal').modal('show');
}


// Function to set the reference id for deletion in the delete modal
function openDeleteReferenceModal (reference_id) {
    document.getElementById('delete_reference_id').value = reference_id;

    // Show the modal
    $('#deleteReferenceModal').modal('show');
}


//EDIT AND DELETE JOB PREFERENCES 

// Open Edit Modal
function openEditJobPreferencesModal(jobPreferences) {
    document.getElementById('edit_job_preference_id').value = jobPreferences.id;
    document.getElementById('edit_work_type').value = jobPreferences.work_type;
    document.getElementById('edit_job_location').value = jobPreferences.job_location;
    document.getElementById('edit_employment_type').value = jobPreferences.employment_type;
    // You can handle preferred skills similarly by updating the select box
    $('#editJobPreferencesModal').modal('show');
}

// Open Delete Modal
function openDeleteJobPreferencesModal(jobPreferenceId) {
    document.getElementById('delete_job_preference_id').value = jobPreferenceId;
    $('#deleteJobPreferencesModal').modal('show');
}




// TOGGLE TO HIDE PERSONAL INFORMATION SECTION
document.addEventListener("DOMContentLoaded", function() {
    var personalInfoSection = document.getElementById("personal-info-section");
    var toggleButton = document.getElementById("toggle-personal-info-section");

    // Initially, personal information section is visible
    personalInfoSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the personal information section
        if (personalInfoSection.style.display === "none") {
            personalInfoSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            personalInfoSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});



// TOGGLE TO HIDE  EDUCATION SECTION
document.addEventListener("DOMContentLoaded", function() {
    var educationSection = document.getElementById("education-section");
    var toggleButton = document.getElementById("toggle-education-section");

    // Initially, education section is visible
    educationSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the education section
        if (educationSection.style.display === "none") {
            educationSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            educationSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});



// TOGGLE TO HIDE WORK EXPERIENCE SECTION

document.addEventListener("DOMContentLoaded", function() {
    var workExperienceSection = document.getElementById("work-experience-section");
    var toggleButton = document.getElementById("toggle-work-experience");

    // Initially, work experience section is visible
    workExperienceSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the work experience section
        if (workExperienceSection.style.display === "none") {
            workExperienceSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            workExperienceSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});








// TOGGLE TO HIDE JOB PREFERENCE
document.addEventListener("DOMContentLoaded", function() {
    var jobPreferencesSection = document.getElementById("job-preferences-section");
    var toggleButton = document.getElementById("toggle-job-preferences-section");

    // Initially, job preferences section is visible
    jobPreferencesSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the job preferences section
        if (jobPreferencesSection.style.display === "none") {
            jobPreferencesSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            jobPreferencesSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


// TOGGLE TO HIDE ACHIEVEMENT SECTION
document.addEventListener("DOMContentLoaded", function() {
    var achievementsSection = document.getElementById("achievements-section");
    var toggleButton = document.getElementById("toggle-achievements-section");

    // Initially, achievements section is visible
    achievementsSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the achievements section
        if (achievementsSection.style.display === "none") {
            achievementsSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            achievementsSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


// TOGGLE TO HIDE CHARACTER REFERENCES
document.addEventListener("DOMContentLoaded", function() {
    var referencesSection = document.getElementById("references-section");
    var toggleButton = document.getElementById("toggle-references-section");

    // Initially, references section is visible
    referencesSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the references section
        if (referencesSection.style.display === "none") {
            referencesSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            referencesSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


// TOGGLE TO HIDE SKILL SECTION
document.addEventListener("DOMContentLoaded", function() {
    var skillsSection = document.getElementById("skills-section");
    var toggleButton = document.getElementById("toggle-skills-section");

    // Initially, skills section is visible
    skillsSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the skills section
        if (skillsSection.style.display === "none") {
            skillsSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            skillsSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


// TOGGLE TO HIDE LANGUAGES SECTION
document.addEventListener("DOMContentLoaded", function() {
    var languagesSection = document.getElementById("languages-section");
    var toggleButton = document.getElementById("toggle-languages-section");

    // Initially, languages section is visible
    languagesSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the languages section
        if (languagesSection.style.display === "none") {
            languagesSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            languagesSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


// TOGGLE TO HIDE CERTIFICATE SECTION
document.addEventListener("DOMContentLoaded", function() {
    var certificatesSection = document.getElementById("certificates-section");
    var toggleButton = document.getElementById("toggle-certificates-section");

    // Initially, certificates section is visible
    certificatesSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the certificates section
        if (certificatesSection.style.display === "none") {
            certificatesSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            certificatesSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});


