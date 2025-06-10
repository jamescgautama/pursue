<?php
session_start();
require_once 'includes/dbh.php';

// Check if talent is logged in
if (!isset($_SESSION['talent_id'])) {
    header("Location: login.php");
    exit();
}

$talent_id = $_SESSION['talent_id'];

// Fetch user profile for navbar
$stmt = $conn->prepare("SELECT profile_picture FROM talent_profiles WHERE talent_id = ?");
$stmt->bind_param("i", $talent_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$profile_picture_navbar = !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'images/resources/default.jpg';

if (isset($_POST['action']) && $_POST['action'] == 'filter') {
    $status_filter = $_POST['status'];
    
    $sql = "SELECT a.*, l.job_title, l.location, l.salary, l.job_type, l.category, l.description, l.slug 
            FROM applications a 
            JOIN listings l ON a.listing_id = l.listing_id 
            WHERE a.talent_id = ?";
    
    if ($status_filter != 'All') {
        $sql .= " AND a.status = ?";
    }
    
    $sql .= " ORDER BY a.application_date DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($status_filter != 'All') {
        $stmt->bind_param("is", $talent_id, $status_filter);
    } else {
        $stmt->bind_param("i", $talent_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Return HTML for applications
    if (count($applications) > 0) {
        foreach ($applications as $app) {
            echo generateApplicationCard($app);
        }
    } else {
echo '<div class="col-12">
    <div class="no-applications-container">
        <i class="bi bi-inbox no-applications-icon"></i>
        <h4 class="no-applications-title">No Applications Yet</h4>
        <p class="no-applications-text">You haven\'t applied to any listings yet.</p>
    </div>
</div>';

    }
    exit();
}

$sql = "SELECT a.*, l.job_title, l.location, l.salary, l.job_type, l.category, l.description, l.slug 
        FROM applications a 
        JOIN listings l ON a.listing_id = l.listing_id 
        WHERE a.talent_id = ? 
        ORDER BY a.application_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $talent_id);
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function generateApplicationCard($app) {
    $status_class = '';
    $status_badge_class = '';
    switch($app['status']) {
        case 'Accepted':
            $status_class = 'text-success';
            $status_badge_class = 'bg-success';
            break;
        case 'Declined':
            $status_class = 'text-danger';
            $status_badge_class = 'bg-danger';
            break;
        case 'Pending':
            $status_class = 'text-warning';
            $status_badge_class = 'bg-warning text-dark';
            break;
    }
    
    $salary_display = $app['salary'] ? '$' . number_format($app['salary']) : 'Not specified';
    
    return '
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="application-card h-100" onclick="window.location.href=\'listings/' . ($app['slug']) . '\'" style="cursor: pointer;">
            <div class="card-header">
                <h5 class="card-title mb-1">' . ($app['job_title']) . '</h5>
                <p class="card-location mb-2">
                    <i class="bi bi-geo-alt me-1"></i>' . ($app['location']) . '
                </p>
            </div>
            <div class="card-content">
                <p class="card-description">' . (substr($app['description'], 0, 120)) . '...</p>
                <div class="card-badges mb-3">
                    <span class="job-badge">' . ($app['job_type']) . '</span>
                    <span class="category-badge">' . ($app['category']) . '</span>
                </div>
                <div class="card-details">
                    <p class="salary-info"><strong>Salary:</strong> ' . $salary_display . '</p>
                    <p class="apply-date"><strong>Applied:</strong> ' . date('M j, Y', strtotime($app['application_date'])) . '</p>
                </div>
            </div>
            <div class="card-footer">
                <span class="status-badge ' . $status_badge_class . '">
                    <i class="bi bi-circle-fill me-1"></i>' . ($app['status']) . '
                </span>
            </div>
        </div>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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

        /* Applications content styles */
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
            border-radius: 25px;
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
            border-radius: 15px;
            padding: 0;
            transition: all 0.3s;
            overflow: hidden;
        }

        .application-card:hover {
            border-color: #F97D37;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1);
        }

        .card-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid #333;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff;
            margin: 0;
        }

        .card-location {
            color: #aaa;
            font-size: 1rem;
            margin: 0;
        }

        .card-content {
            padding: 1.5rem;
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
            color: #F97D37;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #F97D37;
        }

        .card-details p {
            margin-bottom: 0.5rem;
            color: #ddd;
            font-size: 0.95rem;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #333;
            background-color: #0a0a0a;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
        }

        /* Loading and alerts */
        .loading-container {
            text-align: center;
            padding: 3rem;
        }

        .spinner-border {
            color: #F97D37;
        }

        .alert-info {
            background-color: #155724;
            border-color: #c3e6cb;
            color: #d4edda;
        }

        .text-muted {
            color: #aaa !important;
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

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }


.no-applications-container {
    border: 1px solid #333;
    background-color: #111;
    border-radius: 15px;
    padding: 4rem 0; /* generous vertical padding */
    text-align: center;
    margin-top: 2rem; /* space from badges or above content */
}

.no-applications-icon {
    font-size: 3rem; /* bigger icon */
    color: #666;
    margin-bottom: 1rem;
}

.no-applications-title {
    font-size: 1.5rem; /* bigger font */
    font-weight: 600;
    color: #fff;
    margin-bottom: 0.75rem;
}

.no-applications-text {
    font-size: 1.1rem; /* larger text */
    color: #aaa; /* muted */
    margin-bottom: 0;
    max-width: 450px;
    margin-left: auto;
    margin-right: auto;
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
        <a class="nav-link" href="listings.php">Listings</a>
        <a class="nav-link" href="applications.php">Applications</a>
        <a class="nav-link" href="discover.php">Discover</a>
      </div>

      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img 
            src="<?php echo ($profile_picture_navbar); ?>" 
            alt="Profile" 
            class="profile-picture-nav" 
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
      </div>
    </div>
  </div>
</nav>

<div class="applications-container">
    <div class="page-header">
        <h1 class="page-title">My Applications</h1>
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
            <button type="button" class="filter-btn" data-status="Declined">
                <i class="bi bi-x-circle me-1"></i>Declined
            </button>
        </div>
    </div>
    
    <!-- Loading indicator -->
    <div class="loading-container" id="loading" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading applications...</p>
    </div>
    
    <!-- Applications Grid -->
    <div class="row" id="applications-container">
        <?php
        if (count($applications) > 0) {
            foreach ($applications as $app) {
                echo generateApplicationCard($app);
            }
        } else {
echo '<div class="col-12">
    <div class="no-applications-container">
        <i class="bi bi-inbox no-applications-icon"></i>
        <h4 class="no-applications-title">No Applications Yet</h4>
        <p class="no-applications-text">You haven\'t applied to any listings yet.</p>
    </div>
</div>';




        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Filter button click handler
    $('.filter-btn').click(function() {
        var status = $(this).data('status');
        
        // Update active button
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        // Show loading
        $('#loading').show();
        $('#applications-container').hide();
        
        // AJAX request
        $.ajax({
            url: 'applications.php',
            type: 'POST',
            data: {
                action: 'filter',
                status: status
            },
            success: function(response) {
                $('#applications-container').html(response);
                $('#loading').hide();
                $('#applications-container').show();
            },
            error: function() {
                alert('Error loading applications. Please try again.');
                $('#loading').hide();
                $('#applications-container').show();
            }
        });
    });
});
</script>

</body>
</html>