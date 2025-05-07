<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings</title>
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
require_once "includes/dbh.php";

$recEmail = $_SESSION["recemail"];

$query = $conn->prepare(
    "SELECT recID, recFName, recLName FROM recruiters WHERE recEmail = ?"
);
$query->bind_param("s", $recEmail);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

$recfname = $row["recFName"];
$reclname = $row["recLName"];
$recID = $row["recID"];

echo "<h1>Hello, " .
    htmlspecialchars($recfname) .
    " " .
    htmlspecialchars($reclname) .
    "</h1>";
echo "<h2>Welcome to your listings.</h2>";

$query = $conn->prepare(
    "SELECT listingName, content, approval FROM listings WHERE recID = ?"
);
$query->bind_param("i", $recID);
$query->execute();
$result = $query->get_result();

if ($result && $result->num_rows > 0) {
    echo "<div class='listing-container'>";

    while ($row = $result->fetch_assoc()) {
        $listingName = $row["listingName"];
        $content = $row["content"];
        $approval = $row["approval"];

        echo "<div class='listing'> 
                <h3>" .
            htmlspecialchars($listingName) .
            "</h3> 
                <p>" .
            nl2br(htmlspecialchars($content)) .
            "</p>
                <p>" .
            ($approval == 1 ? "Approved" : "Not approved") .
            "</p>
              </div>";
    }

    echo "</div>";
} else {
    echo "<p>No listings available.</p>";
}

$conn->close();
?>

<li><a href='recindex.php'>Recruiter Index</a></li>

</body>
</html>
