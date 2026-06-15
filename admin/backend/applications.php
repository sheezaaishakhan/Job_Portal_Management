<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listApplications();
        break;
    case 'update_status':
        if ($method === 'POST') {
            updateApplicationStatus();
        }
        break;
    case 'get':
        getApplication();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function listApplications() {
    global $conn;
    
    $job_id = $_GET['job_id'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $query = "SELECT a.id, a.job_id, a.candidate_id, j.title as job_title, c.name, c.email, c.phone, a.status, a.applied_date 
              FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              JOIN candidates c ON a.candidate_id = c.id 
              WHERE 1=1";
    
    if ($job_id) {
        $job_id = intval($job_id);
        $query .= " AND a.job_id = $job_id";
    }
    
    if ($status) {
        $status = $conn->real_escape_string($status);
        $query .= " AND a.status = '$status'";
    }
    
    $query .= " ORDER BY a.applied_date DESC";
    
    $result = $conn->query($query);
    $applications = [];
    
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $applications
    ]);
}

function updateApplicationStatus() {
    global $conn;
    
    $application_id = intval($_POST['application_id'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? '');
    
    if (!$application_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    $valid_statuses = ['Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Accepted'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    $query = "UPDATE applications SET status = '$status', updated_at = CURRENT_TIMESTAMP WHERE id = $application_id";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Application status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function getApplication() {
    global $conn;
    
    $application_id = intval($_GET['id'] ?? 0);
    
    if (!$application_id) {
        echo json_encode(['success' => false, 'message' => 'Application ID required']);
        return;
    }
    
    $query = "SELECT a.*, j.title as job_title, c.* FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              JOIN candidates c ON a.candidate_id = c.id 
              WHERE a.id = $application_id";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'data' => $result->fetch_assoc()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
    }
}
?>
