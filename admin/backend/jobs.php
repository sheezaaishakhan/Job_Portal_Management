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
        listJobs();
        break;
    case 'add':
        if ($method === 'POST') {
            addJob();
        }
        break;
    case 'edit':
        if ($method === 'POST') {
            editJob();
        }
        break;
    case 'delete':
        if ($method === 'POST') {
            deleteJob();
        }
        break;
    case 'get':
        getJob();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function listJobs() {
    global $conn;
    $status = $_GET['status'] ?? '';
    
    $query = "SELECT * FROM jobs WHERE 1=1";
    
    if ($status) {
        $status = $conn->real_escape_string($status);
        $query .= " AND status = '$status'";
    }
    
    $query .= " ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $jobs
    ]);
}

function addJob() {
    global $conn;
    
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $company_name = $conn->real_escape_string($_POST['company_name'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $salary_min = intval($_POST['salary_min'] ?? 0);
    $salary_max = intval($_POST['salary_max'] ?? 0);
    $job_type = $conn->real_escape_string($_POST['job_type'] ?? '');
    $experience_required = $conn->real_escape_string($_POST['experience_required'] ?? '');
    $skills_required = $conn->real_escape_string($_POST['skills_required'] ?? '');
    $deadline = $conn->real_escape_string($_POST['deadline'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (!$title || !$description || !$company_name) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    $query = "INSERT INTO jobs (title, description, company_name, location, salary_min, salary_max, job_type, experience_required, skills_required, deadline, created_by) 
              VALUES ('$title', '$description', '$company_name', '$location', $salary_min, $salary_max, '$job_type', '$experience_required', '$skills_required', '$deadline', $user_id)";
    
    if ($conn->query($query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Job posted successfully',
            'job_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function editJob() {
    global $conn;
    
    $job_id = intval($_POST['job_id'] ?? 0);
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID required']);
        return;
    }
    
    $updates = [];
    $allowed_fields = ['title', 'description', 'company_name', 'location', 'salary_min', 'salary_max', 'job_type', 'experience_required', 'skills_required', 'deadline', 'status'];
    
    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $value = $conn->real_escape_string($_POST[$field]);
            $updates[] = "$field = '$value'";
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }
    
    $query = "UPDATE jobs SET " . implode(', ', $updates) . ", updated_at = CURRENT_TIMESTAMP WHERE id = $job_id";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function deleteJob() {
    global $conn;
    
    $job_id = intval($_POST['job_id'] ?? 0);
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID required']);
        return;
    }
    
    $query = "DELETE FROM jobs WHERE id = $job_id";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function getJob() {
    global $conn;
    
    $job_id = intval($_GET['id'] ?? 0);
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID required']);
        return;
    }
    
    $query = "SELECT * FROM jobs WHERE id = $job_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'data' => $result->fetch_assoc()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
    }
}
?>
