<?php
session_start();
require_once 'dbh.php';

if (!isset($_SESSION['project_id'])) {
    header("Location: ../index.php");
    exit();
}

$project_id = $_SESSION['project_id'];

if (isset($_POST['action']) && ($_POST['action'] == 'approve' || $_POST['action'] == 'decline')) {
    $application_id = intval($_POST['application_id']);
    $new_status = ($_POST['action'] == 'approve') ? 'Accepted' : 'Rejected';
    
    $verify_sql = "SELECT a.application_id 
                    FROM applications a 
                    JOIN listings l ON a.listing_id = l.listing_id 
                    WHERE a.application_id = ? AND l.project_id = ?";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $application_id, $project_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $update_sql = "UPDATE applications SET status = ? WHERE application_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_status, $application_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Application ' . strtolower($new_status) . ' successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating application status']);
        }
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    }
    
    $verify_stmt->close();
    exit();
}

// Handle AJAX requests for filtering
if (isset($_POST['action']) && $_POST['action'] == 'filter') {
    $status_filter = $_POST['status'];
    $normalized_status = trim(strtolower($status_filter));

    // Get filtered applications
    $applications = getApplications($conn, $project_id, $normalized_status);
    
    if (count($applications) > 0) {
        foreach ($applications as $app) {
            echo generateApplicationCard($app);
        }
    } else {
        echo '<div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-person-check"></i>
                    <h4>No Applications Found</h4>
                    <p class="text-muted">No applications match the selected status.<br>Try filtering by a different status.</p>
                </div>
              </div>';
    }
    exit();
}

// Get initial applications data
if (isset($_POST['action']) && $_POST['action'] == 'get_initial') {
    $applications = getApplications($conn, $project_id);
    
    if (count($applications) > 0) {
        foreach ($applications as $app) {
            echo generateApplicationCard($app);
        }
    } else {
        echo '<div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No Applications Received Yet</h4>
                    <p class="text-muted">You haven\'t received any applications for your listings yet.<br>Keep an eye out for new talent!</p>
                </div>
              </div>';
    }
    exit();
}

function getApplications($conn, $project_id, $status_filter = null) {
    // Base SQL with skills join
    $sql = "SELECT a.*, l.job_title, l.location as job_location, l.salary, l.job_type, l.category, l.description, l.slug,
                   tp.talent_name, tp.bio, tp.location as talent_location, tp.cv_url, tp.profile_picture, tp.slug as talent_slug,
                   GROUP_CONCAT(s.skill_name SEPARATOR ', ') as skills
            FROM applications a 
            JOIN listings l ON a.listing_id = l.listing_id 
            LEFT JOIN talent_profiles tp ON a.talent_id = tp.talent_id
            LEFT JOIN talent_skills ts ON tp.talent_id = ts.talent_id
            LEFT JOIN skills s ON ts.skill_id = s.skill_id
            WHERE l.project_id = ?";

    $params = [$project_id];
    $types = 'i';

    // Add status filter if provided and not "all"
    if ($status_filter && $status_filter !== 'all') {
        $sql .= " AND LOWER(TRIM(a.status)) = ?";
        $params[] = $status_filter;
        $types .= 's';
    }

    $sql .= " GROUP BY a.application_id ORDER BY a.application_date DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $applications;
}

function generateApplicationCard($app) {
    $status_class = '';
    $status_badge_class = '';
    $button_html = '';

    switch($app['status']) {
        case 'Accepted':
            $status_class = 'text-success';
            $status_badge_class = 'bg-success';
            $button_html = '<span class="badge bg-success">Accepted</span>';
            break;
        case 'Rejected':
            $status_class = 'text-danger';
            $status_badge_class = 'bg-danger';
            $button_html = '<span class="badge bg-danger">Rejected</span>';
            break;
        case 'Pending':
            $status_class = 'text-warning';
            $status_badge_class = 'bg-warning text-dark';
            $button_html = '
                <button class="btn btn-success btn-sm approve-btn" data-id="' . $app['application_id'] . '">Accept</button>
                <button class="btn btn-danger btn-sm decline-btn ms-1" data-id="' . $app['application_id'] . '">Reject</button>
            ';
            break;
    }

    $salary_display = $app['salary'] ? '$' . number_format($app['salary']) : 'Not specified';
    $profile_picture = $app['profile_picture'] ? '../' . $app['profile_picture'] : '../images/resources/default.jpg';
    $cv_link = $app['cv_url'] ? '<a href="../' . $app['cv_url'] . '" target="_blank" class="btn btn-primary btn-sm">View CV</a>' : '<small class="text-muted">No CV uploaded</small>';
    $bio_snippet = substr($app['bio'], 0, 120) . (strlen($app['bio']) > 120 ? '...' : '');

    $t_name = $app['talent_name'];
    $t_slug = $app['talent_slug'];
    $talent_link = $t_slug 
        ? '<a href="/pursue/projects/discover/' . $t_slug . '" class="text-decoration-none text-white">' . ($t_name) . '</a>' 
        : '<span class="text-white">' . ($t_name) . '</span>';

    $skills_display = $app['skills'] ? ($app['skills']) : 'No skills listed';

    return '
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="application-card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center mb-2">
                    <img src="' . ($profile_picture) . '" alt="Profile" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #F97D37;">
                    <div>
                        <h5 class="card-title mb-0">' . $talent_link . '</h5>
                        <p class="card-location mb-0"><i class="bi bi-geo-alt me-1"></i>' . ($app['talent_location']) . '</p>
                    </div>
                </div>
                <hr style="border-color: #333; margin: 0.5rem 0 1rem 0;">
                <h6 class="text-primary mt-2">Applied for: ' . ($app['job_title']) . '</h6>
                <p class="card-location"><small>' . ($app['job_location']) . ' • ' . ($app['job_type']) . '</small></p>
            </div>
            <div class="card-content">
                <strong>Bio:</strong>
                <p class="card-description">' . ($bio_snippet) . '</p>
                <strong>Skills:</strong>
                <p class="card-description">' . $skills_display . '</p>
                <div class="mb-3">
                    ' . $cv_link . '
                </div>
                <p class="card-details"><strong>Applied:</strong> ' . date('M j, Y', strtotime($app['application_date'])) . '</p>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="status-badge ' . $status_badge_class . '">
                    <i class="bi bi-circle-fill me-1"></i>' . ($app['status']) . '
                </span>
                <div class="action-buttons" data-app-id="' . $app['application_id'] . '">
                    ' . $button_html . '
                </div>
            </div>
        </div>
    </div>';
}

// Get all applications for initial page load (if not handling AJAX)
if (!isset($_POST['action'])) {
    $applications = getApplications($conn, $project_id);
}
?>