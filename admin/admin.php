<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/index.php");
    exit();
}

// Fetch the count of jobs
$job_query = "SELECT COUNT(*) AS total_jobs FROM jobs";
$job_result = $conn->query($job_query);
$job_data = $job_result->fetch_assoc();
$total_jobs = $job_data['total_jobs'];

// Fetch the count of users
$user_query = "SELECT COUNT(*) AS total_users FROM users";
$user_result = $conn->query($user_query);
$user_data = $user_result->fetch_assoc();
$total_users = $user_data['total_users'];

// Fetch total number of jobs (with and without applicants)
$total_jobs_query = "
    SELECT 
        COUNT(DISTINCT j.id) AS total_jobs, 
        COUNT(DISTINCT CASE WHEN a.job_id IS NOT NULL THEN j.id END) AS jobs_with_applicants,
        COUNT(DISTINCT CASE WHEN a.job_id IS NULL THEN j.id END) AS jobs_without_applicants
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id";
$total_jobs_result = $conn->query($total_jobs_query);
$total_jobs_data = $total_jobs_result->fetch_assoc();

// Fetch total number of users by gender
$query_gender_data = "SELECT 
                        SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) AS male_users,
                        SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) AS female_users,
                        SUM(CASE WHEN gender = 'Other' THEN 1 ELSE 0 END) AS other_users,
                        SUM(CASE WHEN gender = 'Non-Binary' THEN 1 ELSE 0 END) AS non_binary_users,
                        SUM(CASE WHEN gender = 'LGBTQ+' THEN 1 ELSE 0 END) AS lgbtq_users
                      FROM users";
$result_gender_data = $conn->query($query_gender_data);
$user_gender_data = $result_gender_data->fetch_assoc();

// Fetch total applicants status (accepted, rejected, pending)
$applicant_status_query = "
    SELECT 
        SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) AS accepted,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending
    FROM applications";
$applicant_status_result = $conn->query($applicant_status_query);
$applicant_status_data = $applicant_status_result->fetch_assoc();

// Fetch total job statuses (approved, rejected, pending)
$job_status_query = "
    SELECT 
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending
    FROM jobs";
$job_status_result = $conn->query($job_status_query);
$job_status_data = $job_status_result->fetch_assoc();

// Store the total counts for output
$total_users = array_sum($user_gender_data);
$total_jobs = array_sum($job_status_data);
$total_applicants = array_sum($applicant_status_data);

// Fetch applicants per week for the current month
$current_year = date('Y');
$current_month = date('m');

// Generate a list of all weeks in the current month
$weeks_in_month = [];
$first_day_of_month = date("$current_year-$current_month-01");
$last_day_of_month = date("Y-m-t", strtotime($first_day_of_month));

$current_week_start = $first_day_of_month;
while ($current_week_start <= $last_day_of_month) {
    $current_week_end = date('Y-m-d', strtotime('+6 days', strtotime($current_week_start)));
    $weeks_in_month[] = [
        'week_start' => $current_week_start,
        'week_end' => $current_week_end,
        'week_number' => date('W', strtotime($current_week_start)) // Use ISO week number
    ];
    $current_week_start = date('Y-m-d', strtotime('+1 week', strtotime($current_week_start)));
}

// Fetch applicants per week for all job posts
$applicants_per_week_query = "
    SELECT 
        YEARWEEK(a.applied_at) AS week,
        COUNT(DISTINCT a.id) AS total_applicants
    FROM applications a
    WHERE YEAR(a.applied_at) = $current_year
    AND MONTH(a.applied_at) = $current_month
    GROUP BY YEARWEEK(a.applied_at)
    ORDER BY week ASC";
$applicants_per_week_result = $conn->query($applicants_per_week_query);
$applicants_per_week_data = $applicants_per_week_result->fetch_all(MYSQLI_ASSOC);

