<?php
// Footer content for the job portal
?>
<!-- Wrapper for the page content and footer -->
<div class="page-wrapper">
    <!-- Main Content -->
    <div class="main-content">
        <!-- Your page content here -->
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <!-- Footer Links -->
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../pages/about.php">About Us</a></li>
                        <li><a href="../pages/announcement.php">Announcements</a></li>
                        <li><a href="../pages/browse.php">Browse Jobs</a></li>
                        <li><a href="../pages/contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <!-- Social Media Icons -->
                <div class="social-media">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/venchansalido" target="_blank" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://venardjhoncsalido.netlify.app/" target="_blank" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/venplaystrings/" target="_blank" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/venard-jhon-cabahug-salido-08041434b/" target="_blank" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <!-- Copyright -->
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Zamboanga PESO Job Portal. All rights reserved.</p>
                    <p>&copy; Salido, Sardani, Umipig, Sarausos</p>
                    <p> Western Mindanao State University BSIT-3A</p>
                </div>
            </div>
        </div>
    </footer>
</div>

<!-- Font Awesome Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Footer CSS -->
<style>
    /* Make sure the entire page is filled vertically */
    html, body {
        height: 100%;
        margin: 0;
    }

    /* Flex container for the entire page */
    .page-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 70vh; /* Ensures footer stays at the bottom */
    }

    /* Main content area will fill the available space */
    .main-content {
        flex: 1;
    }

    /* General Footer Styling */
    .site-footer {
        background: #f9f9f9; /* Light gray background */
        color: #333; /* Dark text for contrast */
        padding: 20px 0;
        width: 100%;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    }

    .site-footer .container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
    }

    /* Footer Links */
    .footer-links h4 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333; /* Darker heading color */
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links ul li {
        margin-bottom: 8px;
    }

    .footer-links ul li a {
        color: #666; /* Soft gray for links */
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-links ul li a:hover {
        color: #1abc9c; /* Teal hover effect */
    }

    /* Social Media Icons */
    .social-icons {
        display: flex;
        gap: 15px;
    }

    .social-icon {
        font-size: 25px;
        color: #666; /* Default gray color */
        transition: color 0.3s ease;
    }

    /* Facebook hover color */
    .social-icon.facebook:hover {
        color: #3b5998; /* Facebook Blue */
    }

    /* Twitter hover color */
    .social-icon.twitter:hover {
        color: #1da1f2; /* Twitter Blue */
    }

    /* Instagram hover color */
    .social-icon.instagram:hover {
        color: #e1306c; /* Instagram Pink */
    }

    /* LinkedIn hover color */
    .social-icon.linkedin:hover {
        color: #0077b5; /* LinkedIn Blue */
    }

    /* Copyright */
    .footer-copyright p {
        font-size: 14px;
        margin: 0;
        text-align: center;
        width: 100%;
        margin-top: 10px;
        color: #666; /* Soft gray for copyright text */
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            align-items: center;
        }

        .footer-links,
        .social-media {
            margin-bottom: 20px;
            text-align: center;
        }

        .footer-links ul {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    }
</style>
