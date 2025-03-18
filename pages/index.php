<?php
include '../includes/config.php';
include '../includes/header.php';


// Fetch ads from the database
$query = "SELECT * FROM ads ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$ads = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ads[] = $row;
}

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Fetch the latest cover photo from the 'homepage' table
$query = "SELECT cover_photo FROM homepage ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$cover_photo = $row ? "/JOB/uploads/" . htmlspecialchars($row['cover_photo']) : "/JOB/uploads/default/COVER.jpg"; // Default if no cover photo is set




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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Zamboanga City PESO Job Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/JOB/assets/index.css">
    
    <style>

        .header-content {
            display: flex;
            flex-direction: column; /* Ensure buttons stack vertically */
            align-items: center; /* Center buttons horizontally */
        }

        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
                }

        body {
            overflow-x: hidden;
        }

        .header {
            position: relative;
            width: 100%;
            max-width: 100vw;
            height: 50vh;
            background: url('<?php echo $cover_photo; ?>') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            color: white;
            padding-bottom: 20px;
            overflow: hidden;
            transition: height 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .header {
                height: 30vh;
            }
        }

        @media (max-width: 480px) {
            .header {
                height: 20vh;
            }
        }

        /* Modal Button Alignment */
        .modal-actions {
            display: flex;
            gap: 10px; /* Space between buttons */
            justify-content: flex-end; /* Align buttons to the right */
        }


        /* Edit Cover Button Styling */
        .edit-cover-btn {
            position: absolute; /* Position relative to the .header container */
            top: 20px; /* Distance from the top */
            right: 20px; /* Distance from the right */
            background-color: rgba(255, 255, 255, 0.85); /* Slight transparency */
            color: #000000;
            border: none;
            padding: 10px 20px;
            font-size: 0.8rem;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            z-index: 10; /* Ensure it stays above other elements */
        }

        .edit-cover-btn:hover {
            color:#4c6ef5;
            background-color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* Remove underline from all links */
a {
    text-decoration: none;
}

/* If you only want to remove the underline for feature links */
.feature-card a {
    text-decoration: none;
}

h3{
    color: #000;
}

    </style>
</head>
<body>  

<!-- Header Section -->
<div class="header">
    <?php if ($user_role === 'admin'): ?>
        <!-- Admin sees "Edit Cover" -->
        <button type="button" class="btn edit-cover-btn" data-bs-toggle="modal" data-bs-target="#editCoverModal">
            <i class="fas fa-camera"></i> Edit Cover
        </button>
    <?php else: ?>
        <!-- Non-admins/guests see "View Photo" -->
        <button type="button" class="btn edit-cover-btn" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
        <i class="fas fa-camera"></i> View Cover
        </button>
    <?php endif; ?>
    
    <div class="header-content">
        <button class="cta-button" onclick="window.location.href='about.php'">Discover More...</button>
    </div>
</div>

<!-- Cover Photo Modal (Only for Admins) -->
<?php if ($user_role === 'admin'): ?>
<div class="modal fade" id="editCoverModal" tabindex="-1" aria-labelledby="editCoverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCoverModalLabel">Edit Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="upload_cover_index.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    <div class="mb-3">
                        <label for="coverPhoto" class="form-label">Upload New Cover Photo</label>
                        <input type="file" class="form-control" name="cover_photo" id="coverPhoto" required>
                    </div>
                    <div class="modal-actions d-flex justify-content-between align-items-center">
                        <!-- View Photo Button -->
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
                            View Photo
                        </button>
                        <!-- Upload and Remove Buttons -->
                        <div class="d-flex gap-2">
                            <button style="background-color:#007bff; box-shadow: none;" type="submit" class="btn btn-primary">Upload</button>
                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#deleteCoverModal">Remove</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Photo Modal (For Everyone) -->
<div class="modal fade" id="viewPhotoModal" tabindex="-1" aria-labelledby="viewPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPhotoModalLabel">Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Full-Sized Image -->
                <img src="<?= htmlspecialchars($cover_photo) ?>" alt="Cover Photo" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>


    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCoverModal" tabindex="-1" aria-labelledby="deleteCoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCoverModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove the cover photo?
                </div>
                <div class="modal-footer">
                <form action="upload_cover_index.php" method="POST">
                        <input type="hidden" name="action" value="remove">
                        <button style="background-color:#007bff; box-shadow: none;" type="submit" class="btn btn-primary">Remove</button>
                    </form>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>

                </div>
            </div>
        </div>
    </div>

<!-- Features Section -->
<div style="margin-bottom:100px;" class="container features">
    <div class="feature-card">
        <a href="../pages/browse.php"> <!-- Link to Browse page for Job Listings -->
            <i class="fas fa-briefcase"></i>
            <h3>Job Listings</h3>
            <p>Find the perfect job that matches your skills and aspirations.</p>
        </a>
    </div>
    <div class="feature-card">
        <a href="../pages/employer_requests.php"> <!-- Link to Announcement page for Employer Support -->
            <i class="fas fa-handshake"></i>
            <h3>Employer Support</h3>
            <p>We assist employers in finding the right candidates for their needs.</p>
        </a>
    </div>
    <div class="feature-card">
        <a href="../admin/announcement.php"> <!-- Link to About Us page for Community Engagement -->
            <i class="fas fa-users"></i>
            <h3>Community Engagement</h3>
            <p>Empowering the community through employment opportunities.</p>
        </a>
    </div>
</div>

    </div>

        <!-- Ads Section -->
        <div class="container ads-section">
        <h2></h2>
        <?php if ($user_role === 'admin'): ?>
            <a href="../admin/add_ad.php" class="post-ad-button">Add Advertisement</a>
        <?php endif; ?>
        <div class="ad-slider" id="adSlider">
            <?php foreach ($ads as $ad): ?>
                <div class="ad-card-wrapper" data-ad-id="<?= htmlspecialchars($ad['id']) ?>">
                    <a href="<?= htmlspecialchars($ad['link_url']) ?>" target="_blank" class="ad-card-link">
                        <div class="ad-card">
                            <img 
                                src="/JOB/uploads/<?= htmlspecialchars($ad['image_file']) ?>" 
                                alt="<?= htmlspecialchars($ad['title']) ?>" 
                                onerror="this.onerror=null; this.src='/JOB/uploads/default/PESO.png';"
                            >
                            <div class="ad-card-content">
                                <h4><?= htmlspecialchars($ad['title']) ?></h4>
                                <p><?= htmlspecialchars($ad['description']) ?></p>
                            </div>
                        </div>
                    </a>
                    <?php if ($user_role === 'admin'): ?>
                        <button class="delete-ad-button" onclick="deleteAd(<?= htmlspecialchars($ad['id']) ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="slider-controls" id="sliderControls">
            <?php foreach ($ads as $index => $ad): ?>
                <button data-index="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>

<!-- Announcement Section -->
<div class="container announcement-section py-5">
    <h2 class="pb-4 border-bottom">Latest Announcements</h2>
    <div class="row g-5">
        <?php
        // Fetch the latest 2 announcements from the database
        $query = "SELECT id, title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 2";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $id = htmlspecialchars($row['id']);
                $title = htmlspecialchars($row['title']);
                $content = htmlspecialchars($row['content']); // Full content
                $short_content = htmlspecialchars(substr($row['content'], 0, 200)) . '...'; // Short excerpt
                $created_at = htmlspecialchars(date('F j, Y', strtotime($row['created_at'])));
                
                // Check if content is longer than the short excerpt
                $is_long_content = strlen($content) > 200;
        ?>
                <!-- Announcement Card -->
                <div class="col-md-6">
                    <article class="blog-post card-hover-effect">
                        <h3 class="blog-post-title"><?= $title ?></h3>
                        <p class="blog-post-meta"><?= $created_at ?></p>
                        <p class="short-content"><?= $short_content ?></p>
                        <p class="full-content" style="display: none;"><?= $content ?></p>
                        
                        <!-- Only show the 'Continue reading' button if the content is long -->
                        <?php if ($is_long_content): ?>
                            <button class="btn btn-outline-primary continue-reading">Continue reading</button>
                        <?php endif; ?>
                    </article>
                </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><p>No announcements available at the moment.</p></div>';
        }
        ?>
    </div>
    <div class="text-center mt-5">
        <a href="/JOB/admin/announcement.php" class="btn btn-primary">View All Announcements</a>
    </div>
