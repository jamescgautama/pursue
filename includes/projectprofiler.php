<?php
session_start();
require 'dbh.php';

if (!isset($_SESSION['project_id'])) {
    header("Location: ../login.php");
}

$project_id = $_SESSION['project_id'];

function createSlug($title, $id) {
    $string = strtolower($title);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string); // Replace non-alphanumeric with dashes
    $string = preg_replace('/-+/', '-', $string);         // Merge multiple dashes
    $string = trim($string, '-');                          // Trim dashes from ends
    return $id . '-' . $string;
}

function uploadFile($file, $allowedTypes, $uploadDir) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $fileType = mime_content_type($fileTmpPath);

        if (!in_array($fileType, $allowedTypes)) {
            return ['error' => "Invalid file type: $fileType"];
        }

        $newFileName = uniqid() . "-" . preg_replace("/[^a-zA-Z0-9.]/", "-", $fileName);
        $destPath = $uploadDir . $newFileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return ['path' => $destPath];
        } else {
            return ['error' => "Error moving uploaded file."];
        }
    }
    return ['error' => "No file uploaded or upload error."];
}

function handleIndustries($conn, $project_id, $industries_string) {
    // First, remove all existing industry associations for this project
    $deleteStmt = $conn->prepare("DELETE FROM project_industries WHERE project_id = ?");
    $deleteStmt->bind_param("i", $project_id);
    $deleteStmt->execute();
    $deleteStmt->close();

    if (empty(trim($industries_string))) {
        return true; // No industries to process
    }

    // Split the comma-separated string and clean up each industry name
    $industry_names = array_map('trim', explode(',', $industries_string));
    $industry_names = array_filter($industry_names); // Remove empty values

    foreach ($industry_names as $industry_name) {
        if (empty($industry_name)) continue;

        // Check if industry exists
        $checkStmt = $conn->prepare("SELECT industry_id FROM industries WHERE LOWER(industry_name) = LOWER(?)");
        $checkStmt->bind_param("s", $industry_name);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Industry exists, get its ID
            $row = $result->fetch_assoc();
            $industry_id = $row['industry_id'];
        } else {
            // Industry doesn't exist, create it
            $insertStmt = $conn->prepare("INSERT INTO industries (industry_name) VALUES (?)");
            $insertStmt->bind_param("s", $industry_name);
            $insertStmt->execute();
            $industry_id = $conn->insert_id;
            $insertStmt->close();
        }
        $checkStmt->close();

        // Link project to industry via project_industries table
        $linkStmt = $conn->prepare("INSERT INTO project_industries (project_id, industry_id) VALUES (?, ?)");
        $linkStmt->bind_param("ii", $project_id, $industry_id);
        $linkStmt->execute();
        $linkStmt->close();
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $website_url = trim($_POST['website_url'] ?? '');
    $industries_input = trim($_POST['industries'] ?? ''); // Changed from 'industry' to 'industries'
    $location = trim($_POST['location'] ?? '');

    if (empty($project_name)) {
        echo "project name is required.";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM project_profiles WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingProfile = $result->fetch_assoc();

    $profile_picture_path = $existingProfile['profile_picture'] ?? null;

    // Generate and insert slug
    $slug = createSlug($project_name, $project_id);
    $slugStmt = $conn->prepare("UPDATE project_profiles SET slug = ? WHERE project_id = ?");
    if ($slugStmt) {
        $slugStmt->bind_param("si", $slug, $project_id);
        $slugStmt->execute();
        $slugStmt->close();
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = __DIR__ . '/../images/project_profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileType = mime_content_type($fileTmpPath);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            die("Invalid profile picture file type.");
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = $project_id . '.' . $ext;

        $destPath = $uploadDir . $newFileName;

        $webPath = '../images/project_profiles/' . $newFileName;

        if (!empty($existingProfile['profile_picture']) &&
            $existingProfile['profile_picture'] !== '../images/resources/default.jpg' &&
            file_exists(__DIR__ . '/../' . $existingProfile['profile_picture'])) {
            unlink(__DIR__ . '/../' . $existingProfile['profile_picture']);
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $profile_picture_path = $webPath;
        } else {
            die("Error uploading profile picture.");
        }
    }

    // Handle industries before updating/inserting profile
    if (!handleIndustries($conn, $project_id, $industries_input)) {
        echo "Failed to process industries.";
        exit;
    }

    if ($existingProfile) {
        // Remove industry from UPDATE query since it's now handled separately
        $stmt = $conn->prepare("UPDATE project_profiles SET project_name = ?, description = ?, website_url = ?, location = ?, profile_picture = ? WHERE project_id = ?");
        $stmt->bind_param("sssssi", $project_name, $description, $website_url, $location, $profile_picture_path, $project_id);
        $stmt->execute();

        if ($stmt->affected_rows >= 0) {
            echo "success";
            exit;
        } else {
            echo "Failed to update profile.";
            exit;
        }
    } else {
        // Remove industry from INSERT query since it's now handled separately
        $stmt = $conn->prepare("INSERT INTO project_profiles (project_id, project_name, description, website_url, location, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $project_id, $project_name, $description, $website_url, $location, $profile_picture_path);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "success";
            exit;
        } else {
            echo "Failed to create profile.";
            exit;
        }
    }
}

// Documentation:
// This one was very shit to code. Step by step:
// uploadFile function is a reusable function for upload. Since file names can do all sorts of shit, including
// messing ACID, put malicious injections, etc, this uploadFile is very useful. Note the 0755 is octal permissions
// similar to linux. Next is the actual upload into images/project_profiles. We need to reuse the path variable for
// later in profile.php, so we use __DIR__
