<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Log in as applicant</h1>
    <form action="includes/applogger.php" method="POST">
        <input type="email" name="appemail" placeholder="E-Mail">
        <input type="password" name="apppwd" placeholder="Password">
        <button type="submit" name="submit">Log In</button>
    </form>

    <li><a href='appindex.php'>Applicant Index</a></li>

    <?php
        session_start();
        if (isset($_SESSION["apploginerror"])) {
            echo $_SESSION["apploginerror"];
        }
    ?>
</body>
</html>