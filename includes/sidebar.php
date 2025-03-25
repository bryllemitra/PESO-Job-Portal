<!-- sidebar.php -->
<div class="sidebar" id="sidebar">
    <div>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="user_list.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'user_list.php') ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="employer_hiring.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'employer_hiring.php') ? 'active' : '' ?>"><i class="fas fa-person-circle-question"></i> Employer Request</a></li>
            <li><a href="job_list.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'job_list.php') ? 'active' : '' ?>"><i class="fas fa-briefcase"></i> Job List</a></li>
            <li><a href="job_approval.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'job_approval.php') ? 'active' : '' ?>"><i class="fas fa-clipboard-check"></i> Job Approvals</a></li>
            <li><a href="feedback_bin.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'feedback_bin.php') ? 'active' : '' ?>"><i class="fas fa-trash-alt"></i> Feedback Bin</a></li> 
        </ul>
    </div>
</div>

<!-- Toggle Button outside Sidebar -->
<button id="toggleSidebar" class="toggle-btn">&#9776;</button>

<!-- Additional Styles -->
<style>
/* Sidebar */
.sidebar {
    position: fixed;
    top: 70px;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #ffffff;
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
    z-index: 1000; /* Sidebar in front of content */
}

/* Hide sidebar by default on mobile */
.sidebar.hidden {
    transform: translateX(-100%);
}

/* Sidebar heading styles */
.sidebar h2 {
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
    color: #4a90e2;
}

.sidebar ul {
    list-style: none;
}

.sidebar ul li {
    margin-bottom: 15px;
}

.sidebar ul li a {
    text-decoration: none;
    color: #333;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 10px;
    transition: background 0.3s, color 0.3s;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
    background: #eaf4ff;
    color: #4a90e2;
}

/* Toggle Button Styles */
.toggle-btn {
    position: fixed; /* Change to fixed position outside sidebar */
    bottom: 20px; /* Position at the bottom of the page */
    right: 20px; /* Right side of the screen */
    background-color: #4a90e2;
    color: white;
    border: none;
    padding: 15px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    z-index: 1100; /* Ensure the toggle button stays on top */
    display: none; /* Hide on larger screens */
}

/* Show the toggle button on smaller screens */
@media screen and (max-width: 768px) {
    .toggle-btn {
        display: block;
    }
}
</style>

<!-- JavaScript -->
<script>
    // Get the sidebar and toggle button elements
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');

    // Function to handle the sidebar visibility based on screen width
    function checkWindowSize() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('hidden'); // Ensure sidebar is visible on large screens
            toggleBtn.style.display = 'none';   // Hide toggle button on larger screens
        } else {
            toggleBtn.style.display = 'block';  // Show toggle button on smaller screens
        }
    }

    // Add event listener to toggle the sidebar visibility
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('hidden');
    });

    // Ensure the sidebar starts hidden on mobile and auto hides on page load
    window.addEventListener('load', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('hidden');  // Hide sidebar by default on mobile
        }
    });

    // Check window size on page load and resize
    window.addEventListener('load', checkWindowSize);
    window.addEventListener('resize', checkWindowSize);

    // Auto-hide sidebar when clicking outside the sidebar
    document.addEventListener('click', function(event) {
        // Check if the click is outside the sidebar and toggle button
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && window.innerWidth <= 768) {
            sidebar.classList.add('hidden'); // Hide sidebar if clicked outside
        }
    });
</script>
