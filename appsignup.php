<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
</head>
<body>
    <h1>Sign up as applicant</h1>
    <form action="includes/appsigner.php" method="POST">
        <input type="text" name="appfname" placeholder="First Name" required>
        <input type="text" name="applname" placeholder="Last Name" required>
        <input type="email" name="appemail" placeholder="E-Mail" required>
        <input type="password" name="apppwd" placeholder="Password" required>
        <button type="submit" name="submit">Sign Up</button>
    </form>

    <li><a href='appindex.php'>Applicant Index</a></li>

    <?php
        if (isset($_SESSION["appsignuperror"])) {
            echo $_SESSION["appsignuperror "];
        }
    ?>
</body>
</html>