// Map the fetched data to the weeks in the current month
$applicants_per_week = [];
foreach ($weeks_in_month as $week) {
    $week_number = $week['week_number'];
    $found = false;
    foreach ($applicants_per_week_data as $data) {
        if ($data['week'] == $current_year . $week_number) {
            $applicants_per_week[] = [
                'week' => $week_number,
                'total_applicants' => $data['total_applicants']
            ];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $applicants_per_week[] = [
            'week' => $week_number,
            'total_applicants' => 0
        ];
    }
}

// Convert the data to JSON for JavaScript
$applicants_per_week_json = json_encode($applicants_per_week);

// Check if there is a login message to display
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    unset($_SESSION['login_message']); // Clear the message after displaying it
} else {
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Font Awesome Icons -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/JOB/assets/admin.css">
    <style>
        /* Target the bar chart cards specifically */
.bar-chart-card .card-body {
    height: 400px;  /* Set your desired height here */
}

.bar-chart-card canvas {
    height: 100% !important;  /* Make sure the canvas fills its container */
}

/* Make the chart container responsive */
.chart-card {
    height: 400px; /* Default height for large screens */
}

@media (max-width: 768px) {
    .chart-card {
        height: 300px; /* Adjust height for medium screens */
    }
}

@media (max-width: 576px) {
    .chart-card {
        height: 250px; /* Adjust height for small screens */
    }
}

#totalApplicationsChart {
    width: 100% !important;  /* Make canvas width responsive */
    height: 100% !important; /* Ensure height adjusts accordingly */
}


    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div>
        <h2 >Admin Panel</h2>
        <ul>
            <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="employer_hiring.php"><i class="fas fa-person-circle-question"></i> Employer Request</a></li>
            <li><a href="job_list.php"><i class="fas fa-briefcase"></i> Job List</a></li>
            <li><a href="job_approval.php "><i class="fas fa-clipboard-check"></i> Job Approvals</a></li>
            <li><a href="feedback_bin.php"><i class="fas fa-trash-alt"></i> Feedback Bin</a></li>
            
        </ul>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-angle-right"></i>
    </div>
</div>

<!-- Main Content -->
<div class="mt-4 main-content" id="mainContent">
    <div class="header">
        <h1>Dashboard</h1>
    </div>
    
        <!-- Additional Stats Section -->
        <div class="row  g-4">
        <div class="col-md-4">
            <div class="stats-card card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-success"><i class="fas fa-user-check me-2"></i> Active Users</h5>
                    <p  class="card-text"><?= $total_users ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-info"><i class="fas fa-briefcase me-2"></i> Total Jobs</h5>
                    <p class="card-text"><?= $total_jobs ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card card h-100">
            <div class="card-body text-center">
                <h5 class="card-title text-primary"><i class="fas fa-users me-2"></i> Total Applicants</h5>
                <p class="card-text"><?= $total_applicants ?></p> <!-- Replace with actual total applicants -->
            </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4 g-4">
        <!-- Jobs with/without Applicants Chart (Pie Chart) -->
        <div class="col-md-6 col-lg-6">
            <div class="card h-100 shadow-lg border-0 rounded-3 chart-card">
                <div class="card-header bg-transparent text-white">
                    <i class="fas fa-briefcase me-2"></i> Total Job Postings
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="jobsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Job Status Chart (Pie Chart) -->
        <div class="col-md-6 col-lg-6">
            <div class="card h-100 shadow-lg border-0 rounded-3 chart-card">
                <div class="card-header bg-transparent text-white">
                    <i class="fas fa-check-circle me-2"></i> Job Status
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="jobStatusChart"></canvas>
                </div>
            </div>
        </div>
       
    </div>

    <!-- Additional Stats Section (for Applicants and Job Status) -->
    <div class="row mt-4 g-4">
