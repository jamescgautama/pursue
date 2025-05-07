<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $adminemail = $_POST["adminemail"];
    $adminpwd = $_POST["adminpwd"];
    
    require_once 'dbh.php';

    $sql = "SELECT adminID, adminPassword FROM admins WHERE adminEmail = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $adminemail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($adminID, $db_password);
            $stmt->fetch();

            if ($adminpwd === $db_password) {

                $_SESSION['adminemail'] = $adminemail;
                header("Location: ../admin.php");
                exit();
            }
            else {
                $_SESSION["adminloginerror"] = "Invalid email or password!";
                header(header: "Location: ../adminlogin.php");
                exit();
            }
        } else {
            $_SESSION["adminloginerror"] = "No account found with this email";
            $stmt->close();
            header("Location: ../adminlogin.php");
            exit();
        }

    } else {
        $_SESSION["adminloginerror"] = "Something went wrong. Please try again later.";
        $conn->close();
        header("Location: ../adminlogin.php");
        exit();
    }


} else {
    header("Location: ../adminlogin.php");
    exit();
}
