<?php
session_start();
require_once 'dbh.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_SESSION['adminemail'])) {
    $listingID = intval($_POST['id']);
    $adminEmail = $_SESSION['adminemail'];

    $stmt = $conn->prepare("SELECT adminID FROM admins WHERE adminEmail = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($admin = $result->fetch_assoc()) {
        $adminID = $admin['adminID'];
        $stmt->close();

        $update = $conn->prepare("UPDATE listings SET approval = 1, adminID = ? WHERE listingsID = ?");
        $update->bind_param("ii", $adminID, $listingID);

        if ($update->execute()) {
            echo "success";
        } else {
            echo "Update failed: " . $update->error;
        }

        $update->close();
    } else {
        echo "Admin not found";
    }

    $conn->close();
} else {
    echo "Invalid request or session missing";
}