<!-- Applicant Status Chart (Bar Chart) -->
<div class="col-md-6 col-lg-6">
    <div class="card h-100 shadow-lg border-0 rounded-3 chart-card bar-chart-card">
        <div class="card-header bg-transparent text-white">
            <i class="fas fa-user-check me-2"></i> Applicant Status
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="applicantStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- User Gender Distribution Chart (Bar Chart) -->
<div class="col-md-6 col-lg-6">
    <div class="card h-100 shadow-lg border-0 rounded-3 chart-card bar-chart-card">
        <div class="card-header bg-transparent text-white">
            <i class="fas fa-users me-2"></i> Registered Users
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="genderChart"></canvas>
        </div>
    </div>
</div>

    </div>


<!-- Line Chart for Total Applicants per Week -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-lg border-0 rounded-3 chart-card" style="height: 500px;">
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-chart-line me-2"></i> Total Applicants per Week
            </div>
            <div class="card-body" style="height: 80%;">
                <!-- Dynamic Label -->
                <h6 id="dynamicLabel" class="text-center mb-3"></h6>
                <canvas id="totalApplicationsChart" style="height: 80%; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

</div>

</div>


<script>
 // Jobs with and without Applicants (Pie Chart)
const ctxJobs = document.getElementById('jobsChart').getContext('2d');
const jobsChart = new Chart(ctxJobs, {
    type: 'pie',
    data: {
        labels: ['With Applicants', 'Without Applicants'],
        datasets: [{
            data: [<?= $total_jobs_data['jobs_with_applicants']; ?>, <?= $total_jobs_data['jobs_without_applicants']; ?>],
            backgroundColor: [
                'rgba(57, 174, 255, 0.8)', // Gradient Light Blue
                'rgba(134, 219, 129, 0.8)'  // Gradient Light Green
            ],
            borderColor: ['#ffffff', '#ffffff'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 14,
                        family: 'Segoe UI'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.label}: ${context.raw} Jobs`;
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            duration: 1000
        }
    }
});

// User Gender Distribution (Bar Chart)
const ctxGender = document.getElementById('genderChart').getContext('2d');
const genderChart = new Chart(ctxGender, {
    type: 'bar',
    data: {
        labels: ['Male', 'Female', 'Other', 'Non-Binary', 'LGBTQ+'], // Added Non-Binary and LGBTQ+ categories, keeping "Other"
        datasets: [{
            label: 'User Count',
            data: [
                <?= $user_gender_data['male_users']; ?>, 
                <?= $user_gender_data['female_users']; ?>, 
                <?= $user_gender_data['other_users']; ?>, 
                <?= $user_gender_data['non_binary_users']; ?>,  // Add Non-Binary data
                <?= $user_gender_data['lgbtq_users']; ?>],    // Add LGBTQ+ data
            backgroundColor: [
                'rgba(0, 204, 255, 0.8)', // Gradient Cyan for Male
                'rgba(255, 105, 180, 0.8)', // Gradient Pink for Female
                'rgba(255, 165, 0, 0.8)',  // Gradient Orange for Other
                'rgba(128, 0, 128, 0.8)', // Gradient Purple for Non-Binary
                'rgba(75, 0, 130, 0.8)' // Gradient Indigo for LGBTQ+
            ],
            borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff', '#ffffff'], // Border colors for each category
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    font: {
                        size: 12,
                        family: 'Segoe UI'
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12,
                        family: 'Segoe UI'
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.dataset.label}: ${context.raw} Users`;
                    }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuad'
        }
    }
});


