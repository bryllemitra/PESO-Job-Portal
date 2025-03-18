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

// Query to get total applications per week
$query_applications = "SELECT WEEK(applied_at) AS week_number, COUNT(*) AS total_applications 
                        FROM applications 
                        GROUP BY week_number 
                        ORDER BY week_number";
$result_applications = $conn->query($query_applications);

// Fetch data for applications
$applications_data = [];
if ($result_applications->num_rows > 0) {
    while ($row = $result_applications->fetch_assoc()) {
        $applications_data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Font Awesome Icons -->
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

    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="job_list.php"><i class="fas fa-briefcase"></i> Job List</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="feedback_bin.php"><i class="fas fa-trash-alt"></i> Feedback Bin</a></li>
            <li><a href="job_approval.php "><i class="fas fa-clipboard-check"></i> Job Approvals</a></li>
        </ul>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-angle-right"></i>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="header">
        <h1>Admin Dashboard</h1>
    </div>
    
        <!-- Additional Stats Section -->
        <div class="row mt-4 g-4">
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
        <div class="card shadow-lg border-0 rounded-3 chart-card" style="height: 500px;"> <!-- Adjust height here -->
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-chart-line me-2"></i> Total Applicants per Week
            </div>
            <div class="card-body" style="height: 100%;"> <!-- Ensure card body takes full height -->
                <canvas id="totalApplicationsChart" style="height: 100%; width: 100%;"></canvas> <!-- Adjust height and width for the canvas -->
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

// Line chart for Total Applications per Week
var ctx5 = document.getElementById('totalApplicationsChart').getContext('2d');
var totalApplicationsChart = new Chart(ctx5, {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'], // Weekly labels
        datasets: [{
            label: 'Total Applications', // Chart label
            data: [
                <?= isset($total_applications[1]) ? $total_applications[1] : 0 ?>, // Week 1 data
                <?= isset($total_applications[2]) ? $total_applications[2] : 0 ?>, // Week 2 data
                <?= isset($total_applications[3]) ? $total_applications[3] : 0 ?>, // Week 3 data
                <?= isset($total_applications[4]) ? $total_applications[4] : 0 ?>, // Week 4 data
                <?= isset($total_applications[5]) ? $total_applications[5] : 0 ?>  // Week 5 data
            ],
            backgroundColor: 'rgba(28, 200, 138, 0.2)', // Light green color
            borderColor: '#1cc88a', // Dark green color for the line
            fill: false,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

</script>




<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('hidden');
    }
</script>

</body>
</html>