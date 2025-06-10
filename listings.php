<?php
session_start();
require_once 'includes/dbh.php'; // Make sure this returns a MySQLi OOP connection $conn

$profile_picture = 'images/resources/default.jpg'; // Default fallback
$is_logged_in = false; // Flag to check if a talent is logged in

if (isset($_SESSION['talent_id'])) {
    $is_logged_in = true;
    $talent_id = $_SESSION['talent_id'];
    $profile_sql = "SELECT profile_picture FROM talent_profiles WHERE talent_id = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $talent_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile_row = $profile_result->fetch_assoc();
        if (!empty($profile_row['profile_picture'])) {
            $profile_picture = ($profile_row['profile_picture']);
        }
    }
    $profile_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Listings | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

        /* Navbar styles */
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

        .btn-success, .btn-success:hover, .btn-success:active, .btn-success:visited {
            background-color: #F97D37 !important;
            border: #F97D37 !important;
        }

        .btn-danger {
            background-color: #f44336;
            border: none;
        }

        .btn-primary {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-primary:hover {
            background-color: #e6692e;
            border-color: #e6692e;
        }

        /* Profile dropdown styles */
        .profile-dropdown {
            position: relative;
        }

        .profile-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #F97D37;
            object-fit: cover;
            transition: border-color 0.3s;
        }

        .profile-picture:hover {
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

        .listing-container {
            max-width: 1200px; /* Increased max-width for better card layout */
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #fff;
        }
        
        /* Modernized Listing Card Styles from projects/listings.php */
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
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .listing-card:hover {
            border-color: #F97D37;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1);
        }

        .listing-card-top-banner {
            background-color: #212122;
            height: 70px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .listing-logo-container {
            position: absolute;
            top: 25px;
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
            padding-top: 110px; /* Adjust if the logo overlap changes */
            flex-grow: 1;
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
        .listing-company-name {
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
            font-weight: bold;
            color: #F97D37;
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
            margin-top: auto;
        }

        .job-type-badge, .category-badge {
            background-color: #222;
            color: #F97D37;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #F97D37;
        }
        .job-type-badge {
            color: #fff;
            background-color: #333;
            border: 1px solid #444;
        }

        .listing-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #333;
            background-color: #0a0a0a;
            display: flex;
            justify-content: flex-end;
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

        .text-muted {
            color: #aaa !important;
        }

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
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/resources/pursuelogo.svg" alt="Pursue Logo" class="me-2">
            Pursue
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link active" aria-current="page" href="listings.php">Listings</a>
                <a class="nav-link" href="applications.php">Applications</a>
                <a class="nav-link" href="discover.php">Discover</a>
            </div>

            <div class="d-flex ms-auto">
                <?php if ($is_logged_in): ?>
                    <div class="dropdown profile-dropdown">
                        <img 
                            src="<?= $profile_picture; ?>" 
                            alt="Profile" 
                            class="profile-picture" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            id="profileDropdown"
                        >
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider" style="border-color: #333;"></li>
                            <li><a class="dropdown-item" href="includes/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="signup.php" class="btn btn-success">Signup</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="listing-container">
    <h1 class="page-title">Available Listings</h1>

    <div class="row" id="listings-grid">
    <?php
    $hasListings = false;

    if (isset($_GET['query']) && trim($_GET['query']) !== '') {
        $query = $_GET['query'];
        $search = "%" . $query . "%";

        $stmt = $conn->prepare("SELECT listing_id, job_title, description, location, salary, job_type, category, approval, slug, project_id FROM listings WHERE job_title LIKE ? OR description LIKE ? OR location LIKE ? OR category LIKE ?");
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['approval'] == 1) {
                    $hasListings = true;
                    $project_name_card = '';
                    $project_profile_picture_card = 'images/resources/default.jpg';
                    $project_id_for_listing = $row['project_id'];

                    $project_profile_sql = "SELECT profile_picture, project_name FROM project_profiles WHERE project_id = ?";
                    $project_profile_stmt = $conn->prepare($project_profile_sql);
                    $project_profile_stmt->bind_param("i", $project_id_for_listing);
                    $project_profile_stmt->execute();
                    $project_profile_result = $project_profile_stmt->get_result();
                    if ($project_profile_result && $project_profile_result->num_rows > 0) {
                        $project_profile_row = $project_profile_result->fetch_assoc();
                        if (!empty($project_profile_row['profile_picture'])) {
                            $project_profile_picture_card = str_replace('../', '', ($project_profile_row['profile_picture']));
                        }
                        $project_name_card = ($project_profile_row['project_name']);
                    }
                    $project_profile_stmt->close();

                    $listing_detail_url = "listings/" . ($row['slug']);
                    $truncated_description = substr(($row['description']), 0, 150) . (strlen($row['description']) > 150 ? '...' : '');
                    ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="listing-card" onclick="window.location.href='<?= $listing_detail_url; ?>'" style="cursor: pointer;">
                            <div class="listing-card-top-banner"></div>
                            <div class="listing-logo-container">
                                <img src="<?= $project_profile_picture_card; ?>" alt="<?= $project_name_card; ?> Logo" class="listing-logo">
                            </div>
                            <div class="listing-card-content-wrapper">
                                <h5 class="listing-title"><?= ($row['job_title']); ?></h5>
                                <p class="listing-company-name"><?= $project_name_card; ?></p>
                                <p class="listing-location"><i class="bi bi-geo-alt me-1"></i><?= ($row['location']); ?></p>
                                <p class="listing-salary">$<?= ($row['salary']); ?></p>
                                <p class="listing-description"><?= nl2br($truncated_description); ?></p>
                                <div class="listing-badges">
                                    <span class="job-type-badge"><i class="bi bi-briefcase me-1"></i><?= ($row['job_type']); ?></span>
                                    <span class="category-badge"><i class="bi bi-tag me-1"></i><?= ($row['category']); ?></span>
                                </div>
                            </div>
                            <div class="listing-footer d-flex justify-content-between align-items-center">
                                <i class="bi bi-arrow-right-circle" style="color: #F97D37; font-size: 1.2rem;"></i>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        $stmt->close();
    } else {
        $sql = "SELECT listing_id, job_title, description, location, salary, job_type, category, approval, slug, project_id FROM listings WHERE approval = 1 ORDER BY date_posted DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $hasListings = true;
                $project_name_card = '';
                $project_profile_picture_card = 'images/resources/default.jpg';
                $project_id_for_listing = $row['project_id'];

                $project_profile_sql = "SELECT profile_picture, project_name FROM project_profiles WHERE project_id = ?";
                $project_profile_stmt = $conn->prepare($project_profile_sql);
                $project_profile_stmt->bind_param("i", $project_id_for_listing);
                $project_profile_stmt->execute();
                $project_profile_result = $project_profile_stmt->get_result();
                if ($project_profile_result && $project_profile_result->num_rows > 0) {
                    $project_profile_row = $project_profile_result->fetch_assoc();
                    if (!empty($project_profile_row['profile_picture'])) {
                        $project_profile_picture_card = str_replace('../', '', ($project_profile_row['profile_picture']));
                    }
                    $project_name_card = ($project_profile_row['project_name']);
                }
                $project_profile_stmt->close();

                $listing_detail_url = "listings/" . ($row['slug']);
                $truncated_description = substr(($row['description']), 0, 150) . (strlen($row['description']) > 150 ? '...' : '');
                ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="listing-card" onclick="window.location.href='<?= $listing_detail_url; ?>'" style="cursor: pointer;">
                        <div class="listing-card-top-banner"></div>
                        <div class="listing-logo-container">
                            <img src="<?= $project_profile_picture_card; ?>" alt="<?= $project_name_card; ?> Logo" class="listing-logo">
                        </div>
                        <div class="listing-card-content-wrapper">
                            <h5 class="listing-title"><?= $row['job_title']; ?></h5>
                            <p class="listing-company-name"><?= $project_name_card; ?></p>
                            <p class="listing-location"><i class="bi bi-geo-alt me-1"></i><?= $row['location']; ?></p>
                            <p class="listing-salary">$<?= $row['salary']; ?></p>
                            <p class="listing-description"><?= nl2br($truncated_description); ?></p>
                            <div class="listing-badges">
                                <span class="job-type-badge"><i class="bi bi-briefcase me-1"></i><?= $row['job_type']; ?></span>
                                <span class="category-badge"><i class="bi bi-tag me-1"></i><?= $row['category']; ?></span>
                            </div>
                        </div>
                        <div class="listing-footer d-flex justify-content-between align-items-center">
                            <i class="bi bi-arrow-right-circle" style="color: #F97D37; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }

    if (!$hasListings) {
        echo '<div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-briefcase"></i>
                    <h4>No Listings Yet</h4>
                    <p class="text-muted">It appears that there are no listings open at this moment.<br>Come back later!</p>
                </div>
              </div>';
    }

    $conn->close();
    ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>