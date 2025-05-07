<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <?php
        $appemail = "";
        session_start();
        require_once 'includes/dbh.php';

        $appemail = $_SESSION['appemail'];
        $sql = "SELECT appFName, appLName FROM applicants WHERE appEmail = '$appemail'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        $appfname = $row['appFName'];
        $applname = $row['appLName'];

        echo "<h1>Hello, " . $appfname . " " . $applname . "</h1>";
        echo "<h2>Welcome to your profile.</h2>"
    ?>

    <li><a href='appindex.php'>Applicant Index</a></li>

</body>
</html>