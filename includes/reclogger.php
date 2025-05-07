<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recemail = $_POST["recemail"];
    $recpwd = $_POST["recpwd"];
    
    require_once 'dbh.php'; // Assumes $conn is an OOP mysqli instance

    $sql = "SELECT recID, recPassword FROM recruiters WHERE recEmail = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $recemail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($recID, $db_password);
            $stmt->fetch();

            if ($recpwd === $db_password) { // Plain text check (see below)
                $_SESSION['recemail'] = $recemail;
                $stmt->close();
                $conn->close();
                header("Location: ../recindex.php");
                exit();
            } else {
                $stmt->close();
                $conn->close();
                $_SESSION["recloginerror"] = "Invalid email or password!";
                header("Location: ../reclogin.php");
                exit();
            }
        } else {
            $stmt->close();
            $conn->close();
            $_SESSION["recloginerror"] = "No account found with this email";
            header("Location: ../reclogin.php");
            exit();
        }

    } else {
        $_SESSION["recloginerror"] = "Database error. Please try again.";
        $conn->close();
        header("Location: ../reclogin.php");
        exit();
    }

} else {
    header("Location: ../reclogin.php");
    exit();
}
