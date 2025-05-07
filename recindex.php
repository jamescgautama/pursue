<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Index</title>
    <style>
        form {
            display: flex;
            flex-direction: column;
            width: 300px;
            padding: 20px;

        }

        form input,
        form textarea,
        form button {
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <?php
        session_start();

        
        if (isset($_SESSION['recemail'])) {
            echo "<li><a href='reclistings.php'>My Listings</a></li>";
            echo "<li><a href='includes/logout.php'>Logout</a></li>";
        } else {
            echo "<li><a href='recsignup.php'>Sign up</a></li>";
            echo "<li><a href='reclogin.php'>Log in</a></li>";
        }
    ?>

        <p>This is recruit index</p>

        <li><a href='appindex.php'>Applicant Index</a></li>


    <form action="includes/listingsupload.php" method="POST">
        <input type="text" name="listingname" placeholder="Listing Name">
        <textarea name="content"></textarea>
        <button type="submit" name="submit">Submit Listing</button>
    </form>

    <?php 
        if (isset($_SESSION["listingerror"])) {
            echo $_SESSION["listingerror"];
        }
    ?>

</body>
</html>