</div>


<!-- Recent Jobs Section -->
<div class="container recent-jobs-section py-5">
    <h2 class="pb-4 border-bottom">Recently Posted Jobs</h2>
    <div id="jobCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Slides -->
        <div class="carousel-inner">
            <?php
            // Fetch the latest 5 jobs from the database
            $query = "
            SELECT j.id, j.title, j.description, j.responsibilities, j.requirements, j.preferred_qualifications, j.created_at, 
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC) AS categories, 
                   GROUP_CONCAT(DISTINCT p.position_name ORDER BY p.position_name ASC) AS positions,
                   j.location, j.thumbnail 
            FROM jobs j
            JOIN job_categories jc ON j.id = jc.job_id
            JOIN categories c ON jc.category_id = c.id
            JOIN job_positions_jobs jpj ON j.id = jpj.job_id
            JOIN job_positions p ON jpj.position_id = p.id
            WHERE j.status = 'approved'  -- Only fetch jobs that are approved
            GROUP BY j.id
            ORDER BY j.created_at DESC 
            LIMIT 5
        ";
        
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $first = true; // Flag to set the first item as active
            while ($row = mysqli_fetch_assoc($result)) {
                $id = htmlspecialchars($row['id']);
                $title = htmlspecialchars($row['title']);
                $short_description = htmlspecialchars(substr($row['description'], 0, 100)) . '...'; // Short excerpt
                $responsibilities = htmlspecialchars(substr($row['responsibilities'], 0, 100)) . '...'; // Short excerpt
                $requirements = htmlspecialchars(substr($row['requirements'], 0, 100)) . '...'; // Short excerpt
                $preferred_qualifications = htmlspecialchars(substr($row['preferred_qualifications'], 0, 100)) . '...'; // Short excerpt
                $created_at = htmlspecialchars(date('F j, Y', strtotime($row['created_at'])));
                $categories = htmlspecialchars($row['categories']); // Comma-separated list of categories
                $positions = htmlspecialchars($row['positions']); // Comma-separated list of positions
                $location = htmlspecialchars($row['location']);
                $thumbnail = htmlspecialchars($row['thumbnail']);
        
                // Generate thumbnail URL with fallback
                $thumbnail_url = !empty($thumbnail) && file_exists("../$thumbnail")
                    ? "../$thumbnail"
                    : "../uploads/default_image.jpg";
        ?>
                <!-- Job Slide -->
                <div class="carousel-item <?= $first ? 'active' : '' ?>">
                    <div class="job-card card-hover-effect">
                        <div class="row g-0">
                            <!-- Content -->
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h4 class="card-title"><?= $title ?></h4>
                                    <p class="card-category"><i class="fas fa-briefcase me-2"></i><?= $categories ?></p> <!-- All categories -->
                                    <p class="card-positions"><i class="fas fa-users me-2"></i><?= $positions ?></p> <!-- All positions -->
                                    <p class="card-location"><i class="fas fa-map-marker-alt me-2"></i><?= $location ?></p>
                                    <p class="card-description"><?= $short_description ?></p>
                                    <!-- Add Responsibilities, Requirements, and Preferred Qualifications -->
                                    <div class="extra-details mt-2">
                                        <p><strong>Responsibilities:</strong> <?= $responsibilities ?></p>
                                        <p><strong>Requirements:</strong> <?= $requirements ?></p>
                                        <p><strong>Preferred Qualifications:</strong> <?= $preferred_qualifications ?></p>
                                    </div>
                                    <a href="job.php?id=<?= $id ?>" class="btn btn-outline-primary w-30">View Details</a>
                                </div>
                            </div>
                            <!-- Thumbnail -->
                            <div class="col-md-4">
                                <img src="<?= $thumbnail_url ?>" alt="<?= $title ?>" class="card-thumbnail">
                            </div>
                        </div>
                    </div>
                </div>
        <?php
                $first = false; // After the first item, set flag to false
            }
        } else {
            echo '<div class="col-12 text-center"><p>No recent jobs available at the moment.</p></div>';
        }
            ?>
        </div>
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#jobCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#jobCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>


 


    <!-- Browse All Jobs Button -->
    <div class="text-center mt-4">
        <a href="browse.php" class="btn btn-primary">Browse All Jobs</a>
    </div>
