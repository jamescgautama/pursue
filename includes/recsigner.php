<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recfname = $_POST["recfname"];
    $reclname = $_POST["reclname"]; 
    $recemail = $_POST["recemail"]; 
    $reccompanyname = $_POST["reccompanyname"]; 
    $recpwd = $_POST["recpwd"];
    require_once 'dbh.php';

    $sql = "SELECT recID FROM recruiters WHERE recEmail = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $recemail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $_SESSION["recsignuperror"] = "Email already exists!";
            header("Location: ../recsignup.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION["recsignuperror"] = "Database error.";
        header("Location: ../recsignup.php");
        exit();
    }

    // Insert recruiter
    $sql = "INSERT INTO recruiters (recFName, recLName, recEmail, companyName, recPassword) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssss", $recfname, $reclname, $recemail, $reccompanyname, $recpwd);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['recemail'] = $recemail;
            header("Location: ../recindex.php");
            exit();
        } else {
            $_SESSION["recsignuperror"] = "Error inserting record: " . $stmt->error;
            $stmt->close();
            $conn->close();
            header("Location: ../recsignup.php");
            exit();
        }

    } else {
        $_SESSION["recsignuperror"] = "Failed to prepare statement.";
        $conn->close();
        header("Location: ../recsignup.php");
        exit();
    }

} else {
    header("Location: ../recsignup.php");
    exit();
}
