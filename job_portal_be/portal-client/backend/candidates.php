<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($method === 'POST') {
            registerCandidate();
        }
        break;
    case 'apply':
        if ($method === 'POST') {
            applyJob();
        }
        break;
    case 'check_application':
        checkApplication();
        break;
    case 'get_by_email':
        getCandidateByEmail();
        break;
    case 'my_applications':
        listCandidateApplications();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function registerCandidate() {
    global $conn;
    
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $education = $conn->real_escape_string($_POST['education'] ?? '');
    $experience = $conn->real_escape_string($_POST['experience'] ?? '');
    
    if (!$name || !$email) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        return;
    }
    
    // Check if email already exists
    $checkQuery = "SELECT id FROM candidates WHERE email = '$email'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $candidate = $checkResult->fetch_assoc();
        echo json_encode([
            'success' => true,
            'message' => 'Candidate already exists',
            'candidate_id' => $candidate['id'],
            'is_new' => false
        ]);
        return;
    }
    
    // Handle resume upload
    $resume_path = '';
    if (isset($_FILES['resume'])) {
        $file = $_FILES['resume'];
        $upload_dir = '../../uploads/resumes/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($file['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['pdf', 'doc', 'docx'];
        
        if (in_array(strtolower($file_ext), $allowed_ext)) {
            $new_file_name = time() . '_' . $email . '.' . $file_ext;
            $resume_path = 'resumes/' . $new_file_name;
            
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_file_name)) {
                echo json_encode(['success' => false, 'message' => 'Error uploading resume']);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, DOCX allowed']);
            return;
        }
    }
    
    $query = "INSERT INTO candidates (name, email, phone, city, education, experience, resume_path) 
              VALUES ('$name', '$email', '$phone', '$city', '$education', '$experience', '$resume_path')";
    
    if ($conn->query($query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Candidate registered successfully',
            'candidate_id' => $conn->insert_id,
            'is_new' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function applyJob() {
    global $conn;
    
    $job_id = intval($_POST['job_id'] ?? 0);
    $candidate_id = intval($_POST['candidate_id'] ?? 0);
    
    if (!$job_id || !$candidate_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID and Candidate ID are required']);
        return;
    }
    
    // Check if job exists
    $jobQuery = "SELECT id FROM jobs WHERE id = $job_id AND status = 'Active'";
    if ($conn->query($jobQuery)->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Job not found or inactive']);
        return;
    }
    
    // Check if candidate already applied
    $checkQuery = "SELECT id FROM applications WHERE job_id = $job_id AND candidate_id = $candidate_id";
    if ($conn->query($checkQuery)->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
        return;
    }
    
    $query = "INSERT INTO applications (job_id, candidate_id, status) VALUES ($job_id, $candidate_id, 'Pending')";
    
    if ($conn->query($query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}

function checkApplication() {
    global $conn;
    
    $job_id = intval($_GET['job_id'] ?? 0);
    $candidate_id = intval($_GET['candidate_id'] ?? 0);
    
    if (!$job_id || !$candidate_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID and Candidate ID are required']);
        return;
    }
    
    $query = "SELECT id, status, applied_date FROM applications WHERE job_id = $job_id AND candidate_id = $candidate_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'applied' => true,
            'application' => $application
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'applied' => false
        ]);
    }
}

function getCandidateByEmail() {
    global $conn;

    $email = $conn->real_escape_string($_GET['email'] ?? '');
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        return;
    }

    $query = "SELECT id, name, email, phone FROM candidates WHERE email = '$email' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo json_encode(['success' => true, 'candidate' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Candidate not found']);
    }
}

function listCandidateApplications() {
    global $conn;

    $candidate_id = intval($_GET['candidate_id'] ?? 0);
    if (!$candidate_id) {
        echo json_encode(['success' => false, 'message' => 'Candidate ID is required']);
        return;
    }

    $query = "SELECT a.id, a.job_id, j.title AS job_title, a.status, a.applied_date
              FROM applications a
              JOIN jobs j ON a.job_id = j.id
              WHERE a.candidate_id = $candidate_id
              ORDER BY a.applied_date DESC";

    $result = $conn->query($query);
    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $applications]);
}
?>
