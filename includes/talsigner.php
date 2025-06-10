<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $talent_name = $_POST["talent_name"];
    $talent_email = $_POST["talent_email"]; 
    $talent_password = $_POST["talent_password"];
    require_once 'dbh.php'; 

    $sql = "SELECT talent_id FROM talents WHERE talent_email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $talent_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $_SESSION["talsignuperror"] = "Email already exists!";
            header("Location: ../signup.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION["talsignuperror"] = "Database error.";
        header("Location: ../signup.php");
        exit();
    }

    $hashed_password = password_hash($talent_password, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    $sql = "INSERT INTO talents (talent_email, talent_password) VALUES (?, ?)";
    $stmt1 = $conn->prepare($sql);
    if ($stmt1) {
        $stmt1->bind_param("ss", $talent_email, $hashed_password);
        if ($stmt1->execute()) {
            $talent_id = $stmt1->insert_id;
            $stmt1->close();

            $sql = "INSERT INTO talent_profiles (talent_id, talent_name) VALUES (?, ?)";
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("is", $talent_id, $talent_name);
                if ($stmt2->execute()) {
                    $stmt2->close();
                    $conn->commit();
                    $conn->close();

                    $_SESSION['talent_id'] = $talent_id;
                    header("Location: ../listings.php");
                    exit();
                } else {
                    $stmt2->close();
                    $conn->rollback();
                    $conn->close();
                    $_SESSION["talsignuperror"] = "Failed to insert into talent_profiles.";
                    header("Location: ../signup.php");
                    exit();
                }
            } else {
                $conn->rollback();
                $conn->close();
                $_SESSION["talsignuperror"] = "Error preparing talent_profiles insert.";
                header("Location: ../signup.php");
                exit();
            }
        } else {
            $stmt1->close();
            $conn->rollback();
            $conn->close();
            $_SESSION["talsignuperror"] = "Failed to insert into talents.";
            header("Location: ../signup.php");
            exit();
        }
    } else {
        $conn->close();
        $_SESSION["talsignuperror"] = "Error preparing talents insert.";
        header("Location: ../signup.php");
        exit();
    }
} else {
    header("Location: ../signup.php");
    exit();
}


// Documentation:
// Our database orginally stored talent user data and talent profile data under one really big talent table. 
// However, after reading through forums, turns out its more architecturally simple to split it into a 1-to-1
// relationship where talent_id serves as a foreign key PK of talent_profiles table. Thus, we have to adapt
// the code with begin_transaction() to ensure ACID properties.