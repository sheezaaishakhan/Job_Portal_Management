<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listJobs();
        break;
    case 'get':
        getJob();
        break;
    case 'search':
        searchJobs();
        break;
    default:
        listJobs();
}

function listJobs() {
    global $conn;
    
    $page = intval($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM jobs WHERE status = 'Active'";
    $countResult = $conn->query($countQuery);
    $total = $countResult->fetch_assoc()['total'];
    
    $query = "SELECT id, title, description, company_name, location, salary_min, salary_max, job_type, experience_required, posted_date 
              FROM jobs 
              WHERE status = 'Active' 
              ORDER BY posted_date DESC 
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    $jobs = [];
    
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $jobs,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
}

function getJob() {
    global $conn;
    
    $job_id = intval($_GET['id'] ?? 0);
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Job ID required']);
        return;
    }
    
    $query = "SELECT * FROM jobs WHERE id = $job_id AND status = 'Active'";
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

function searchJobs() {
    global $conn;
    
    $keyword = $conn->real_escape_string($_GET['keyword'] ?? '');
    $location = $conn->real_escape_string($_GET['location'] ?? '');
    $job_type = $conn->real_escape_string($_GET['job_type'] ?? '');
    
    $query = "SELECT id, title, description, company_name, location, salary_min, salary_max, job_type, experience_required, posted_date 
              FROM jobs 
              WHERE status = 'Active'";
    
    if ($keyword) {
        $query .= " AND (title LIKE '%$keyword%' OR description LIKE '%$keyword%' OR company_name LIKE '%$keyword%')";
    }
    
    if ($location) {
        $query .= " AND location LIKE '%$location%'";
    }
    
    if ($job_type) {
        $query .= " AND job_type = '$job_type'";
    }
    
    $query .= " ORDER BY posted_date DESC";
    
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
?>
