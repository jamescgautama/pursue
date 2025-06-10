<?php
session_start();
require_once '../includes/dbh.php';

// Check if project is logged in
if (!isset($_SESSION['project_id'])) {
    header("Location: login.php");
    exit();
}

$project_id = $_SESSION['project_id'];

// Fetch project profile for navbar
$stmt = $conn->prepare("SELECT profile_picture FROM project_profiles WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$profile_picture_navbar = !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'images/resources/default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Talents | Pursue</title>
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

        /* Discover content styles */
        .discover-container {
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

        .page-subtitle {
            color: #aaa;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Search section */
        .search-container {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .search-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
        }

        .search-input {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 25px;
            width: 100%;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #F97D37;
            box-shadow: 0 0 0 0.2rem rgba(249, 125, 55, 0.25);
        }

        .search-input::placeholder {
            color: #aaa;
        }

        /* Talent cards */
        .talent-card {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 0;
            transition: all 0.3s;
            overflow: hidden;
            cursor: pointer;
        }

        .talent-card:hover {
            border-color: #F97D37;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1);
        }

        .talent-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid #333;
        }

        .talent-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff;
            margin: 0;
        }

        .talent-content {
            padding: 1.5rem;
        }

        .talent-description {
            color: #ddd;
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Loading and alerts */
        .loading-container {
            text-align: center;
            padding: 3rem;
        }

        .spinner-border {
            color: #F97D37;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
        }

        .no-results i {
            font-size: 3rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .text-muted {
            color: #aaa !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<!-- Modern Navbar -->
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
        <a class="nav-link" href="discover.php">Discover</a>
      </div>

      <!-- Profile Picture Dropdown -->
      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img 
            src="<?php echo $profile_picture_navbar; ?>" 
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

<!-- Discover Content -->
<div class="discover-container">
    <div class="page-header">
        <h1 class="page-title">Discover Talents</h1>
    </div>

    <!-- Search Section -->
    <div class="search-container">
        <h5 class="search-title">
            <i class="bi bi-search me-2"></i>Search Talents
        </h5>
        <input type="text" id="search" class="search-input" placeholder="Search by name or skills...">
    </div>
    
    <!-- Loading indicator -->
    <div class="loading-container" id="loading" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Searching talents...</p>
    </div>
    
    <!-- Talents Grid -->
    <div class="row" id="results">
        <!-- Results will be loaded here -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    function loadRecords(query = '') {
        $('#loading').show();
        $('#results').hide();
        
        $.ajax({
            url: '../includes/discovertalents.php', 
            method: 'GET', 
            data: {query: query}, 
            success: function(response) {
                $('#results').html(response);
                $('#loading').hide();
                $('#results').show();
            },
            error: function() {
                $('#results').html('<div class="col-12"><div class="no-results"><i class="bi bi-exclamation-triangle"></i><h4>Error Loading Talents</h4><p class="text-muted">Please try again later.</p></div></div>');
                $('#loading').hide();
                $('#results').show();
            }
        });
    }
   
    loadRecords();
   
    $('#search').on('keyup', function() {
        let query = $(this).val();
        loadRecords(query);
    });
});
</script>

</body>
</html>