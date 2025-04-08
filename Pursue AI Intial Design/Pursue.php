<?php
// Start session for profile management
session_start();
$loggedIn = false; // Default not logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header with Logo -->
    <header>
        <div class="logo">LOGO</div>
        <div class="profile">
            <?php echo $loggedIn ? 'PROFILE' : 'LOGIN'; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <h1>PURSUE LISTING CATEGORY</h1>
        
        <!-- Category Grid -->
        <div class="category-grid">
            <div class="category-item">CAT 1</div>
            <div class="category-item">CAT 2</div>
            <div class="category-item">CAT 3</div>
            <div class="category-item">CAT 4</div>
            <div class="category-item">CAT 5</div>
            <div class="category-item">CAT 6</div>
        </div>
    </main>
</body>
</html>