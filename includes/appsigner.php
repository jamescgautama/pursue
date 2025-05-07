<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $appfname = $_POST["appfname"];
    $applname = $_POST["applname"]; 
    $appemail = $_POST["appemail"]; 
    $apppwd = $_POST["apppwd"];
    require_once 'dbh.php'; 

    $sql = "SELECT appID FROM applicants WHERE appEmail = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $appemail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $_SESSION["appsignuperror"] = "Email already exists!";
            header("Location: ../appsignup.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION["appsignuperror"] = "Database error. Please try again later.";
        header("Location: ../appsignup.php");
        exit();
    }

    $sql = "INSERT INTO applicants (appFName, appLName, appEmail, appPassword) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $appfname, $applname, $appemail, $apppwd);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['appemail'] = $appemail;
            header("Location: ../appindex.php");
            exit();
        } else {
            $stmt->close();
            $conn->close();
            $_SESSION["appsignuperror"] = "Error: " . $conn->error;
            header("Location: ../appsignup.php");
            exit();
        }
    } else {
        $conn->close();
        $_SESSION["appsignuperror"] = "Error preparing statement.";
        header("Location: ../appsignup.php");
        exit();
    }

} else {
    header("Location: ../appsignup.php");
    exit();
}
