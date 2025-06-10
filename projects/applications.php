<?php
session_start();
require_once '../includes/dbh.php'; // Corrected path to dbh.php

// Check if project user is logged in
if (!isset($_SESSION['project_id'])) {
    header("Location: ../login.php"); // Assuming login.php is in the root or appropriate path
    exit();
}

// Fetch project user profile picture and name for the navbar
$profile_picture_navbar = '../images/resources/default.jpg'; // Default fallback path relative to applications.php
$navbar_project_name = 'Project'; // Default name for navbar

if (isset($_SESSION['project_id'])) {
    $project_id = $_SESSION['project_id'];
    $profile_sql = "SELECT profile_picture, project_name FROM project_profiles WHERE project_id = ?"; // Added project_name
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $project_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile_row = $profile_result->fetch_assoc();
        if (!empty($profile_row['profile_picture'])) {
            $profile_picture_navbar = $profile_row['profile_picture'];
        }
        $navbar_project_name = ($profile_row['project_name']); // Set for navbar alt text
    }
    $profile_stmt->close();
}

// Ensure the main $conn connection is not closed prematurely if used later
// $conn->close(); // Only close if no other database operations will occur on this page.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

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

        /* Profile dropdown styles from listings.php */
        .profile-dropdown {
            position: relative;
        }

        .profile-picture-nav { /* Changed class from .profile-picture to .profile-picture-nav for clarity with existing css */
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #F97D37;
            object-fit: cover;
            transition: border-color 0.3s;
        }

        .profile-picture-nav:hover { /* Changed class for consistency */
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
        .applications-container {
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

        /* Filter buttons */
        .filter-container {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-btn {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 25px; /* More rounded */
            transition: all 0.3s;
            cursor: pointer;
            font-weight: 500;
        }

        .filter-btn:hover {
            background-color: #F97D37;
            border-color: #F97D37;
            color: #fff;
        }

        .filter-btn.active {
            background-color: #F97D37;
            border-color: #F97D37;
            color: #fff;
        }

        /* Application cards */
        .application-card {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px; /* More rounded corners */
            padding: 0;
            transition: all 0.3s;
            overflow: hidden;
        }

        .application-card:hover {
            border-color: #F97D37; /* Accent color on hover */
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1); /* Subtle glow */
        }

        .card-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid #333;
            background-color: #1a1a1a; /* Darker header */
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff; /* White title */
            margin: 0;
        }

        .card-location {
            color: #aaa; /* Lighter grey for location */
            font-size: 1rem;
            margin: 0;
        }

        .card-content {
            padding: 1.5rem;
            color: #ddd; /* Light grey for general text */
        }

        .card-description {
            color: #ddd;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .card-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .job-badge {
            background-color: #333;
            color: #fff;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .category-badge {
            background-color: #222;
            color: #F97D37; /* Accent color for category */
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #F97D37; /* Border matching accent */
        }

        .card-details p {
            margin-bottom: 0.5rem;
            color: #ddd;
            font-size: 0.95rem;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #333;
            background-color: #0a0a0a; /* Even darker footer */
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        /* Specific badge colors (match Bootstrap 5) */
        .badge.bg-success {
            background-color: #198754 !important; /* Brighter green */
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important; /* Dark text on warning */
        }

        /* Loading indicator */
        .loading-container {
            text-align: center;
            padding: 3rem;
        }

        .spinner-border {
            color: #F97D37; /* Match accent color */
        }

        .text-muted {
            color: #aaa !important;
        }

        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050; /* Ensure it's above other elements */
        }
        .toast {
            background-color: #1a1a1a; /* Dark background */
            border: 1px solid #333;
            color: #fff;
            border-radius: 8px;
        }
        .toast-header {
            background-color: #1a1a1a;
            border-bottom: 1px solid #333;
            color: #F97D37;
        }
        .toast-body {
            color: #ccc;
        }
        .toast-header .btn-close { /* For Bootstrap 5 close button */
            color: #fff; /* Ensure close button is visible */
            filter: invert(1); /* Invert color if needed to make it white */
            opacity: 1; /* Ensure it's not faded */
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
            .filter-buttons {
                justify-content: center;
            }
            .filter-btn {
                flex: 1;
                min-width: 120px;
                text-align: center;
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
        <a class="nav-link" href="listings.php">Listings</a>
        <a class="nav-link" href="applications.php">Applications</a>
        <a class="nav-link" href="create.php">Create</a>
        <a class="nav-link" href="discover.php">Discover</a>

      </div>

      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img
            src="<?= $profile_picture_navbar; ?>"
            alt="<?= $navbar_project_name; ?> Logo"
            class="profile-picture-nav"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            id="profileDropdown"
          >
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="project_profile.php">Profile</a></li>
            <li><hr class="dropdown-divider" style="border-color: #333;"></li>
            <li><a class="dropdown-item" href="../includes/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

    <div class="applications-container">
        <div class="page-header">
            <h1 class="page-title">Manage Applications</h1>
        </div>
        
        <div class="filter-container">
            <h5 class="filter-title">
                <i class="bi bi-funnel me-2"></i>Filter Applications
            </h5>
            <div class="filter-buttons">
                <button type="button" class="filter-btn active" data-status="All">
                    All Applications
                </button>
                <button type="button" class="filter-btn" data-status="Pending">
                    <i class="bi bi-clock me-1"></i>Pending
                </button>
                <button type="button" class="filter-btn" data-status="Accepted">
                    <i class="bi bi-check-circle me-1"></i>Accepted
                </button>
                <button type="button" class="filter-btn" data-status="Rejected">
                    <i class="bi bi-x-circle me-1"></i>Rejected
                </button>
            </div>
        </div>
        
        <div class="loading-container" id="loading" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading applications...</p>
        </div>
        
        <div class="row" id="applications-container-grid"> </div>
    </div>

    <div class="toast-container">
        <div id="alertToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Load initial applications
        loadApplications('get_initial');
        
        // Filter button click handler
        $('.filter-btn').click(function() { // Changed selector
            let status = $(this).data('status');
            
            // Update active button
            $('.filter-btn').removeClass('active'); // Changed selector
            $(this).addClass('active');
            
            // Load filtered applications
            loadApplications('filter', status);
        });
        
        // Approve/Decline button handlers (using event delegation)
        $(document).on('click', '.approve-btn, .decline-btn', function(e) {
            e.preventDefault();
            
            let button = $(this);
            let applicationId = button.data('id');
            let action = button.hasClass('approve-btn') ? 'approve' : 'decline';
            
            // Disable buttons to prevent double-click
            let buttonContainer = button.closest('.action-buttons');
            buttonContainer.find('button').prop('disabled', true);
            
            // AJAX request for approval/decline
            $.ajax({
                url: '../includes/applicationstatuser.php', // Path remains correct
                type: 'POST',
                data: {
                    action: action,
                    application_id: applicationId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        
                        // Update the UI dynamically for the specific card
                        let currentCardFooter = button.closest('.card-footer');
                        let statusSpan = currentCardFooter.find('.status-badge'); // Changed class
                        
                        if (action === 'approve') {
                            statusSpan.removeClass('bg-warning text-dark text-danger').addClass('bg-success').html('<i class="bi bi-check-circle me-1"></i>Accepted');
                            buttonContainer.html('<span class="badge bg-success">Accepted</span>'); // Changed class
                        } else {
                            statusSpan.removeClass('bg-warning text-dark text-success').addClass('bg-danger').html('<i class="bi bi-x-circle me-1"></i>Rejected');
                            buttonContainer.html('<span class="badge bg-danger">Rejected</span>'); // Changed class
                        }
                    } else {
                        showToast(response.message, 'error');
                        // Re-enable buttons on error
                        buttonContainer.find('button').prop('disabled', false);
                    }
                },
                error: function() {
                    showToast('Error processing request. Please try again.', 'error');
                    // Re-enable buttons on error
                    buttonContainer.find('button').prop('disabled', false);
                }
            });
        });
        
        // Function to load applications
        function loadApplications(action, status = null) {
            $('#loading').show();
            $('#applications-container-grid').hide(); // Changed ID
            
            let postData = { action: action };
            if (status) {
                postData.status = status;
            }
            
            $.ajax({
                url: '../includes/applicationstatuser.php', // Path remains correct
                type: 'POST',
                data: postData,
                success: function(response) {
                    $('#applications-container-grid').html(response); // Changed ID
                    $('#loading').hide();
                    $('#applications-container-grid').show(); // Changed ID
                },
                error: function() {
                    showToast('Error loading applications. Please try again.', 'error');
                    $('#loading').hide();
                    $('#applications-container-grid').show(); // Changed ID
                }
            });
        }
        
        // Toast notification function (updated for Bootstrap 5)
        function showToast(message, type) {
            let toastElement = $('#alertToast');
            let toastBody = $('#toastMessage');
            
            // Remove previous color classes from toast body if any
            toastBody.removeClass('text-success text-danger');
            
            // Remove previous background classes from toast element
            toastElement.removeClass('bg-success bg-danger');

            if (type === 'success') {
                toastElement.addClass('bg-success'); // Bootstrap 5 background color class
                toastBody.text(message);
            } else {
                toastElement.addClass('bg-danger'); // Bootstrap 5 background color class
                toastBody.text(message);
            }
            
            let bsToast = new bootstrap.Toast(toastElement);
            bsToast.show();
        }
    });
    </script>
</body>
</html>