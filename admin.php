<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> 
    <style>
        .listing {
            border: 3px solid black;
            margin: 5px;
            padding: 5px;
            max-width: 250px;
            max-height: fit-content;
        }
    </style>
</head>
<body>

<?php
session_start();
require_once 'includes/dbh.php';

if (isset($_SESSION['adminemail'])) {
    echo "<li><a href='appprofile.php'>Profile</a></li>";
    echo "<li><a href='includes/logout.php'>Logout</a></li>";
} else {
    header("Location: adminlogin.php");
    exit();
}

echo "<p>This is admin panel</p>";
echo "<li><a href='recindex.php'>Recruiter Index</a></li>";
echo "<li><a href='appindex.php'>Applicant Index</a></li>";

$sql = "SELECT listingsID, listingName, content, approval FROM listings";
$result = $conn->query($sql);
$adminemail = $_SESSION['adminemail'];

if ($result && $result->num_rows > 0) {
    echo "<div class='listing-container'>";

    while ($row = $result->fetch_assoc()) {
        $listingsID = $row['listingsID'];
        $listingName = $row['listingName'];
        $content = $row['content'];
        $approval = $row['approval'];

        if ($approval == 0) {
            echo "<div class='listing'>
                    <h3>" . htmlspecialchars($listingName) . "</h3>
                    <p>" . nl2br(htmlspecialchars($content)) . "</p>
                    <button class='approve-btn' data-id='" . $listingsID . "'>Approve?</button>
                  </div>";
        }
    }

    echo "</div>";
} else {
    echo "<p>No listings available.</p>";
}

$conn->close();
?>

<script>
$(document).on("click", ".approve-btn", function() {
    const listingsID = $(this).data("id");
    const button = $(this);

    $.ajax({
        url: "includes/listingsapprove.php",
        method: "POST",
        data: { id: listingsID },
        success: function(response) {
            if (response.trim() === "success") {
                button.text("Approved").prop("disabled", true);
            } else {
                alert("Failed to approve listing. Server said: " + response);
            }
        }
    });
});
</script>

</body>
</html>
