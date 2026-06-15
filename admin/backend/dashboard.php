<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$stats = [
    'total_jobs' => 0,
    'active_jobs' => 0,
    'total_applications' => 0,
    'pending_applications' => 0,
    'total_candidates' => 0,
    'recent_jobs' => [],
    'recent_applications' => []
];

// Total Jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobs");
$stats['total_jobs'] = $result->fetch_assoc()['count'];

// Active Jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'Active'");
$stats['active_jobs'] = $result->fetch_assoc()['count'];

// Total Applications
$result = $conn->query("SELECT COUNT(*) as count FROM applications");
$stats['total_applications'] = $result->fetch_assoc()['count'];

// Pending Applications
$result = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'Pending'");
$stats['pending_applications'] = $result->fetch_assoc()['count'];

// Total Candidates
$result = $conn->query("SELECT COUNT(*) as count FROM candidates");
$stats['total_candidates'] = $result->fetch_assoc()['count'];

// Recent Jobs
$result = $conn->query("SELECT id, title, company_name, status, created_at FROM jobs ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $stats['recent_jobs'][] = $row;
}

// Recent Applications
$result = $conn->query("SELECT a.id, j.title, c.name, a.status, a.applied_date FROM applications a 
                        JOIN jobs j ON a.job_id = j.id 
                        JOIN candidates c ON a.candidate_id = c.id 
                        ORDER BY a.applied_date DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $stats['recent_applications'][] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $stats
]);
?>
