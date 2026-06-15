<?php
/**
 * Helper Functions for Job Portal Backend
 */

/**
 * Sanitize input string
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate required fields
 */
function validateRequiredFields($fields, $data = null) {
    $data = $data ?? $_POST;
    $missing = [];
    
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    return $missing;
}

/**
 * Get HTTP response code
 */
function setResponseCode($code) {
    http_response_code($code);
}

/**
 * Send JSON response
 */
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit();
}

/**
 * Check authentication
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(false, 'Unauthorized', null, 401);
    }
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate unique ID
 */
function generateUniqueId() {
    return uniqid('job_', true);
}

/**
 * Paginate results
 */
function paginate($total, $page, $limit) {
    $totalPages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;
    
    return [
        'offset' => $offset,
        'limit' => $limit,
        'page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total
    ];
}

/**
 * Get pagination query
 */
function getPaginationQuery($page = 1, $limit = 10) {
    $page = max(1, intval($page));
    $limit = max(1, min(100, intval($limit)));
    $offset = ($page - 1) * $limit;
    
    return "LIMIT $limit OFFSET $offset";
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Check file upload
 */
function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload error'];
    }
    
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file_size > $max_size) {
        return ['valid' => false, 'message' => 'File size exceeds limit'];
    }
    
    if (!empty($allowed_types) && !in_array($file_ext, $allowed_types)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }
    
    return ['valid' => true, 'extension' => $file_ext];
}

/**
 * Log error
 */
function logError($message, $file = null) {
    $log_file = '../logs/error.log';
    
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] Error: $message";
    
    if ($file) {
        $log_message .= " in $file";
    }
    
    $log_message .= "\n";
    
    error_log($log_message, 3, $log_file);
}

/**
 * Get client IP
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ?: 'Unknown';
}

/**
 * Rate limiting check
 */
function checkRateLimit($key, $limit = 10, $period = 60) {
    $cache_file = '../cache/' . md5($key) . '.cache';
    
    if (!is_dir('../cache')) {
        mkdir('../cache', 0755, true);
    }
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        
        if (time() - $data['time'] < $period && $data['count'] >= $limit) {
            return false; // Rate limited
        }
        
        if (time() - $data['time'] < $period) {
            $data['count']++;
        } else {
            $data['count'] = 1;
            $data['time'] = time();
        }
    } else {
        $data = ['count' => 1, 'time' => time()];
    }
    
    file_put_contents($cache_file, json_encode($data));
    return true;
}

/**
 * Get request body (JSON or Form)
 */
function getRequestBody() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/json') !== false) {
            return json_decode(file_get_contents('php://input'), true);
        }
    }
    
    return $_POST;
}

/**
 * Generate JWT Token (Basic)
 */
function generateToken($user_id, $secret = 'your-secret-key', $expiration = 3600) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user_id,
        'iat' => time(),
        'exp' => time() + $expiration
    ]);
    
    $header_encoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
    $payload_encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    $signature_encoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    return "$header_encoded.$payload_encoded.$signature_encoded";
}

/**
 * Verify JWT Token (Basic)
 */
function verifyToken($token, $secret = 'your-secret-key') {
    list($header_encoded, $payload_encoded, $signature_encoded) = explode('.', $token);
    
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    $signature_encoded_verify = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    if ($signature_encoded !== $signature_encoded_verify) {
        return false;
    }
    
    $payload = json_decode(base64_decode(strtr($payload_encoded, '-_', '+/')), true);
    
    if ($payload['exp'] < time()) {
        return false; // Token expired
    }
    
    return $payload;
}
?>
