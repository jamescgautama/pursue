<?php
session_start();
require 'includes/dbh.php';

if (!isset($_SESSION['talent_id'])) {
    header("Location: login.php");
    exit;
}

$talent_id = $_SESSION['talent_id'];

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM talent_profiles WHERE talent_id = ?");
$stmt->bind_param("i", $talent_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Fetch skills for this talent
$skillsStmt = $conn->prepare("
    SELECT s.skill_name 
    FROM skills s 
    JOIN talent_skills ts ON s.skill_id = ts.skill_id 
    WHERE ts.talent_id = ?
    ORDER BY s.skill_name
");
$skillsStmt->bind_param("i", $talent_id);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();

$skills = [];
while ($skillRow = $skillsResult->fetch_assoc()) {
    $skills[] = $skillRow['skill_name'];
}
$skillsStmt->close();

// Convert skills array to comma-separated string for the form
$skillsString = implode(', ', $skills);

$profilePicture = !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'images/resources/default.jpg';

// Also fetch profile picture for navbar dropdown
$profile_picture_navbar = $profilePicture; // Same as main profile picture
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Pursue</title>
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

        /* Profile content styles */
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
            margin: 0;
            color: #fff;
        }

        .profile-location {
            font-size: 1.2rem;
            color: #aaa;
            margin-bottom: 1.5rem;
        }

        .profile-bio {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #ddd;
        }

        .skills-container {
            margin-top: 1rem;
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

        .edit-form {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 2rem;
            display: none;
        }

        .form-control {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
        }

        .form-control:focus {
            background-color: #222;
            border-color: #F97D37;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(249, 125, 55, 0.25);
        }

        .form-label {
            color: #fff;
            font-weight: 600;
        }

        .text-muted {
            color: #aaa !important;
        }

        .btn-outline-light {
            border-color: #444;
            color: #fff;
        }

        .btn-outline-light:hover {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-secondary {
            background-color: #444;
            border-color: #444;
        }

        .btn-secondary:hover {
            background-color: #555;
            border-color: #555;
        }

        .alert-success {
            background-color: #155724;
            border-color: #c3e6cb;
            color: #d4edda;
        }

        .alert-danger {
            background-color: #721c24;
            border-color: #f5c6cb;
            color: #f8d7da;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .section-divider {
            border-bottom: 1px solid #333;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>

<!-- Modern Navbar -->
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

      <!-- Profile Picture Dropdown -->
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

<!-- Profile Content -->
<div class="profile-container">
    <h1 class="mb-4">Your Profile</h1>

    <!-- View Mode -->
    <div id="view-mode" class="profile-card">
        <div class="row align-items-start">
            <div class="col-md-3 text-center">
                <img src="<?= ($profilePicture) ?>" 
                     alt="Profile Picture" 
                     class="profile-picture-main mb-3">
            </div>
            
            <div class="col-md-9">
                <h2 class="profile-name">
                    <?= ($profile['talent_name'] ?? 'Your Name') ?>
                </h2>
                
                <div class="profile-location">
                    <i class="bi bi-geo-alt me-1"></i>
                    <?= ($profile['location'] ?? 'Location not set') ?>
                </div>
                
                <div class="section-divider"></div>
                
                <div class="profile-bio">
                    <?= nl2br(($profile['bio'] ?? 'No bio provided yet.')) ?>
                </div>
                
                <div class="skills-container">
                    <h5 class="mb-3">Skills</h5>
                    <?php if (!empty($skills)): ?>
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-badge"><?= ($skill) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">No skills listed yet</span>
                    <?php endif; ?>
                </div>
                
                <div class="section-divider"></div>
                
                <div class="d-flex gap-3 align-items-center">
                    <?php if (!empty($profile['cv_url'])): ?>
                        <a href="<?= ($profile['cv_url']) ?>" 
                           target="_blank" 
                           class="btn btn-outline-light">
                            <i class="bi bi-file-earmark-pdf me-1"></i>View CV
                        </a>
                    <?php else: ?>
                        <span class="text-muted">No CV uploaded</span>
                    <?php endif; ?>
                    
                    <button id="editBtn" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Mode -->
    <div id="edit-mode" class="edit-form">
        <h5 class="mb-4">
            <i class="bi bi-pencil-square me-2"></i>Edit Profile
        </h5>
        
        <form id="profileForm" enctype="multipart/form-data" novalidate>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="talent_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="talent_name" 
                               id="talent_name" 
                               required 
                               value="<?= ($profile['talent_name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" 
                               class="form-control" 
                               name="location" 
                               id="location" 
                               value="<?= ($profile['location'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" 
                                  name="bio" 
                                  id="bio" 
                                  rows="4"><?= ($profile['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="skills" class="form-label">Skills (comma separated)</label>
                        <textarea class="form-control" 
                                  name="skills" 
                                  id="skills" 
                                  rows="3"
                                  placeholder="e.g. JavaScript, PHP, MySQL"><?= ($skillsString) ?></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Current Profile Picture</label>
                        <div class="text-center">
                            <img src="<?= ($profilePicture) ?>" 
                                 alt="Profile Picture" 
                                 class="profile-picture-main mb-2">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Change Profile Picture</label>
                        <input type="file" 
                               class="form-control" 
                               name="profile_picture" 
                               id="profile_picture" 
                               accept="image/*">
                        <div class="form-text text-muted">Accepted formats: JPG, PNG, GIF</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current CV</label>
                        <div class="mb-2">
                            <?php if (!empty($profile['cv_url'])): ?>
                                <a href="<?= ($profile['cv_url']) ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View Current CV
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No CV uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cv" class="form-label">Upload New CV</label>
                        <input type="file" 
                               class="form-control" 
                               name="cv" 
                               id="cv" 
                               accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">Accepted formats: PDF, DOC, DOCX</div>
                    </div>
                </div>
            </div>

            <hr style="border-color: #333;">
            
            <div class="d-flex gap-2">
                <button type="submit" id="saveBtn" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Save Profile
                </button>
                <button type="button" id="cancelEditBtn" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </button>
            </div>
            
            <div id="saveStatus" class="mt-3"></div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    $('#editBtn').click(function () {
        $('#view-mode').hide();
        $('#edit-mode').show();
    });

    $('#cancelEditBtn').click(function () {
        $('#edit-mode').hide();
        $('#view-mode').show();
    });

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const saveBtn = $('#saveBtn');
        const saveStatus = $('#saveStatus');
        
        saveBtn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise spin me-1"></i>Saving...');
        saveStatus.removeClass('alert-success alert-danger').text('');

        $.ajax({
            url: 'includes/talentprofiler.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                saveStatus.addClass('alert alert-success')
                         .html('<i class="bi bi-check-circle me-1"></i>Profile updated successfully!');
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                saveStatus.addClass('alert alert-danger')
                         .html('<i class="bi bi-exclamation-triangle me-1"></i>Error: ' + xhr.responseText);
            },
            complete: function () {
                saveBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save Profile');
            }
        });
    });
});
</script>

</body>
</html>