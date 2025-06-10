<?php
    session_start();
    require 'dbh.php';

    if (!isset($_SESSION['talent_id'])) {
        header("Location: ../login.php");
    }

    $talent_id = $_SESSION['talent_id'];

    function createSlug($name, $id) {
        $string = strtolower($name);
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

    function handleSkills($conn, $talent_id, $skillsString) {
        if (empty(trim($skillsString))) {
            return;
        }

        // First, delete existing talent_skills for this talent
        $deleteStmt = $conn->prepare("DELETE FROM talent_skills WHERE talent_id = ?");
        $deleteStmt->bind_param("i", $talent_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Parse skills (assuming comma-separated)
        $skillsArray = array_map('trim', explode(',', $skillsString));
        $skillsArray = array_filter($skillsArray, function($skill) {
            return !empty($skill);
        });

        foreach ($skillsArray as $skillName) {
            $skillName = trim($skillName);
            if (empty($skillName)) continue;

            // Check if skill exists
            $checkSkillStmt = $conn->prepare("SELECT skill_id FROM skills WHERE skill_name = ?");
            $checkSkillStmt->bind_param("s", $skillName);
            $checkSkillStmt->execute();
            $result = $checkSkillStmt->get_result();
            
            if ($result->num_rows > 0) {
                // Skill exists, get its ID
                $row = $result->fetch_assoc();
                $skill_id = $row['skill_id'];
            } else {
                // Skill doesn't exist, create it
                $insertSkillStmt = $conn->prepare("INSERT INTO skills (skill_name) VALUES (?)");
                $insertSkillStmt->bind_param("s", $skillName);
                $insertSkillStmt->execute();
                $skill_id = $conn->insert_id;
                $insertSkillStmt->close();
            }
            $checkSkillStmt->close();

            // Link talent to skill
            $linkStmt = $conn->prepare("INSERT INTO talent_skills (talent_id, skill_id) VALUES (?, ?)");
            $linkStmt->bind_param("ii", $talent_id, $skill_id);
            $linkStmt->execute();
            $linkStmt->close();
        }
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talent_name = trim($_POST['talent_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (empty($talent_name)) {
        echo "Talent name is required.";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM talent_profiles WHERE talent_id = ?");
    $stmt->bind_param("i", $talent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingProfile = $result->fetch_assoc();

    $profile_picture_path = $existingProfile['profile_picture'] ?? null;
    $cv_url_path = '../' . $existingProfile['cv_url'] ?? null;

            // Generate and insert slug
    $slug = createSlug($talent_name, $talent_id);
    $slugStmt = $conn->prepare("UPDATE talent_profiles SET slug = ? WHERE talent_id = ?");
    if ($slugStmt) {
        $slugStmt->bind_param("si", $slug, $talent_id);
        $slugStmt->execute();
        $slugStmt->close();
    }

	if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
		$uploadDir = __DIR__ . '/../images/talent_profiles/';

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
		$newFileName = $talent_id . '.' . $ext;

		$destPath = $uploadDir . $newFileName;

		$webPath = 'images/talent_profiles/' . $newFileName;

		if (!empty($existingProfile['profile_picture']) &&
			$existingProfile['profile_picture'] !== 'images/resources/default.jpg' &&
			file_exists(__DIR__ . '/../' . $existingProfile['profile_picture'])) {
			unlink(__DIR__ . '/../' . $existingProfile['profile_picture']);
		}

		if (move_uploaded_file($fileTmpPath, $destPath)) {
			$profile_picture_path = $webPath;
		} else {
			die("Error uploading profile picture.");
		}
	}

    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = '../images/talent_profiles/';
        $uploadResult = uploadFile($_FILES['cv'], [
            'application/pdf',
            'application/msword',
        ], $uploadDir);

        if (isset($uploadResult['error'])) {
            echo "CV upload error: " . $uploadResult['error'];
            exit;
        }

        $cv_url_path = $uploadResult['path'];
    }

    // Handle skills (this will work for both update and insert)
    handleSkills($conn, $talent_id, $skills);

    if ($existingProfile) {
        // Remove skills from UPDATE query since it's handled separately
        $stmt = $conn->prepare("UPDATE talent_profiles SET talent_name = ?, bio = ?, location = ?, cv_url = ?, profile_picture = ? WHERE talent_id = ?");
        $stmt->bind_param("sssssi", $talent_name, $bio, $location, $cv_url_path, $profile_picture_path, $talent_id);
        $stmt->execute();

        if ($stmt->affected_rows >= 0) {
            echo "success";
            exit;
        } else {
            echo "Failed to update profile.";
            exit;
        }
    } else {
        // Remove skills from INSERT query since it's handled separately
        $stmt = $conn->prepare("INSERT INTO talent_profiles (talent_id, talent_name, bio, location, cv_url, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $talent_id, $talent_name, $bio, $location, $cv_url_path, $profile_picture_path);
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
// similar to linux. Next is the actual upload into images/talent_profiles. We need to reuse the path variable for
// later in profile.php, so we use __DIR__