// Applicant Status (Bar Chart)
const ctxApplicantStatus = document.getElementById('applicantStatusChart').getContext('2d');
const applicantStatusChart = new Chart(ctxApplicantStatus, {
    type: 'bar',
    data: {
        labels: ['Accepted', 'Rejected', 'Pending'],
        datasets: [{
            label: 'Applicant Status',
            data: [<?= $applicant_status_data['accepted']; ?>, <?= $applicant_status_data['rejected']; ?>, <?= $applicant_status_data['pending']; ?>],
            backgroundColor: [
                'rgba(57, 174, 255, 0.8)', // Gradient Light Blue (Pending)
                'rgba(255, 105, 180, 0.8)', // Gradient Pink (Rejected)
                'rgba(255, 165, 0, 0.8)' // Gradient Orange (Accepted)
            ],
            borderColor: ['#ffffff', '#ffffff', '#ffffff'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    font: {
                        size: 12,
                        family: 'Segoe UI'
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12,
                        family: 'Segoe UI'
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.dataset.label}: ${context.raw} Applicants`;
                    }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuad'
        }
    }
});

// Job Status (Pie Chart)
const ctxJobStatus = document.getElementById('jobStatusChart').getContext('2d');
const jobStatusChart = new Chart(ctxJobStatus, {
    type: 'pie',
    data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{
            data: [<?= $job_status_data['approved']; ?>, <?= $job_status_data['rejected']; ?>, <?= $job_status_data['pending']; ?>],
            backgroundColor: [
                'rgba(0, 204, 255, 0.8)', // Gradient Cyan (Pending)
                'rgba(255, 105, 180, 0.8)', // Gradient Pink (Rejected)
                'rgba(255, 165, 0, 0.8)'  // Gradient Orange (Approved)
            ],
            borderColor: ['#ffffff', '#ffffff', '#ffffff'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 14,
                        family: 'Segoe UI'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.label}: ${context.raw} Jobs`;
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            duration: 1000
        }
    }
});

// Parse the JSON data from PHP
const applicantsPerWeekData = <?php echo $applicants_per_week_json; ?>;

// Extract weeks and applicants data
const weeks = applicantsPerWeekData.map(item => `Week ${item.week}`);
const applicants = applicantsPerWeekData.map(item => item.total_applicants);

// Get the current date
const currentDate = new Date();
const currentMonth = currentDate.toLocaleString('default', { month: 'long' }); // Full month name (e.g., "March")
const currentDay = currentDate.getDate(); // Day of the month (e.g., 27)
const currentWeekOfYear = getWeekOfYear(currentDate); // Week of the year (e.g., 13)
const currentYear = currentDate.getFullYear(); // Current year (e.g., 2023)

// Function to get the week of the year
function getWeekOfYear(date) {
    const startOfYear = new Date(date.getFullYear(), 0, 1);
    const pastDaysOfYear = (date - startOfYear) / 86400000;
    return Math.ceil((pastDaysOfYear + startOfYear.getDay() + 1) / 7);
}

// Set the dynamic label in the HTML
const dynamicLabel = document.getElementById('dynamicLabel');
dynamicLabel.textContent = `Applicants for ${currentMonth} ${currentDay} (Week ${currentWeekOfYear}, ${currentYear})`;

// Render the chart
const ctx = document.getElementById('totalApplicationsChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: weeks,
        datasets: [{
            label: 'Total Applicants', // Generic label for the dataset
            data: applicants,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true, // Make the chart responsive
        maintainAspectRatio: false, // Allow the aspect ratio to change with container
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    title: (context) => `Week ${context[0].label.replace('Week ', '')}`, // Custom tooltip title
                    label: (context) => `Applicants: ${context.raw}` // Custom tooltip label
                }
            }
        }
    }
});


</script>




<script>
        // Get the message from the URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');

        // Display SweetAlert2 notification if there is a message
        if (message) {
            Swal.fire({
                title: "Successfully logged in!",
                text: message,
                icon: "success", // You can remove this line if you don't want any icon
                showConfirmButton: true, // Show the close button
                confirmButtonText: "Close", // Customize the close button text
                timer: 5000, // Auto-close after 3 seconds
                timerProgressBar: true, // Show a progress bar
                showClass: {
                    popup: 'swal2-noanimation', // Disable animation for the popup
                    backdrop: 'swal2-noanimation' // Disable animation for the backdrop
                },
                hideClass: {
                    popup: '', // No special class for hiding the popup
                    backdrop: '' // No special class for hiding the backdrop
                }
            }).then(() => {
                // Remove the 'message' query parameter from the URL
                urlParams.delete('message');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, document.title, newUrl);
            });
        }


    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('hidden');
    }
</script>

</body>
</html>