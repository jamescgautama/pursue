<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $appemail = $_POST["appemail"];
    $apppwd = $_POST["apppwd"];
    
    require_once 'dbh.php';

    $sql = "SELECT appID, appPassword FROM applicants WHERE appEmail = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $appemail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($appID, $db_password);
            $stmt->fetch();

            if ($apppwd === $db_password) {

                $_SESSION['appemail'] = $appemail;
                header("Location: ../appindex.php");
                exit();
            }
            else {
                $_SESSION["apploginerror"] = "Invalid email or password!";
                header("Location: ../applogin.php");
                exit();
            }
        } else {
            $_SESSION["apploginerror"] = "No account found with this email";
            $stmt->close();
            header("Location: ../applogin.php");
            exit();
        }

    } else {
        $_SESSION["apploginerror"] = "Something went wrong. Please try again later.";
        $conn->close();
        header("Location: ../applogin.php");
        exit();
    }


} else {
    header("Location: ../applogin.php");
    exit();
}
