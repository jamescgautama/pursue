<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type for AJAX response
header('Content-Type: text/plain');

// Try to include database connection
if (file_exists('../dbh.php')) {
    require_once '../dbh.php';
} elseif (file_exists('dbh.php')) {
    require_once 'dbh.php';
} elseif (file_exists('../../dbh.php')) {
    require_once '../../dbh.php';
} else {
    echo "Database connection file not found.";
    exit();
}

// Check if connection exists
if (!isset($conn)) {
    echo "Database connection not established.";
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['talent_id'])) {
    echo "Please log in first.";
    exit();
}

// Check if listing_id is provided
if (!isset($_POST['listing_id']) || empty($_POST['listing_id'])) {
    echo "Invalid listing ID.";
    exit();
}

$talent_id = $_SESSION['talent_id'];
$listing_id = intval($_POST['listing_id']);

// Debug logging (remove in production)
error_log("Apply attempt - Talent ID: $talent_id, Listing ID: $listing_id");

try {
    // Check if user has already applied
    $checkSql = "SELECT * FROM applications WHERE talent_id = ? AND listing_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $talent_id, $listing_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo "You have already applied for this listing.";
        $checkStmt->close();
        $conn->close();
        exit();
    }
    
    $checkStmt->close();
    
    // Insert new application
    $sql = "INSERT INTO applications (talent_id, listing_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $talent_id, $listing_id);
    
    if ($stmt->execute()) {
        echo "Application submitted successfully!";
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    // Show the actual error for debugging (remove in production)
    echo "Database Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>