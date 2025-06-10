<?php
session_start();
require_once '../includes/dbh.php'; // Make sure this returns a MySQLi OOP connection $conn

// Fetch project user profile picture and name for the navbar
$profile_picture_navbar = 'images/resources/default.jpg'; // Default fallback path relative to applications.php
$navbar_project_name = 'Project'; // Default name for navbar

// IMPORTANT: ALL PHP LOGIC THAT MIGHT SEND HEADERS (LIKE REDIRECTS) MUST BE HERE
// BEFORE ANY HTML OR BLANK LINES

if (!isset($_SESSION['project_id'])) {
    // If not logged in, redirect to login.php
    header("Location: ../projects/login.php");
    exit(); // Always call exit() after header() to prevent further script execution
}

// Now that we are sure the user is logged in, fetch their project details
$project_id = $_SESSION['project_id'];
$profile_sql = "SELECT profile_picture, project_name FROM project_profiles WHERE project_id = ?"; // Added project_name
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->bind_param("i", $project_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();

if ($profile_result && $profile_result->num_rows > 0) {
    $profile_row = $profile_result->fetch_assoc();
    if (!empty($profile_row['profile_picture'])) {
        // Adjust path for profile picture, remove '../' if it's already in the correct web-accessible path
        $profile_picture_navbar = str_replace('../', '', $profile_row['profile_picture']);
    }
    $navbar_project_name = ($profile_row['project_name']); // Set for navbar alt text
}
$profile_stmt->close();

// Close connection if no other database operations will occur,
// but for a dynamic page, often kept open until end of script or managed by a persistent connection.
// $conn->close(); // Consider if you want to close it here or at the very end of the file.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

        /* Navbar styles from applications.php */
        .navbar {
            background-color: #000;
            min-height: 90px;
            padding-top: 1rem;
            padding-bottom: 1rem;
            font-size: 1.5rem;
            border-bottom: solid #161b1f 0.5px;
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
        }

        .navbar-brand img {
            width: 50px;
            height: 50px;
        }

        .nav-link:hover {
            color: #F97D37 !important;
        }

        /* General button styling to match */
        .btn-primary {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-primary:hover {
            background-color: #e6692e;
            border-color: #e6692e;
        }

        /* Profile dropdown styles from applications.php */
        .profile-dropdown {
            position: relative;
        }

        .profile-picture-nav {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #F97D37;
            object-fit: cover;
            transition: border-color 0.3s;
        }

        .profile-picture-nav:hover {
            border-color: #fff;
        }

        .dropdown-menu {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .dropdown-item {
            color: #fff;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .dropdown-item:hover {
            background-color: #1a1a1a;
            color: #F97D37;
        }

        .dropdown-item:focus {
            background-color: #1a1a1a;
            color: #F97D37;
        }

        /* Page specific content styles */
        .listings-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        /* Modernized Listing Card Styles (similar to discoverprojects.php and screenshot) */
        .listing-card {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 0;
            transition: all 0.3s;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: flex; /* Using flexbox for layout within the card */
            flex-direction: column; /* Stack content vertically */
            height: 100%; /* Ensure cards in a grid have equal height */
        }

        .listing-card:hover {
            border-color: #F97D37;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1);
        }

        /* Top section for logo/banner (optional, but for consistency if projects have a banner) */
        .listing-card-top-banner {
            background-color: #212122; /* Dark grey similar to project-card-top-bg */
            height: 70px; /* Consistent height */
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            /* If no logo is meant to overlap, this can be removed or made smaller */
        }

        .listing-logo-container { /* If listings have their own logo/thumbnail */
            position: absolute;
            top: 25px; /* Aligns with project cards */
            left: 20px;
            width: 80px;
            height: 80px;
            border-radius: 15px;
            overflow: hidden;
            background-color: #1a1a1a;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .listing-logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
        }

        .listing-card-content-wrapper {
            padding: 20px;
            padding-top: 110px; /* Push content down to account for the potential overlapping logo */
            flex-grow: 1; /* Allow content to grow and push footer to bottom */
            position: relative;
            z-index: 0;
        }

        .listing-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        .listing-company-name { /* New style for company name if available */
            color: #aaa;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .listing-location, .listing-salary {
            color: #ddd;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .listing-salary {
            font-weight: bold; /* Make salary prominent like in the screenshot */
            color: #F97D37; /* Use accent color for salary for emphasis */
        }

        .listing-description {
            color: #ddd;
            line-height: 1.6;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .listing-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto; /* Push badges to the bottom if content grows */
        }

        .job-type-badge, .category-badge {
            background-color: #222;
            color: #F97D37; /* Accent color for badges */
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #F97D37;
        }
        .job-type-badge {
             /* Different color for job type if needed */
            color: #fff;
            background-color: #333;
            border: 1px solid #444;
        }


        .listing-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #333;
            background-color: #0a0a0a;
            display: flex;
            justify-content: flex-end; /* Align icon to the right */
            align-items: center;
        }

        /* Empty state styling */
        .empty-state {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #666;
            margin-bottom: 1.5rem;
            display: block;
        }

        .empty-state h4 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #fff;
        }

        .empty-state p {
            color: #aaa;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="../index.php">
      <img src="../images/resources/pursuelogo.svg" alt="Pursue Logo" class="me-2">
      Pursue for Projects
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link active" aria-current="page" href="listings.php">Listings</a>
        <a class="nav-link" href="applications.php">Applications</a>
        <a class="nav-link" href="create.php">Create</a>
        <a class="nav-link" href="discover.php">Discover</a>
      </div>

      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img
            src="../<?= ($profile_picture_navbar); ?>"
            alt="<?= $navbar_project_name; ?> Logo"
            class="profile-picture-nav"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            id="profileDropdown"
          >
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider" style="border-color: #333;"></li>
            <li><a class="dropdown-item" href="../includes/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="listings-container">
    <div class="page-header">
        <h1 class="page-title">My Listings</h1>
    </div>

    <div class="row" id="listings-grid">
    <?php
        // Fetch listings belonging to the logged-in project_id
        $sql = "SELECT listing_id, job_title, description, location, salary, job_type, category, slug, date_posted
                FROM listings
                WHERE project_id = ?
                ORDER BY date_posted DESC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $project_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $listing_id = ($row['listing_id']);
                    $job_title = ($row['job_title']);
                    $description = ($row['description']);
                    $location = ($row['location']);
                    $salary = ($row['salary']);
                    $job_type = ($row['job_type']);
                    $category = ($row['category']);
                    $slug = ($row['slug']);
                    $created_at = ($row['date_posted']);

                    $truncated_description = substr($description, 0, 150) . (strlen($description) > 150 ? '...' : '');

                    // CORRECTED SLUG LOGIC: Generate clean URL path similar to the second script
                    $listing_detail_url = "../listings/" . $slug;
                    ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="listing-card" onclick="window.location.href='<?= $listing_detail_url; ?>'" style="cursor: pointer;">
                            <div class="listing-card-top-banner"></div>
                            <div class="listing-logo-container">
                                <img src="../<?= $profile_picture_navbar; ?>" alt="<?= $navbar_project_name; ?> Logo" class="listing-logo">
                            </div>
                            <div class="listing-card-content-wrapper">
                                <h5 class="listing-title"><?= $job_title; ?></h5>
                                <p class="listing-company-name"><?= $navbar_project_name; ?></p>
                                <p class="listing-location"><i class="bi bi-geo-alt me-1"></i><?= $location; ?></p>
                                <p class="listing-salary">$<?= $salary; ?></p>
                                <p class="listing-description"><?= nl2br($truncated_description); ?></p>
                                <div class="listing-badges">
                                    <span class="job-type-badge"><i class="bi bi-briefcase me-1"></i><?= $job_type; ?></span>
                                    <span class="category-badge"><i class="bi bi-tag me-1"></i><?= $category; ?></span>
                                </div>
                            </div>
                            <div class="listing-footer d-flex justify-content-between align-items-center">
                                <i class="bi bi-arrow-right-circle" style="color: #F97D37; font-size: 1.2rem;"></i>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-archive"></i>
                        <h4>No Listings Found</h4>
                        <p class="text-muted mb-3">You haven\'t created any job listings yet.</p>
                        <a href="create.php" class="btn btn-primary">Create Your First Listing</a>
                    </div>
                </div>';
            }
            $stmt->close();
        } else {
            echo '<div class="col-12"><p class="text-danger">Error preparing database statement for listings.</p></div>';
            error_log("listings.php: Prepare statement failed: " . $conn->error);
        }
        // It's usually good practice to close the connection once all database operations are done
        // Or if you have a mechanism that handles it at the end of the script lifecycle.
        $conn->close(); 
    ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Bootstrap Toasts (if you implement any toast notifications here)
    // var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    // var toastList = toastElList.map(function (toastEl) {
    //     return new bootstrap.Toast(toastEl)
    // })
    // toastList.forEach(toast => toast.show())
</script>
</body>
</html>