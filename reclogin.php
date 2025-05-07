<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Log in as recruiter</h1>
    <form action="includes/reclogger.php" method="POST">
        <input type="email" name="recemail" placeholder="E-Mail">
        <input type="password" name="recpwd" placeholder="Password">
        <button type="submit" name="submit">Log In</button>
    </form>

    
    <?php
    session_start();
    if (isset($_SESSION["recloginerror"])) {
        echo $_SESSION["recloginerror"];
    }
    ?>

    <li><a href='recindex.php'>Recruiter Index</a></li>


</body>
</html>