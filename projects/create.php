<?php
session_start();
require '../includes/dbh.php';

if (!isset($_SESSION['project_id'])) {
    header("Location: login.php");
    exit;
}

$project_id = $_SESSION['project_id'];

// Fetch project profile for navbar dropdown (similar to profile.php)
$stmt = $conn->prepare("SELECT * FROM project_profiles WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

$profilePicture = !empty($profile['profile_picture'])
    ? ($profile['profile_picture'])
    : '../images/resources/default.jpg';

$navbar_project_name = ($profile['project_name'] ?? 'Project');
$profile_picture_navbar = $profilePicture;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Listing | Pursue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
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

        /* Form Container */
        .form-container {
            max-width: 700px;
            margin: 3rem auto;
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 2rem;
        }

        /* Form Labels and Inputs */
        label, .form-label {
            color: #fff;
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        select {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            width: 100%;
            font-size: 1rem;
            resize: vertical;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: #F97D37;
            outline: none;
            box-shadow: 0 0 5px #F97D37;
        }

        textarea {
            min-height: 120px;
        }

        /* Button */
        button[type="submit"] {
            background-color: #F97D37;
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            font-size: 1.25rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #e6692e;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="../index.php">
      <img src="../images/resources/pursuelogo.svg" alt="Pursue Logo" class="me-2" />
      Pursue for Projects
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link" href="listings.php">Listings</a>
        <a class="nav-link" href="applications.php">Applications</a>
        <a class="nav-link active" href="create.php">Create</a>
        <a class="nav-link" href="discover.php">Discover</a>
      </div>

      <div class="d-flex ms-auto">
        <div class="dropdown profile-dropdown">
          <img
            src="<?= $profile_picture_navbar ?>"
            alt="<?= $navbar_project_name ?> Logo"
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

<div class="form-container">
    <h1 class="mb-4">Create Job Listing</h1>
    <form action="../includes/createlistings.php" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="job_title" class="form-label">Job Title</label>
                <input type="text" name="job_title" id="job_title" placeholder="Job Title" required>
            </div>

            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" required>
                    <option value="" disabled selected>Select category</option>
                    <option value="Technology">Technology</option>
                    <option value="Design">Design</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Sales">Sales</option>
                    <option value="Finance">Finance</option>
                    <option value="Human Resources">Human Resources</option>
                    <option value="Operations">Operations</option>
                    <option value="Customer Service">Customer Service</option>
                    <option value="Content & Writing">Content & Writing</option>
                    <option value="Legal">Legal</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Education">Education</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Research">Research</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" placeholder="Location">
            </div>

            <div class="col-md-6">
                <label for="salary" class="form-label">Salary</label>
                <input type="text" name="salary" id="salary" placeholder="e.g. $50,000 - $70,000">
            </div>

            <div class="col-md-6">
                <label for="job_type" class="form-label">Job Type</label>
                <select name="job_type" id="job_type" required>
                    <option value="" disabled selected>Select job type</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Contract">Contract</option>
                    <option value="Internship">Internship</option>
                    <option value="Temporary">Temporary</option>
                </select>
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Job Description</label>
                <textarea name="description" id="description" placeholder="Describe the job role and requirements..." required></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end mt-4">
                <button type="submit">Create Listing</button>
            </div>
        </div>
    </form>
</div>


<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>