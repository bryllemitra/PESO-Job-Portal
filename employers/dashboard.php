<?php
include '../includes/config.php';
include '../includes/header.php';

// Check if the session role is set
if (!isset($_SESSION['role'])) {
    // If not logged in (session role not set), show the modal
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    You must be logged in as an employer to access this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' id='redirectBtn'>OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    <script type='text/javascript'>
        document.getElementById('redirectBtn').addEventListener('click', function() {
            window.location.href = '../index.php'; // Redirect to the home page when the button is clicked
        });
    </script>
    ";
    exit();
}

if ($_SESSION['role'] === 'admin') {
    // Redirect admin to the admin dashboard if they try to access this page
    echo "<script>window.location.href = '../admin/admin.php';</script>";
    exit();
} elseif ($_SESSION['role'] !== 'employer') {
    // If the user is not an employer, show a modal
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    You do not have permission to access this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' id='redirectBtn'>OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    <script type='text/javascript'>
        document.getElementById('redirectBtn').addEventListener('click', function() {
            window.location.href = '../index.php'; // Redirect to the home page when the button is clicked
        });
    </script>
    ";
    exit();
}

// Fetch total applicants (with and without job postings)
$applicants_query = "
    SELECT 
        COUNT(DISTINCT a.id) AS total_applicants,
        COUNT(DISTINCT CASE WHEN a.status = 'Accepted' THEN a.id END) AS accepted,
        COUNT(DISTINCT CASE WHEN a.status = 'Rejected' THEN a.id END) AS rejected,
        COUNT(DISTINCT CASE WHEN a.status = 'Pending' THEN a.id END) AS pending
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = " . $_SESSION['user_id'];  // Adjust with employer's job ID
$applicants_result = $conn->query($applicants_query);
$applicants_data = $applicants_result->fetch_assoc();

// Fetch job statistics (total jobs, jobs with applicants, jobs without applicants)
$job_query = "
    SELECT 
        COUNT(DISTINCT j.id) AS total_jobs,
        COUNT(DISTINCT CASE WHEN a.job_id IS NOT NULL THEN j.id END) AS jobs_with_applicants,
        COUNT(DISTINCT CASE WHEN a.job_id IS NULL THEN j.id END) AS jobs_without_applicants,
        COUNT(DISTINCT CASE WHEN j.status = 'Approved' THEN j.id END) AS approved,
        COUNT(DISTINCT CASE WHEN j.status = 'Rejected' THEN j.id END) AS rejected,
        COUNT(DISTINCT CASE WHEN j.status = 'Pending' THEN j.id END) AS pending
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.employer_id = " . $_SESSION['user_id'];  // Adjust with employer's job ID
$job_result = $conn->query($job_query);
$job_data = $job_result->fetch_assoc();

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
        'week_number' => date('W', strtotime($current_week_start))
    ];
    $current_week_start = date('Y-m-d', strtotime('+1 week', strtotime($current_week_start)));
}

// Fetch applicants per week for the current month
$applicants_per_week_query = "
    SELECT 
        YEARWEEK(a.applied_at) AS week,
        COUNT(DISTINCT a.id) AS total_applicants
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = " . $_SESSION['user_id'] . "
    AND YEAR(a.applied_at) = $current_year
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h2>Employer Panel</h2>
        <ul>
            <li><a href="employer.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="job_list.php"><i class="fas fa-briefcase"></i> My Jobs</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Applicants</a></li>
        </ul>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-angle-right"></i>
    </div>
</div>

<!-- Main Content -->
<div class="main-content mt-4" id="mainContent">
    <div class="header">
        <h1>Dashboard</h1>
    </div>
    
 <!-- Stats Section -->
<div class="row g-4 d-flex justify-content-center text-center">
    <div class="col-md-4">
        <div class="stats-card card h-100">
            <div class="card-body text-center">
                <h5 class="card-title text-info"><i class="fas fa-briefcase me-2"></i> Total Jobs</h5>
                <p class="card-text"><?= $job_data['total_jobs']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card card h-100">
            <div class="card-body text-center">
                <h5 class="card-title text-primary"><i class="fas fa-users me-2"></i> Total Applicants</h5>
                <p class="card-text"><?= $applicants_data['total_applicants']; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mt-4 g-4">
    <!-- Jobs with/without Applicants Chart -->
    <div class="col-md-6">
        <div class="card h-100 shadow-lg border-0 rounded-3 chart-card">
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-briefcase me-2"></i> Total Job Post
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="jobApplicantsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 shadow-lg border-0 rounded-3 chart-card">
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-user-check me-2"></i> Total Applicants
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="applicantStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>





<!-- Line chart for total applicants per week -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-lg border-0 rounded-3 chart-card" style="height: 500px;">
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-chart-line me-2"></i> Total Applicants per Week
            </div>
            <div class="card-body" style="height: 100%;">
                <!-- Dynamic Label -->
                <h6 id="dynamicLabel" class="text-center mb-3"></h6>
                <canvas id="totalApplicationsChart" style="height: 100%; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>




<!-- Chart.js Script -->
<script>
// Applicant Status (Bar Chart)
const ctxApplicantStatus = document.getElementById('applicantStatusChart').getContext('2d');
const applicantStatusChart = new Chart(ctxApplicantStatus, {
    type: 'bar',
    data: {
        labels: ['Accepted', 'Rejected', 'Pending'],
        datasets: [{
            label: 'Applicant Status',
            data: [<?= $applicants_data['accepted']; ?>, <?= $applicants_data['rejected']; ?>, <?= $applicants_data['pending']; ?>],
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


// Jobs with/without Applicants (Pie Chart)
const ctxJobApplicants = document.getElementById('jobApplicantsChart').getContext('2d');
const jobApplicantsChart = new Chart(ctxJobApplicants, {
    type: 'pie',
    data: {
        labels: ['Jobs with Applicants', 'Jobs without Applicants'],
        datasets: [{
            data: [<?= $job_data['jobs_with_applicants']; ?>, <?= $job_data['jobs_without_applicants']; ?>],
            backgroundColor: [
                'rgba(28, 200, 138, 0.8)', // Gradient Green (With Applicants)
                'rgba(255, 165, 0, 0.8)'  // Gradient Orange (Without Applicants)
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

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('hidden');
    }
</script>


</body>
</html>
