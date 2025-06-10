<?php
session_start();
require_once 'includes/dbh.php';


if (isset($_SESSION['talent_id'])) {
  header("Location: listings.php");
  exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pursue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    font-family: 'Helvetica Neue', sans-serif;
    background-color: #000;
    color: white;
    overflow-x: hidden;
    padding-right: 0;
    padding-left: 0;
  }

  main {
    padding-left: 25px;
    padding-right: 25px;
  }

  nav {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .navbar {
    background-color: #000;
    min-height: 90px;
    padding-top: 1rem;
    padding-bottom: 1rem;
    font-size: 1.5rem;
    border-bottom: solid #161b1fz 0.5px;
  }

  .navbar-brand, .nav-link {
    color: white !important;
  }

  .navbar-brand {
    font-size: 2rem;
    font-weight: bold;
  }

  .navbar-brand img {
    width: 50px;
    height: 50px;
  }

  .nav-link:hover {
    color: #F97D37 !important;
  }

  .profile-img {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
  }

  .hero-text {
  font-size: clamp(4rem, 10vw, 8rem); /* min: 2rem, responsive: 8vw, max: 5rem */
      font-weight: bold;
    z-index: 1;
  }


  .image-section img {
    width: 100%;
    max-width: 100%;
    min-height: 100%;
    right: auto;
      z-index: 1;
  }

  .full-height {
    height: calc(100vh - 80px); /* match navbar height */
  }

  @media (max-width: 991.98px) {
  .navbar .profile-img {
    margin-left: 1rem;
    }
  }

.grid {
    height: 100%;
    background-image:
        linear-gradient(to right, #E3E3E3, 0.5px, transparent 0.5px),
        linear-gradient(to bottom, #E3E3E3, 0.5px, transparent 0.5px);
    background-size: 9rem 9rem;
    background-position: center center;
    z-index: -100;
    position: absolute; /* Changed to absolute positioning */
    top: 0; /* Position from the top */
    left: 0; /* Position from the left */
    width: 100%; /* Make it span the full width */
    height: 100%; /* Make it span the full height of its positioned parent */
}

.btn-success, .btn-success:hover, .btn-success:active, .btn-success:visited {
    background-color: #F97D37 !important;
    border: #F97D37; !important;
}

@keyframes fadeInOut {
  0% { opacity: 0; color: #fef2eb}
  10% { opacity: 1; color: #fbb187;}
  75% { opacity: 1; color: #f97d37}
  90% { opacity: 1; color: #f97d37}
  100% { opacity: 0}
}

#cycling-word {
  display: inline-block;
  animation: fadeInOut 3s infinite ease-in-out;
  transition: opacity 0.5s ease-in-out;
}


</style>

</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="images/resources/pursuelogo.svg" width="50" height="50" class="me-2" alt="Logo">
      Pursue
    </a>


    <div class="d-lg-none d-flex align-items-center ms-auto">

    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <div class="navbar-nav">

      </div>

      <div class="d-flex ms-auto">
        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
        <a href="signup.php" class="btn btn-success">Signup</a>
      </div>
    </div>
  </div>
</nav>

  <main>
    <div class="container-fluid full-height d-flex p-0">
  <div class="row w-100 g-0 align-items-center">
    
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="text-left px-4">
        <p class="hero-text">
          To pursue<span id="cycling-word">unyielding.</span>
        </p>
      </div>
    </div>

    <div class="col-md-6 d-flex align-items-center justify-content-center image-section">
      <img src="images/resources/steve.png" alt="Hero Image" class="img-fluid">
    </div>

  </div>
</div>

  </main>

<div class="grid"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const words = ["unyielding.", "ambitious.", "synergetic.", "change."];
  let currentIndex = 0;

  const wordSpan = document.getElementById("cycling-word");

  setInterval(() => {
    currentIndex = (currentIndex + 1) % words.length;
    wordSpan.textContent = words[currentIndex];
  }, 3000);
</script>

</body>
</html>
