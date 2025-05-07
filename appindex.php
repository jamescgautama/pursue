<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Index</title>
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
<body>
    <?php
        session_start();
        require_once 'includes/dbh.php';
        
        if (isset($_SESSION['appemail'])) {
            echo "<li><a href='appprofile.php'>Profile</a></li>";
            echo "<li><a href='includes/logout.php'>Logout</a></li>";
            $sql = "SELECT listingID, listingName, content FROM listings";
        } else {
            echo "<li><a href='appsignup.php'>Sign up</a></li>";
            echo "<li><a href='applogin.php'>Log in</a></li>";
        }
    ?>

        <p>This is applicants index</p>
        <li><a href='recindex.php'>Recruit Index</a></li>

            <?php
                $sql = "SELECT listingsID, listingName, content, approval FROM listings";
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    echo "<div class='listing-container'>";
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $listingsID = $row['listingsID'];
                        $listingName = $row['listingName'];
                        $content = $row['content'];
                        $approval = $row['approval'];

                        if ($approval == 1) {
                            echo "<div class='listing'>
                                <h3>" . ($listingName) . "</h3>
                                <p>" . nl2br($content) . "</p>
                            </div>";
                        }

                    }
                
                    echo "</div>";
                } else {
                    echo "<p>No listings available.</p>";
                }
                
                mysqli_close(mysql: $conn);                
            ?>


</body>
</html>