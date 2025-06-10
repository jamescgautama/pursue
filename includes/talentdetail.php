<?php
session_start();
require_once 'dbh.php';

if (!isset($_SESSION['project_id'])) {
    // If no project_id in session, fallback or redirect as needed
    header('Location: ../projects/discover.php');
    exit();
}

$project_id = $_SESSION['project_id'];

// Fetch project profile data for navbar
$stmt = $conn->prepare("SELECT * FROM project_profiles WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project_profile = $result->fetch_assoc();
$stmt->close();

$profile_picture_navbar = !empty($project_profile['profile_picture'])
    ? ($project_profile['profile_picture'])
    : '../images/resources/default.jpg';

$navbar_project_name = ($project_profile['project_name'] ?? 'Project');


// --- Your existing code for talent loading ---
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ../projects/discover.php');
    exit();
}
// Fetch talent by slug (existing)
$sql = "SELECT * FROM talent_profiles WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $talent = $result->fetch_assoc();
} else {
    echo "<p>Talent not found.</p>";
    exit();
}
$stmt->close();

// Fetch skills for this talent via join table
$sql_skills = "
    SELECT s.skill_name 
    FROM skills s
    JOIN talent_skills ts ON s.skill_id = ts.skill_id
    WHERE ts.talent_id = ?
";
$stmt_skills = $conn->prepare($sql_skills);
$stmt_skills->bind_param('i', $talent['talent_id']);
$stmt_skills->execute();
$result_skills = $stmt_skills->get_result();

$skills_array = [];
while ($row = $result_skills->fetch_assoc()) {
    $skills_array[] = $row['skill_name'];
}
$stmt_skills->close();

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo ($talent['talent_name']); ?> | Talent Details</title>
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

    /* Main content styling */
    .talent-detail-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .talent-detail-card {
        background: #111;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        border: 1px solid #333;
    }

    .talent-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #222;
    }

    .profile-pic {
        width: 120px;
        height: 120px;
        border-radius: 15px;
        object-fit: cover;
        margin-right: 25px;
        border: 3px solid #F97D37;
        flex-shrink: 0; /* Prevent shrinking on smaller screens */
    }

    .talent-info h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: bold;
        color: #fff;
    }

    .talent-info p {
        margin: 0;
        font-size: 1.1rem;
        color: #bbb;
    }

    .talent-section {
        margin-bottom: 2rem;
    }

    .talent-section h3 {
        font-size: 1.8rem;
        color: #F97D37;
        margin-bottom: 1rem;
        border-bottom: 2px solid #222;
        padding-bottom: 0.5rem;
    }

    .talent-section p {
        color: #ddd;
        line-height: 1.8;
        font-size: 1rem;
    }

    .meta-item {
        margin-bottom: 0.75rem;
    }

    .meta-item strong {
        color: #fff;
        font-weight: 600;
        display: inline-block;
        min-width: 80px; /* Align labels */
    }

    .meta-item a {
        color: #F97D37;
        text-decoration: none;
        transition: color 0.3s;
    }

    .meta-item a:hover {
        color: #fff;
        text-decoration: underline;
    }

    /* Badges for skills */
    .skills-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .skill-badge {
        background-color: #222;
        color: #F97D37;
        padding: 0.6rem 1.2rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        border: 1px solid #F97D37;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .talent-header {
            flex-direction: column;
            text-align: center;
        }
        .profile-pic {
            margin-right: 0;
            margin-bottom: 15px;
        }
        .talent-info h1 {
            font-size: 2rem;
        }
        .talent-info p {
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
      <img src="../../images/resources/pursuelogo.svg" alt="Pursue Logo" class="me-2">
      Pursue for Projects
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link" href="../listings.php">Listings</a>
        <a class="nav-link" href="../applications.php">Applications</a>
        <a class="nav-link" href="../create.php">Create</a>
        <a class="nav-link" href="../discover.php">Discover</a>
      </div>

      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img
            src="../<?= $profile_picture_navbar; ?>"
            alt="<?= $navbar_project_name; ?> Logo"
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
      </div>
    </div>
  </div>
</nav>

<!-- Page Content -->
<div class="talent-detail-container">
    <div class="talent-detail-card">
        <div class="talent-header">
            <?php if (!empty($talent['profile_picture'])): ?>
                <img src="<?php echo ('../../' . $talent['profile_picture']); ?>" alt="Profile Picture" class="profile-pic" />
            <?php else: ?>
                <img src="../../images/resources/default.jpg" alt="Default Profile Picture" class="profile-pic" />
            <?php endif; ?>
            <div class="talent-info">
                <h1><?php echo ($talent['talent_name']); ?></h1>
                <p><?php echo ($talent['location']); ?></p>
            </div>
        </div>

        <div class="talent-section">
            <h3>About Me</h3>
            <p><?php echo nl2br(($talent['bio'])); ?></p>
        </div>

        <div class="talent-section">
            <h3>Skills</h3>
            <div class="skills-badges">
                <?php
                if (!empty($skills_array)) {
    foreach ($skills_array as $skill) {
        echo '<span class="skill-badge">' . ($skill) . '</span>';
    }
} else {
    echo '<p style="font-color: white">No skills listed.</p>';
}
                ?>
            </div>
        </div>

        <div class="talent-section">
            <h3>Resources</h3>
            <div class="meta-items">
                <?php if (!empty($talent['cv_url'])): ?>
                    <p class="meta-item"><a href="<?php echo ('../../' .$talent['cv_url']); ?>" download>View CV <i class="bi bi-box-arrow-up-right"></i></a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
