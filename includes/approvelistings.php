<?php
session_start();
require_once 'dbh.php'; // make sure this path is correct

// Ensure only logged-in admins can approve/reject
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admins/adminlogin.php");
    exit();
}

// Validate the request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'], $_POST['approval'])) {
    $listing_id = intval($_POST['listing_id']);
    $approval = ($_POST['approval'] == '1') ? 1 : 0;

    // Prepare SQL to prevent SQL injection
    $stmt = $conn->prepare("UPDATE listings SET approval = ? WHERE listing_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $approval, $listing_id);

        if ($stmt->execute()) {
            // Redirect back to dashboard with optional success query param
            header("Location: ../admins/admindashboard.php");
            exit();
        } else {
            echo "Error updating listing: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to prepare statement.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
