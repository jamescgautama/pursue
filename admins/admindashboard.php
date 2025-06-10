<?php
    session_start();
    session_status();
    require_once '../includes/dbh.php';
    if (!isset($_SESSION['admin_id'])) {
        header("Location: adminlogin.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin | Pursue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <style>
        /* Base */
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

        /* Container and border */
        .container-fluid {
            padding: 2rem 3rem;
        }

        /* Header */
        h1 {
            font-weight: 700;
            font-size: 2rem;
            color: #fff;
        }

        /* Border bottom on header container */
        .border-bottom {
            border-color: #161b1f !important;
            border-width: 0.5px !important;
        }

        /* Logout button */
        .btn-outline-danger {
            color: #F97D37;
            border-color: #F97D37;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 600;
        }
        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            background-color: #F97D37;
            color: #000;
            border-color: #F97D37;
            box-shadow: none;
        }

        /* Cards */
        .card {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            transition: border-color 0.3s ease, transform 0.3s ease;
        }
        .card:hover {
            border-color: #F97D37;
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(249, 125, 55, 0.15);
        }

        /* Card body */
        .card-body {
            padding: 1.5rem 2rem;
        }

        /* Card titles */
        .card-title {
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
        }

        .card-subtitle {
            color: #F97D37 !important;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        /* Card text */
        .card-text {
            color: #ddd;
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Strong labels */
        .card-text strong {
            color: #F97D37;
        }

        /* Buttons container */
        .d-flex.gap-2 {
            margin-top: 1rem;
        }

        /* Buttons */
        .btn-success {
            background-color: #F97D37;
            border: 1px solid #F97D37;
            font-weight: 600;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-success:hover,
        .btn-success:focus {
            background-color: #e6692e;
            border-color: #e6692e;
            box-shadow: none;
            color: #fff;
        }

        .btn-danger {
            background-color: #f44336;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-danger:hover,
        .btn-danger:focus {
            background-color: #d32f2f;
            box-shadow: none;
            color: #fff;
        }

        /* Alert */
        .alert-info {
            background-color: #111;
            border-color: #333;
            color: #aaa;
            font-size: 1.1rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .alert-heading {
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <h1 class="mb-0">Admin Dashboard - Listings</h1>
            <a href="../includes/logout.php" class="btn btn-outline-danger">Logout</a>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <?php
                    $sql = "SELECT listing_id, job_title, description, location, salary, job_type, category, approval FROM listings WHERE approval IS NULL";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $listings_id = $row['listing_id'];
                            $job_title = $row['job_title'];
                            $description = $row['description'];
                            $location = $row['location'];
                            $salary = $row['salary'];
                            $job_type = $row['job_type'];
                            $category = $row['category'];
                            $approval = $row['approval'];
                            
                            echo "<div class='card mb-3'>
                                <div class='card-body'>
                                    <h5 class='card-title'>" . ($job_title) . "</h5>
                                    <h6 class='card-subtitle mb-2 text-success'>" . ($salary) . "</h6>
                                    <p class='card-text'>
                                        <strong>Location:</strong> " . ($location) . "<br>
                                        <strong>Type:</strong> " . ($job_type) . "<br>
                                        <strong>Category:</strong> " . ($category) . "
                                    </p>
                                    <p class='card-text'>" . nl2br(($description)) . "</p>
                                    
                                    <div class='d-flex gap-2'>
                                        <form method='POST' action='../includes/approvelistings.php' style='display:inline;'>
                                            <input type='hidden' name='listing_id' value='" . $listings_id . "'>
                                            <input type='hidden' name='approval' value='1'>
                                            <button type='submit' class='btn btn-success'>Approve</button>
                                        </form>
                                        <form method='POST' action='../includes/approvelistings.php' style='display:inline;'>
                                            <input type='hidden' name='listing_id' value='" . $listings_id . "'>
                                            <input type='hidden' name='approval' value='0'>
                                            <button type='submit' class='btn btn-danger'>Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<div class='alert alert-info text-center' role='alert'>
                            <h4 class='alert-heading'>No Pending Listings</h4>
                            <p class='mb-0'>There are no listings waiting for approval at this time.</p>
                        </div>";
                    }
                    $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
