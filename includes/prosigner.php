<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_name = $_POST["project_name"];
    $project_email = $_POST["project_email"]; 
    $project_password = $_POST["project_password"];
    require_once 'dbh.php'; 

    $sql = "SELECT project_id FROM projects WHERE project_email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $project_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $_SESSION["prosignuperror"] = "Email already exists!";
            header("Location: ../projects/signup.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION["prosignuperror"] = "Database error.";
        header("Location: ../projects/signup.php");
        exit();
    }

    $hashed_password = password_hash($project_password, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    $sql = "INSERT INTO projects (project_email, project_password) VALUES (?, ?)";
    $stmt1 = $conn->prepare($sql);
    if ($stmt1) {
        $stmt1->bind_param("ss", $project_email, $hashed_password);
        if ($stmt1->execute()) {
            $project_id = $stmt1->insert_id;
            $stmt1->close();

            $sql = "INSERT INTO project_profiles (project_id, project_name) VALUES (?, ?)";
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("is", $project_id, $project_name);
                if ($stmt2->execute()) {
                    $stmt2->close();
                    $conn->commit();
                    $conn->close();

                    $_SESSION['project_id'] = $project_id;
                    header("Location: ../projects/listings.php");
                    exit();
                } else {
                    $stmt2->close();
                    $conn->rollback();
                    $conn->close();
                    $_SESSION["prosignuperror"] = "Failed to insert into project_profiles.";
                    header("Location: ../projects/signup.php");
                    exit();
                }
            } else {
                $conn->rollback();
                $conn->close();
                $_SESSION["prosignuperror"] = "Error preparing project_profiles insert.";
                header("Location: ../projects/signup.php");
                exit();
            }
        } else {
            $stmt1->close();
            $conn->rollback();
            $conn->close();
            $_SESSION["prosignuperror"] = "Failed to insert into projects.";
            header("Location: ../projects/signup.php");
            exit();
        }
    } else {
        $conn->close();
        $_SESSION["prosignuperror"] = "Error preparing projects insert.";
        header("Location: ../projects/signup.php");
        exit();
    }
} else {
    header("Location: ../projects/signup.php");
    exit();
}


// Documentation:
// Our database orginally stored project user data and project profile data under one really big project table. 
// However, after reading through forums, turns out its more architecturally simple to split it into a 1-to-1
// relationship where project_id serves as a foreign key PK of project_profiles table. Thus, we have to adapt
// the code with begin_transaction() to ensure ACID properties.