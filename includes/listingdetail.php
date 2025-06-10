<?php
session_start();
require_once 'dbh.php'; // Ensure this returns a MySQLi OOP connection $conn

// --- Fetch talent profile picture if logged in (for navbar) ---
$profile_picture_navbar = '../images/resources/default.jpg'; // Default fallback path for talent
$is_talent_logged_in = false;
$navbar_talent_name = ''; // Initialize for consistency

if (isset($_SESSION['talent_id'])) {
    $is_talent_logged_in = true;
    $talent_id = $_SESSION['talent_id'];
    $profile_sql = "SELECT profile_picture, talent_name FROM talent_profiles WHERE talent_id = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $talent_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile_row = $profile_result->fetch_assoc();
        if (!empty($profile_row['profile_picture'])) {
            // Adjust path from includes/ to web root if stored as talent_images/pic.jpg
            $profile_picture_navbar = '../' . ($profile_row['profile_picture']);
        }
        $navbar_talent_name = ($profile_row['talent_name']);
    }
    $profile_stmt->close();
}

// --- Fetch listing details ---
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ../listings.php');
    exit();
}

// Select the listing AND the project_id to fetch project details later
$sql = "SELECT listing_id, job_title, description, location, salary, job_type, category, project_id FROM listings WHERE slug = ? AND approval = 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . ($conn->error)); // Basic error handling
}
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $job = $result->fetch_assoc();
    
    // --- Check if user has already applied (if logged in) ---
    $has_applied = false;
    if ($is_talent_logged_in) {
        $check_sql = "SELECT * FROM applications WHERE talent_id = ? AND listing_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $talent_id, $job['listing_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $has_applied = ($check_result->num_rows > 0);
        $check_stmt->close();
    }
    
    // --- Fetch Project Details for the Listing ---
    $project_name = "N/A";
    $project_profile_picture = '../../images/resources/default_project.jpg'; // Default project logo path

    $project_id_for_listing = $job['project_id'];
    if ($project_id_for_listing) {
        $project_sql = "SELECT project_name, profile_picture FROM project_profiles WHERE project_id = ?";
        $project_stmt = $conn->prepare($project_sql);
        if (!$project_stmt) {
             die('Project prepare failed: ' . ($conn->error));
        }
        $project_stmt->bind_param("i", $project_id_for_listing);
        $project_stmt->execute();
        $project_result = $project_stmt->get_result();

        if ($project_result && $project_result->num_rows > 0) {
            $project_data = $project_result->fetch_assoc();
            $project_name = ($project_data['project_name']);
if (!empty($project_data['profile_picture'])) {
    // Remove any '../' from DB path
    $clean_path = ltrim(str_replace('../', '', $project_data['profile_picture']), '/');
    // Prepend with the root URL base folder 'pursue' (adjust if your project folder is different)
    $project_profile_picture = '/pursue/' . $clean_path;
}
   
        }
        $project_stmt->close();
    }

} else {
    // If listing not found or not approved
    echo '<!DOCTYPE html>
          <html lang="en">
          <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>Listing Not Found | Pursue</title>
              <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
              <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
              <style>
                  body { background-color: #000; color: #fff; font-family: \'Helvetica Neue\', sans-serif; }
                  .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #111; border-radius: 15px; border: 1px solid #333; text-align: center; }
                  .container h1 { color: #F97D37; margin-bottom: 1rem; }
                  .container p { color: #bbb; margin-bottom: 1.5rem; }
                  .btn-primary { background-color: #F97D37 !important; border-color: #F97D37 !important; }
                  .btn-primary:hover { background-color: #e6692e !important; border-color: #e6692e !important; }
              </style>
          </head>
          <body>
              <div class="container">
                  <h1>Job Not Found</h1>
                  <p>The job listing you are looking for does not exist, is not approved, or has been removed.</p>
                  <a href="../listings.php" class="btn btn-primary"><i class="bi bi-arrow-left me-2"></i>Back to Listings</a>
              </div>
              <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
          </body>
          </html>';
    $conn->close();
    exit();
}
$stmt->close();
$conn->close(); // Close connection after all queries are done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($job['job_title']); ?> | Listing Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        
        .btn-primary {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-primary:hover {
            background-color: #e6692e;
            border-color: #e6692e;
        }

        /* Profile dropdown styles for talent */
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

        /* Main content styling (similar to talentdetail.php) */
        .listing-detail-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .listing-detail-card {
            background: #111;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border: 1px solid #333;
        }

        .listing-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #222;
        }

        .project-logo {
            width: 100px; /* Smaller than talent profile pic */
            height: 100px;
            border-radius: 15px; /* Square with rounded corners */
            object-fit: cover;
            margin-right: 25px;
            border: 3px solid #F97D37;
            flex-shrink: 0;
        }

        .listing-info h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
        }

        .listing-info p {
            margin: 0;
            font-size: 1.1rem;
            color: #bbb;
        }
        .listing-info .salary {
            font-size: 1.5rem;
            font-weight: bold;
            color: #F97D37;
            margin-top: 0.5rem;
        }


        .listing-section {
            margin-bottom: 2rem;
        }

        .listing-section h3 {
            font-size: 1.8rem;
            color: #F97D37;
            margin-bottom: 1rem;
            border-bottom: 2px solid #222;
            padding-bottom: 0.5rem;
        }

        .listing-section p {
            color: #ddd;
            line-height: 1.8;
            font-size: 1rem;
        }

        .meta-item {
            margin-bottom: 0.75rem;
            color: #ddd; /* General text color for meta items */
        }

        .meta-item strong {
            color: #fff;
            font-weight: 600;
            display: inline-block;
            min-width: 100px; /* Align labels */
        }

        /* Badges for job details */
        .detail-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .job-detail-badge {
            background-color: #222;
            color: #F97D37;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid #F97D37;
        }
        .job-detail-badge i {
            margin-right: 5px;
        }

        /* Apply button and login prompt */
        /* Apply button and login prompt */
.apply-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #222;
    text-align: center;
}

.btn-apply {
    background-color: #F97D37;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 1.2em;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #F97D37;
}

.btn-apply:hover:not(:disabled):not(.applied) {
    background-color: #e6692e;
    border-color: #e6692e;
    transform: translateY(-2px);
}

.btn-apply:disabled {
    cursor: not-allowed;
    transform: none;
}

/* Applied state - gray and non-interactive */
.btn-apply.applied {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: white !important;
    cursor: not-allowed !important;
}

.btn-apply.applied:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    transform: none !important;
}

.login-prompt {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #333;
    color: #ddd;
}

.login-prompt a {
    color: #F97D37;
    text-decoration: none;
    font-weight: bold;
}

.login-prompt a:hover {
    text-decoration: underline;
}

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .listing-header {
                flex-direction: column;
                text-align: center;
            }
            .project-logo {
                margin-right: 0;
                margin-bottom: 15px;
            }
            .listing-info h1 {
                font-size: 2rem;
            }
            .listing-info p {
                font-size: 1rem;
            }
            .meta-item strong {
                min-width: auto;
                display: block;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="../listings.php">
            <img src="../images/resources/pursuelogo.svg" alt="Pursue Logo" class="me-2">
            Pursue
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link active" aria-current="page" href="../listings.php">Listings</a>
                <a class="nav-link" href="../applications.php">Applications</a>
                <a class="nav-link" href="../discover.php">Discover</a>
            </div>

            <div class="d-flex ms-auto">
                <?php if ($is_talent_logged_in): ?>
                    <div class="dropdown profile-dropdown">
                        <img 
                            src="<?= $profile_picture_navbar;?>" 
                            alt="<?= $navbar_talent_name; ?> Profile" 
                            class="profile-picture-nav" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            id="profileDropdown"
                        >
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider" style="border-color: #333;"></li>
                            <li><a class="dropdown-item" href="../includes/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="../signup.php" class="btn btn-success">Signup</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="listing-detail-container">
    <div class="listing-detail-card">
        <div class="mb-4">
            <a href="../listings.php" class="btn btn-primary"><i class="bi bi-arrow-left me-2"></i>Back to Listings</a>
        </div>
        
        <div class="listing-header">
            <img src="<?=$project_profile_picture; ?>" alt="<?= $project_name; ?> Logo" class="project-logo" />
            <div class="listing-info">
                <h1><?php echo ($job['job_title']); ?></h1>
                <p class="mb-1"><?= $project_name; ?></p>
                <p class="salary">$<?php echo ($job['salary']); ?></p>
            </div>
        </div>

        <div class="listing-section">
            <h3>Job Overview</h3>
            <div class="detail-badges">
                <span class="job-detail-badge"><i class="bi bi-geo-alt"></i><?= ($job['location']); ?></span>
                <span class="job-detail-badge"><i class="bi bi-briefcase"></i><?= ($job['job_type']); ?></span>
                <span class="job-detail-badge"><i class="bi bi-tag"></i><?= ($job['category']); ?></span>
            </div>
        </div>

        <div class="listing-section">
            <h3>Description</h3>
            <p><?php echo nl2br(($job['description'])); ?></p>
        </div>

        <div class="apply-section">
            <?php if ($is_talent_logged_in): ?>
                <?php if ($has_applied): ?>
                    <button class="btn-apply applied" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i>Application Submitted
                    </button>
                <?php else: ?>
                    <button id="applyBtn" onclick="applyForJob(<?php echo $job['listing_id']; ?>)" class="btn-apply">
                        <i class="bi bi-send-fill me-2"></i>Apply for This Job
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="login-prompt">
                    <p class="mb-0">Please <a href="../login.php">log in</a> or <a href="../signup.php">sign up</a> to apply for this job.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
function applyForJob(listing_id) {
    const applyBtn = document.getElementById('applyBtn');
    
    // Debug: Log the current page path
    console.log('Current page path:', window.location.pathname);
    console.log('Listing ID:', listing_id);
    
    // Disable button immediately to prevent double-clicks
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Applying...';
    
    // Try multiple possible paths - this will help identify the correct one
    const possiblePaths = [
        '../includes/applytolisting.php',  // Most likely - go up one directory
        'includes/applytolisting.php',     // Current directory
        '/pursue/includes/applytolisting.php', // Absolute path (adjust 'pursue' to your project folder)
        '../../includes/applytolisting.php' // Go up two directories
    ];
    
    // Let's try the most likely path first
    const ajaxUrl = '../includes/applytolisting.php';
    console.log('Attempting to call:', ajaxUrl);
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: { listing_id: listing_id },
        success: function(response) {
            console.log('Success response:', response);
            
            if (response.includes("successfully") || response.includes("already applied")) {
                applyBtn.style.transition = 'all 0.3s ease';
                applyBtn.style.backgroundColor = '#6c757d';
                applyBtn.style.borderColor = '#6c757d';
                applyBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Application Submitted';
                applyBtn.classList.add('applied');
                applyBtn.disabled = true;
                applyBtn.style.cursor = 'not-allowed';
            } else {
                console.log('Unexpected response:', response);
                alert('Server Response: ' + response);
                
                applyBtn.style.transition = 'all 0.3s ease';
                applyBtn.style.backgroundColor = '#dc3545';
                applyBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Try Again';
                
                console.log('Full response text:', response);
                
                setTimeout(() => {
                    applyBtn.disabled = false;
                    applyBtn.style.backgroundColor = '#F97D37';
                    applyBtn.style.borderColor = '#F97D37';
                    applyBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Apply for This Job';
                }, 2000);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error Details:');
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);
            console.log('Status Code:', xhr.status);
            console.log('Ready State:', xhr.readyState);
            
            applyBtn.style.transition = 'all 0.3s ease';
            applyBtn.style.backgroundColor = '#dc3545';
            
            if (xhr.status === 404) {
                applyBtn.innerHTML = '<i class="bi bi-file-earmark-x me-2"></i>File Not Found';
                console.log('404 Error: File not found at path:', ajaxUrl);
                console.log('Try these alternative paths in order:');
                possiblePaths.forEach((path, index) => {
                    console.log(`${index + 1}. ${path}`);
                });
            } else if (xhr.status === 500) {
                applyBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Server Error';
                console.log('500 Error: Check your PHP error logs');
            } else if (xhr.status === 0) {
                applyBtn.innerHTML = '<i class="bi bi-wifi-off me-2"></i>Connection Error';
                console.log('Connection Error: Possible CORS issue or file not found');
            } else {
                applyBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Error ' + xhr.status;
            }
            
            setTimeout(() => {
                applyBtn.disabled = false;
                applyBtn.style.backgroundColor = '#F97D37';
                applyBtn.style.borderColor = '#F97D37';
                applyBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Apply for This Job';
            }, 3000);
        }
    });
}</script>
</body>
</html>