<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $talent_email = $_POST["talent_email"]; 
    $talent_password = $_POST["talent_password"];
    require_once 'dbh.php'; 
    
    $sql = "SELECT talent_id, talent_password FROM talents WHERE talent_email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $talent_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($talent_id, $db_password);
            $stmt->fetch();

            if (password_verify($talent_password, $db_password)) {

                $_SESSION['talent_id'] = $talent_id;
                $stmt->close();
                header(header: "Location: ../listings.php");
                exit();
            }
            else {
                $_SESSION["talloginerror"] = "Invalid email or password!";
                header("Location: ../login.php");
                exit();
            }
        } else {
            $_SESSION["talloginerror"] = "No account found with this email";
            $stmt->close();
            header("Location: ../login.php");
            exit();
        }

    } else {
        $_SESSION["talloginerror"] = "Something went wrong. Please try again later.";
        $conn->close();
        header("Location: ../login.php");
        exit();
    }


} else {
    header("Location: ../login.php");
    exit();
}

// Documentation:
// Just simple code for checking if it exists. This works by checking if the number of rows where 
// the statement logic is true is more than one. If so, then there is the account.