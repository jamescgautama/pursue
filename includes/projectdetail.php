<?php
session_start();
require_once 'dbh.php';

$profile_picture_navbar = '../images/resources/default.jpg';
$is_talent_logged_in = false;
$navbar_talent_name = '';

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
            $profile_picture_navbar = '../' . $profile_row['profile_picture'];
        }
        $navbar_talent_name = $profile_row['talent_name'];
    }
    $profile_stmt->close();
}

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ../discover.php');
    exit();
}

$sql = "SELECT pp.project_id, pp.project_name, pp.profile_picture, pp.description, pp.location, pp.website_url 
        FROM project_profiles pp 
        WHERE pp.slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $project = $result->fetch_assoc();
    $project_id = $project['project_id'];

    $project_name = $project['project_name'];
    $project_description = nl2br($project['description']);
    $project_location = $project['location'];
    $project_website = !empty($project['website_url']) ? $project['website_url'] : null;

    $project_profile_picture = '../../images/resources/default_project.jpg';
    if (!empty($project['profile_picture'])) {
        $clean_path = ltrim(str_replace('../', '', $project['profile_picture']), '/');
        $project_profile_picture = '/pursue/' . $clean_path;
    }

    $industry_sql = "SELECT i.industry_name 
                     FROM project_industries pi 
                     JOIN industries i ON pi.industry_id = i.industry_id 
                     WHERE pi.project_id = ?";
    $industry_stmt = $conn->prepare($industry_sql);
    $industry_stmt->bind_param('i', $project_id);
    $industry_stmt->execute();
    $industry_result = $industry_stmt->get_result();
    
    $industries = [];
    while ($industry_row = $industry_result->fetch_assoc()) {
        $industries[] = $industry_row['industry_name'];
    }
    $industry_stmt->close();

} else {
    echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Project Not Found | Pursue</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background-color: #000; color: #fff; text-align: center; padding: 4rem; font-family: sans-serif; }
                .container { background: #111; padding: 2rem; border-radius: 10px; border: 1px solid #333; }
                a.btn { background-color: #F97D37; border: none; }
                a.btn:hover { background-color: #e6692e; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Project Not Found</h1>
                <a href="../discover.php" class="btn btn-primary">Back to Discover</a>
            </div>
        </body>
        </html>';
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $project_name; ?> | Project Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
body {
    background-color: #000;
    color: #fff;
    font-family: 'Helvetica Neue', sans-serif;
    padding: 0;
    margin: 0;
}

.profile-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-card {
    background-color: #111;
    border: 1px solid #333;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.profile-picture-main {
    width: 120px;
    height: 120px;
    border-radius: 15px;
    object-fit: cover;
    border: 3px solid #F97D37;
}

.profile-name {
    font-size: 2rem;
    font-weight: bold;
    color: #fff;
}

.profile-location {
    font-size: 1.2rem;
    color: #aaa;
    margin-top: 0.5rem;
}

.profile-bio {
    font-size: 1rem;
    line-height: 1.6;
    color: #ddd;
    margin-top: 0.5rem;
}

.skills-container {
    margin-top: 0.5rem;
}

.skill-badge {
    background-color: #F97D37;
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin: 0.25rem;
    display: inline-block;
}

.section-divider {
    border-bottom: 1px solid #333;
    margin: 1.5rem 0;
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
        
        .btn-primary {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-primary:hover {
            background-color: #e6692e;
            border-color: #e6692e;
        }

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


    <div class="profile-container">
        <div class="profile-card">
            <div class="row align-items-start">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <?php if (!empty($project['profile_picture'])): ?>
                        <img src="<?= $project['profile_picture'] ?>" alt="Project Logo" class="profile-picture-main">
                    <?php endif; ?>
                </div>

                <div class="col-md-9">
                    <h2 class="profile-name">
                        <?= $project['project_name'] ?>
                    </h2>

                    <div class="profile-location">
                        <?php if (!empty($project['location'])): ?>
                            <i class="bi bi-geo-alt me-1"></i><?= $project['location'] ?><br>
                        <?php endif; ?>

                        <?php if (!empty($project['website_url'])): ?>
                            <i class="bi bi-link me-1"></i>
                            <a href="<?= $project['website_url'] ?>" target="_blank" class="text-decoration-none" style="color: #F97D37;">
                                <?= $project['website_url'] ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="section-divider"></div>

                    <div class="profile-bio">
                        <?= nl2br($project['description'] ?? 'No description provided yet.') ?>
                    </div>

                    <div class="section-divider"></div>

                    <h5 class="mb-2">Industry</h5>
                    <div class="skills-container">
                        <?php if (!empty($industries)): ?>
                            <?php foreach ($industries as $industry): ?>
                                <span class="skill-badge"><?= $industry ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>