</div>


    



    <!-- Delete Ad Confirmation Modal -->
<div class="modal fade" id="deleteAdModal" tabindex="-1" aria-labelledby="deleteAdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAdModalLabel">Confirm Remove Advertisement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this advertisement? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
      <button style="background-color:#007bff; box-shadow:none;" type="button" class="btn btn-primary" id="confirmDeleteAdBtn">Delete</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        
      </div>
    </div>
  </div>
</div>





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



document.addEventListener("DOMContentLoaded", function () {
    // Select all "View Details" buttons
    const buttons = document.querySelectorAll(".view-details");

    buttons.forEach((button) => {
        button.addEventListener("click", function () {
            const cardBody = button.closest(".card-body"); // Find the parent card body
            const shortContent = cardBody.querySelector(".short-content");
            const fullContent = cardBody.querySelector(".full-content");

            if (shortContent.style.display !== "none") {
                // Hide short content and show full content
                shortContent.style.display = "none";
                fullContent.style.display = "block";
                button.textContent = "Collapse"; // Change button text
            } else {
                // Show short content and hide full content
                shortContent.style.display = "block";
                fullContent.style.display = "none";
                button.textContent = "View Details"; // Change button text back
            }
        });
    });
});    


        document.addEventListener("DOMContentLoaded", function () {
    // Select all "Continue reading" buttons
    const buttons = document.querySelectorAll(".continue-reading");

    buttons.forEach((button) => {
        button.addEventListener("click", function () {
            const article = button.closest(".blog-post"); // Find the parent article
            const shortContent = article.querySelector(".short-content");
            const fullContent = article.querySelector(".full-content");

            if (shortContent.style.display !== "none") {
                // Hide short content and show full content
                shortContent.style.display = "none";
                fullContent.style.display = "block";
                button.textContent = "Collapse"; // Change button text
            } else {
                // Show short content and hide full content
                shortContent.style.display = "block";
                fullContent.style.display = "none";
                button.textContent = "Continue reading"; // Change button text back
            }
        });
    });
});

        const slider = document.getElementById('adSlider');
        const controls = document.getElementById('sliderControls').querySelectorAll('button');
        let currentIndex = 0;

        function updateSlider(index) {
            slider.style.transform = `translateX(-${index * 100}%)`;
            controls.forEach((control, i) => {
                control.classList.toggle('active', i === index);
            });
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % controls.length;
            updateSlider(currentIndex);
        }

        // Auto-slide every 5 seconds
        setInterval(nextSlide, 4000);

        // Manual control
        controls.forEach((control, index) => {
            control.addEventListener('click', () => {
                currentIndex = index;
                updateSlider(currentIndex);
            });
        });

// Function to delete an ad using AJAX with a confirmation modal and auto-refresh after deletion
function deleteAd(adId) {
    // Show the confirmation modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAdModal'));
    deleteModal.show();

    // Handle the "Delete" button click inside the confirmation modal
    document.getElementById('confirmDeleteAdBtn').addEventListener('click', function() {
        // Send AJAX request to delete the ad
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../includes/delete_ad_ajax.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Remove the ad card from the DOM
                    const adWrapper = document.querySelector(`[data-ad-id="${adId}"]`);
                    if (adWrapper) {
                        adWrapper.remove();
                    }
                    // Close the modal after successful deletion
                    deleteModal.hide();
                    
                    // Refresh the page to reflect the changes
                    location.reload();
                } else {
                    alert("Failed to delete the advertisement.");
                }
            }
        };
        xhr.send(`ad_id=${adId}`);
    });
}



        document.addEventListener("DOMContentLoaded", function () {
        var carousel = new bootstrap.Carousel(document.getElementById('jobCarousel'), {
            interval: 3000, // Auto-slide every 3 seconds
            wrap: true      // Loop back to the first slide after the last
        });
    });
    </script>

    
</body>
</html>

<?php include '../includes/footer.php'; ?>