<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

        body {
            background-color: #000; /* Consistent dark background */
            font-family: 'Helvetica Neue', sans-serif;
            color: #fff; /* Default text color for contrast */
            background-image: url('images/resources/inversepursue.svg'); /* Keep existing pattern */
            background-repeat: repeat;
            background-size: 100px 100px;
        }

        .rounded-card {
            border-radius: 20px;
            /* Adjust height to be more flexible and fit content */
            height: auto; 
            min-height: 66vh; /* Ensure it's still tall enough */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.5rem; /* Slightly more padding for a roomier feel */
            min-width: 31vw;
            /* Glassmorphism effect consistent with original */
            background: rgba(18, 18, 18, 0.15);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid #212122; /* Consistent border color */
        }

        .form-control {
            background-color: #1D1D1D; /* Dark input background */
            height: 4rem;
            color: #fff; /* White text for user input */
            border: 1px solid #333; /* Subtle border similar to applications page */
            border-radius: 1.5rem; /* Rounded borders */
            padding-left: 1.5rem; /* Add some left padding for aesthetics */
            transition: border-color 0.3s, box-shadow 0.3s; /* Smooth transition on focus */
        }

        .form-control::placeholder {
            color: #aaa; /* Lighter placeholder text */
        }

        .form-control:focus {
            background-color: #1D1D1D;
            color: #fff;
            border-color: #F97D37; /* Orange accent on focus */
            box-shadow: 0 0 0 0.25rem rgba(249, 125, 55, 0.25); /* Subtle orange glow on focus */
        }

        .btn-primary {
            background-color: #F97D37; /* Orange accent color for primary button */
            color: #ffffff; /* White text on orange button */
            border: 1px solid #F97D37; /* Consistent border */
            border-radius: 1.5rem;
            font-weight: bold;
            height: 4rem;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        .btn-primary:hover {
            background-color: #e6692e; /* Slightly darker orange on hover */
            border-color: #e6692e;
            color: #ffffff;
        }

        /* Specific styles for the "Sign up as talent" title and subtitle */
        .page-header-text {
            color: #fff; /* Ensuring text is white */
            text-align: center;
            margin-bottom: 2rem; /* Spacing below the header */
        }
        
        .page-header-text h1 {
            font-size: 2.25rem; /* Slightly larger for prominence */
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .page-header-text h2 {
            font-size: 1.3rem; /* Subtitle size */
            font-weight: 500;
            color: #ddd; /* Slightly lighter color for subtitle */
        }

        .form-middle {
            flex-grow: 1;
            display: flex;
            align-items: center; /* Vertically center content */
            justify-content: center; /* Horizontally center content */
            padding-top: 1rem; /* Add a bit more space above the form fields */
            padding-bottom: 1rem; /* Add a bit more space below the form fields */
        }

        .form-fields {
            width: 100%;
        }

        .alert-danger {
            background-color: #dc3545; /* Bootstrap red */
            color: #fff; /* White text for error messages */
            border: 1px solid #dc3545;
            border-radius: 0.75rem; /* Consistent rounding */
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
        }

        /* Adjustments for the right side background image */
        .right-bg-image {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100vh;
            background-image: url('images/resources/pursuepeople.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0; /* Ensure it stays in the background */
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .rounded-card {
                max-width: 90vw; /* Wider on smaller screens */
                padding: 2rem;
                height: auto;
                min-height: unset; /* Remove min-height for better mobile fit */
            }
            .right-bg-image {
                display: none !important; /* Hide image on smaller screens if desired, or adjust width */
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-6 col-md-8 d-flex justify-content-center">
                <div class="card rounded-card">
                    <div class="page-header-text">
                        <h2>Welcome Back.</h2>
                        <h1>Login As Talent</h1>
                    </div>

                    <div class="form-middle">
                        <form action="includes/tallogger.php" method="POST" class="form-fields">

                            <div class="mb-3">
                                <input type="email" class="form-control" name="talent_email" placeholder="E-Mail" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="talent_password" placeholder="Password" required>
                            </div>
                            <?php
                                if (isset($_SESSION["talloginerror"])) {
                                    echo '<div class="alert alert-danger mt-3">'.$_SESSION["talloginerror"].'</div>';
                                    unset($_SESSION["talloginerror"]); // Clear the error after displaying
                                }
                            ?>
                            <div class="d-grid mt-4"> <button type="submit" class="btn btn-primary" name="submit">Log In</button>
                            </div>
                        </form>
                    </div>
                <p>
                    <a href="projects/login.php" class="text-decoration-none" style="color: #F97D37; font-weight: 500;">
                    Or login as Project
                    </a>
                </p>

                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block p-0">
                <div class="right-bg-image"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>