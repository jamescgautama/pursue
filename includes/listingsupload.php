<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "dbh.php";

    $listingname = $_POST["listingname"];
    $content = $_POST["content"];
    $recEmail = $_SESSION["recemail"];
    $approval = 0;

    $stmt = $conn->prepare("SELECT recID FROM recruiters WHERE recEmail = ?");
    if ($stmt) {
        $stmt->bind_param("s", $recEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $recID = $row["recID"];
        } else {
            $_SESSION["listingerror"] = "Recruiter not found.";
            $stmt->close();
            $conn->close();
            header("Location: ../rexindex.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION["listingerror"] = "Database error.";
        $conn->close();
        header("Location: ../rexindex.php");
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO listings (listingName, content, recID, approval) VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param("ssii", $listingname, $content, $recID, $approval);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: ../appindex.php");
            exit();
        } else {
            $_SESSION["listingerror"] =
                "Error inserting listing: " . $stmt->error;
            $stmt->close();
            $conn->close();
            header("Location: ../rexindex.php");
            exit();
        }
    } else {
        $_SESSION["listingerror"] = "Failed to prepare insert statement.";
        $conn->close();
        header("Location: ../rexindex.php");
        exit();
    }
} else {
    header("Location: ../recindex.php");
    exit();
}
