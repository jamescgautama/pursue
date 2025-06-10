<?php
session_start();
require_once 'dbh.php';

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function generateProjectCard($row) {
    
    $project_picture = !empty($row['profile_picture']) ? escape($row['profile_picture']) : '../images/resources/default_project.jpg'; // Path to a default project image
    $project_picture = str_replace('../', '', $project_picture);

    $truncated_description = escape(substr($row['description'], 0, 150)) . (strlen($row['description']) > 150 ? '...' : '');

    return '
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="project-card h-100" onclick="window.location.href=\'/pursue/discover/' . escape($row['slug']) . '\'" style="cursor: pointer;">
            <div class="project-card-top-bg"></div> <div class="project-logo-container">
                <img src="' . $project_picture . '" alt="' . escape($row['project_name']) . ' Logo" class="project-logo">
            </div>
            <div class="project-card-content-wrapper">
                <h5 class="project-title">' . escape($row['project_name']) . '</h5>
                <p class="project-description">' . $truncated_description . '</p>
                ' . (isset($row['category']) ? '<div class="project-badges mt-3"><span class="category-badge">' . escape($row['category']) . '</span></div>' : '') . '
            </div>
            <div class="project-footer">
                <i class="bi bi-arrow-right-circle" style="color: #F97D37; font-size: 1.2rem;"></i>
            </div>
        </div>
    </div>';
}

if (isset($_GET['query']) && trim($_GET['query']) !== '') {
    $query = $_GET['query'];
    $search = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM project_profiles WHERE project_name LIKE ? OR description LIKE ? ORDER BY project_name ASC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo generateProjectCard($row);
        }
    } else {
        echo '
        <div class="col-12">
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h4>No Projects Found</h4>
                <p class="text-muted mb-3">We couldn\'t find any projects matching your search. Try different keywords or browse all projects.</p>
            </div>
        </div>';
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM project_profiles ORDER BY project_name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo generateProjectCard($row);
        }
    } else {
        echo '
        <div class="col-12">
            <div class="no-results">
                <i class="bi bi-inbox"></i>
                <h4>No Projects Available</h4>
                <p class="text-muted mb-3">There are currently no projects to discover. Check back later!</p>
            </div>
        </div>';
    }
    $stmt->close();
}

$conn->close();
?>

<style>
/* Base card styles - consistent with previous designs */
.project-card {
    background-color: #111;
    border: 1px solid #333;
    border-radius: 15px; /* More rounded corners */
    padding: 0; /* Remove internal padding, will be handled by sub-elements */
    transition: all 0.3s;
    overflow: hidden; /* Crucial for background-strip and logo overlap */
    cursor: pointer;
    position: relative; /* Needed for absolute positioning of top-bg and logo */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Add a subtle default shadow */
    display: flex; /* Enable flexbox */
    flex-direction: column; /* Stack elements vertically */
}

.project-card:hover {
    border-color: #F97D37;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(249, 125, 55, 0.1);
}

/* New top background strip for the logo, mimicking Zapier card */
.project-card-top-bg {
    background-color: #212122; /* A dark grey, or a subtle orange if you prefer it like the Zapier card */
    height: 70px; /* Height of the top strip */
    border-top-left-radius: 15px; /* Match card border radius */
    border-top-right-radius: 15px; /* Match card border radius */
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1; /* Below logo, above main card content */
}

/* Project Logo/Profile Picture styling */
.project-logo-container {
    position: absolute;
    top: 25px; /* Adjust this value to control how much it overlaps the top strip */
    left: 20px; /* Spacing from the left edge */
    width: 80px; /* Size of the logo container */
    height: 80px; /* Size of the logo container */
    border-radius: 15px; /* Rounded square shape */
    overflow: hidden; /* Ensure image respects the container's border-radius */
    background-color: #1a1a1a; /* Background for logos that might not fill the space */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2; /* Ensure logo is above the background strip */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Subtle shadow for the logo */
}

.project-logo {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures image covers the area without distortion */
    border-radius: 15px; /* Consistent with container for full rounding */
}

/* Content wrapper to push content below the logo */
.project-card-content-wrapper {
    padding: 20px; /* General padding for content */
    padding-top: 110px; /* Push content down to account for the overlapping logo + padding */
    position: relative; /* For z-index stacking if needed */
    z-index: 0; /* Keep content below the logo */
    flex: 1;
}

.project-title {
    font-size: 1.4rem; /* Slightly larger title */
    font-weight: bold;
    color: #fff;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.project-description {
    color: #ddd;
    line-height: 1.6;
    font-size: 0.95rem;
    margin-bottom: 0;
}

.project-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.category-badge { /* Similar to the applications page category badge */
    background-color: #222;
    color: #F97D37;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid #F97D37;
}


.project-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #333;
    background-color: #0a0a0a;
    display: flex; /* Use flexbox for alignment */
    justify-content: flex-end; /* Push arrow to the right */
    align-items: center;
    margin-top: auto;
}

/* No results / No projects available styling */
.no-results {
    text-align: center;
    padding: 3rem;
    background-color: #111;
    border: 1px solid #333;
    border-radius: 15px;
    color: #fff; /* Ensure text is visible */
}

.no-results i {
    font-size: 3rem;
    color: #666; /* Muted icon color */
    margin-bottom: 1rem;
    display: block;
}

.no-results h4 {
    color: #fff;
    margin-bottom: 1rem;
}

.text-muted {
    color: #aaa !important; /* Lighter grey for muted text */
}
</style>