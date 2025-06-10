<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_email = $_POST["project_email"]; 
    $project_password = $_POST["project_password"];
    require_once 'dbh.php'; 
    
    $sql = "SELECT project_id, project_password FROM projects WHERE project_email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $project_email);
        $stmt->execute();
        $stmt->store_result();


        if ($stmt->num_rows > 0) {
            $stmt->bind_result($project_id, $db_password);
            $stmt->fetch();

            if (password_verify($project_password, $db_password)) {

                $_SESSION['project_id'] = $project_id;
                header(header: "Location: ../projects/listings.php");
                exit();
            }
            else {  
                $_SESSION["prologinerror"] = "Invalid email or password!";
                header("Location: ../projects/login.php");
                exit();
            }
        } else {
            $_SESSION["prologinerror"] = "No account found with this email";
            $stmt->close();
            header("Location: ../projects/login.php");
            exit();
        }

    } else {
        $_SESSION["prologinerror"] = "Something went wrong. Please try again later.";
        $conn->close();
        header("Location: ../projects/login.php");
        exit();
    }


} else {
    header("Location: ../projects/login.php");
    exit();
}

// Documentation:
// Just simple code for checking if it exists. This works by checking if the number of rows where 
// the statement logic is true is more than one. If so, then there is the account.