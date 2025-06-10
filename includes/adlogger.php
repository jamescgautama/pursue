<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $admin_email = $_POST["admin_email"]; 
    $admin_password = $_POST["admin_password"];
    require_once 'dbh.php'; 
    
    $sql = "SELECT admin_id, admin_password FROM admins WHERE admin_email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $db_password);
            $stmt->fetch();

            if ($admin_password == $db_password) {

                $_SESSION['admin_id'] = $admin_id;
                header(header: "Location: ../admins/admindashboard.php");
                exit();
            }
            else {
                $_SESSION["adloginerror"] = "Invalid email or password!";

                header("Location: ../admins/adminlogin.php");
                exit();
            }
        } else {
            $_SESSION["adloginerror"] = "No account found with this email" . $admin_email . "<-the email";
            $stmt->close();
            header("Location: ../admins/adminlogin.php");
            exit();
        }

    } else {
        $_SESSION["adloginerror"] = "Something went wrong. Please try again later.";
        $conn->close();
        header("Location: ../admins/adminlogin.php");
        exit();
    }


} else {
    header("Location: ../admins/adminlogin.php");
    exit();
}

// Documentation:
// Just simple code for checking if it exists. This works by checking if the number of rows where 
// the statement logic is true is more than one. If so, then there is the account.