<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
</head>
<body>
    <h1>Sign up as recruiter</h1>
    <form action="includes/recsigner.php" method="POST">
        <input type="text" name="recfname" placeholder="First Name" required>
        <input type="text" name="reclname" placeholder="Last Name" required>
        <input type="email" name="recemail" placeholder="E-Mail" required>
        <input type="text" name="reccompanyname"placeholder="Company Name" required>
        <input type="password" name="recpwd" placeholder="Password" required>
        <button type="submit" name="submit">Sign Up</button>
    </form>

    
    <?php
        if (isset($_SESSION["recsignuperror"])) {
            echo $_SESSION["recsignuperror "];
        }
    ?>

    <li><a href='recindex.php'>Recruiter Index</a></li>


</body>
</html>