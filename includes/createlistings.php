<?php
session_start();
require_once "dbh.php";

function createSlug($title, $id) {
    $string = strtolower($title);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');                          
    return $id . '-' . $string;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["project_id"])) {
        $_SESSION["createlistingerror"] = "Project ID not found in session.";
        header("Location: ../projects/create.php");
        exit();
    }

    $job_title = $_POST["job_title"];
    $description = $_POST["description"];
    $location = $_POST["location"];
    $salary = $_POST["salary"];
    $category = $_POST["category"];
    $job_type = $_POST["job_type"];
    $approval = NULL;
    $project_id = $_SESSION["project_id"];

    $stmt = $conn->prepare(
        "INSERT INTO listings (project_id, job_title, description, location, salary, job_type, category, approval)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param(
            "issssdss",
            $project_id,
            $job_title,
            $description,
            $location,
            $salary,
            $job_type,
            $category,
            $approval
        );

        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;
            $stmt->close();

            $slug = createSlug($job_title, $listing_id);
            $slugstmt = $conn->prepare("UPDATE listings SET slug = ? WHERE listing_id = ?");
            if ($slugstmt) {
                $slugstmt->bind_param("si", $slug, $listing_id);
                $slugstmt->execute();
                $slugstmt->close();
            }

            $conn->close();
            header("Location: ../projects/create.php");
            exit();
        } else {
            $_SESSION["createlistingerror"] = "Error inserting listing: " . $stmt->error;
            $stmt->close();
            $conn->close();
            header("Location: ../projects/create.php");
            exit();
        }
    } else {
        $_SESSION["createlistingerror"] = "Failed to prepare insert statement.";
        $conn->close();
        header("Location: ../projects/create.php");
        exit();
    }
} else {
    header("Location: ../projects/create.php");
    exit();
}
