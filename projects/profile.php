<?php
session_start();
require '../includes/dbh.php'; // Assuming dbh.php contains the $conn connection

if (!isset($_SESSION['project_id'])) {
    header("Location: login.php");
    exit;
}

$project_id = $_SESSION['project_id'];

// Fetch project profile for the main content
$stmt = $conn->prepare("SELECT * FROM project_profiles WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

$profilePicture = !empty($profile['profile_picture'])
    ? ($profile['profile_picture'])
    : '../images/resources/default.jpg'; // Path from project_profile.php to images

// --- For Navbar Dropdown ---
// Fetch project name and picture for the navbar dropdown
$navbar_project_name = ($profile['project_name'] ?? 'Project'); // Default name for navbar
$profile_picture_navbar = $profilePicture; // Use the same picture for the navbar
// --- End Navbar Dropdown fetch ---

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Profile | Pursue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Global & Body Styles */
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

        /* Navbar Styles */
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

        /* Profile content styles (Matching talent profile layout) */
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

        .profile-picture-main { /* Changed from .profile-picture to match talent profile */
            width: 120px;
            height: 120px;
            border-radius: 15px; /* Square with rounded corners */
            object-fit: cover;
            border: 3px solid #F97D37;
        }

        .profile-name { /* Re-used for project name */
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            color: #fff;
        }

        .profile-location { /* Re-used for location and URL */
            font-size: 1.2rem;
            color: #aaa;
            margin-bottom: 1.5rem;
        }

        .profile-bio { /* Re-used for description */
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #ddd;
        }

        /* Skill badge for industry */
        .skills-container {
            margin-bottom: 1.5rem;
        }

        .skill-badge {
            background-color: #F97D37;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0.25rem; /* Added margin for spacing between bubbles */
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

        .btn-success {
            background-color: #F97D37 !important;
            border: #F97D37 !important;
        }

        .btn-success:hover {
            background-color: #e6692e !important; /* Darker orange on hover */
            border-color: #e6692e !important;
        }

        .btn-primary {
            background-color: #F97D37;
            border-color: #F97D37;
        }

        .btn-primary:hover {
            background-color: #e6692e;
            border-color: #e6692e;
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
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider" style="border-color: #333;"></li>
            <li><a class="dropdown-item" href="../includes/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="profile-container">
    <h1 class="mb-4">Your Profile</h1>

    <div id="view-mode" class="profile-card">
        <div class="row align-items-start">
            <div class="col-md-3 text-center">
                <img src="<?= $profilePicture ?>"
                     alt="Project Profile Picture"
                     class="profile-picture-main mb-3">
            </div>

            <div class="col-md-9">
                <h2 class="profile-name">
                    <?= ($profile['project_name'] ?? 'Your Project Name') ?>
                </h2>

                <div class="profile-location">
                    <i class="bi bi-geo-alt me-1"></i>
                    <?= ($profile['location'] ?? 'Location not set') ?>
                    <?php if (!empty($profile['website_url'])): ?>
                        <br>
                        <i class="bi bi-link me-1"></i>
                        <a href="<?= ($profile['website_url']) ?>" target="_blank" class="text-decoration-none" style="color: #F97D37;">
                            <?= ($profile['website_url']) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="section-divider"></div>

                <div class="profile-bio"> <?= nl2br(($profile['description'] ?? 'No description provided yet.')) ?>
                </div>

                <div class="skills-container"> <h5 class="mb-3">Industry</h5>
                    <?php
                    $industries = [];
                    if (!empty($profile['industry'])) {
                        // Split the comma-separated string, trim whitespace from each, and filter out empty entries
                        $industries = array_filter(array_map('trim', explode(',', $profile['industry'])));
                    }

                    if (!empty($industries)):
                        foreach ($industries as $industry): ?>
                            <span class="skill-badge"><?= ($industry) ?></span>
                        <?php endforeach;
                    else: ?>
                        <span class="text-muted">No industry listed yet</span>
                    <?php endif; ?>
                </div>

                <div class="section-divider"></div>

                <div class="d-flex gap-3 align-items-center">
                    <button id="editBtn" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="edit-mode" class="edit-form">
        <h5 class="mb-4">
            <i class="bi bi-pencil-square me-2"></i>Edit Project Profile
        </h5>

        <form id="profileForm" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               name="project_name"
                               id="project_name"
                               required
                               value="<?= ($profile['project_name'] ?? '') ?>">
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
                        <label for="website_url" class="form-label">Website URL</label>
                        <input type="url"
                               class="form-control"
                               name="website_url"
                               id="website_url"
                               placeholder="https://example.com"
                               value="<?= ($profile['website_url'] ?? '') ?>">
                        <div class="form-text text-muted">e.g., https://yourproject.com</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control"
                                  name="description"
                                  id="description"
                                  rows="4"><?= ($profile['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="industry" class="form-label">Industry</label>
                        <input type="text"
                               class="form-control"
                               name="industry"
                               id="industry"
                               value="<?= ($profile['industry'] ?? '') ?>">
                        <div class="form-text text-muted">Separate multiple industries with commas (e.g., Software, AI, Marketing)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Profile Picture</label>
                        <div class="text-center">
                            <img src="<?= $profilePicture ?>"
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        saveStatus.removeClass('alert alert-success alert-danger').text(''); // Clear previous alerts

        $.ajax({
            url: '../includes/projectprofiler.php', // Correct path
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                let message = "Profile updated successfully!";
                try {
                    const resJson = JSON.parse(response);
                    if (resJson.status === 'success' && resJson.message) {
                        message = resJson.message;
                    } else if (resJson.status === 'error' && resJson.message) {
                        // If success handler receives an error JSON, show it as an error
                        saveStatus.addClass('alert alert-danger').html('<i class="bi bi-exclamation-triangle me-1"></i>Error: ' + resJson.message);
                        saveBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save Profile');
                        return; // Exit if it's an error handled in success
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || errorMessage;
                }

                saveStatus.addClass('alert alert-success').html('<i class="bi bi-check-circle me-1"></i>' + message);
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                let errorMessage = 'An unexpected error occurred.';
                try {
                    const errorJson = JSON.parse(xhr.responseText);
                    if (errorJson.message) {
                        errorMessage = errorJson.message;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || errorMessage;
                }
                saveStatus.addClass('alert alert-danger').html('<i class="bi bi-exclamation-triangle me-1"></i>Error: ' + errorMessage);
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