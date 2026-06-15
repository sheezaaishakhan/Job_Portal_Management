<?php
// Job Portal Backend - API Index
header('Content-Type: application/json');

$api_endpoints = [
    'admin' => [
        'login' => 'POST admin/backend/login.php',
        'dashboard' => 'GET admin/backend/dashboard.php',
        'jobs' => [
            'list' => 'GET admin/backend/jobs.php?action=list',
            'get' => 'GET admin/backend/jobs.php?action=get&id=1',
            'add' => 'POST admin/backend/jobs.php?action=add',
            'edit' => 'POST admin/backend/jobs.php?action=edit',
            'delete' => 'POST admin/backend/jobs.php?action=delete'
        ],
        'applications' => [
            'list' => 'GET admin/backend/applications.php?action=list',
            'update_status' => 'POST admin/backend/applications.php?action=update_status',
            'get' => 'GET admin/backend/applications.php?action=get&id=1'
        ]
    ],
    'portal' => [
        'jobs' => [
            'list' => 'GET portal-client/backend/jobs.php?action=list',
            'get' => 'GET portal-client/backend/jobs.php?action=get&id=1',
            'search' => 'GET portal-client/backend/jobs.php?action=search&keyword=php'
        ],
        'candidates' => [
            'register' => 'POST portal-client/backend/candidates.php?action=register',
            'apply' => 'POST portal-client/backend/candidates.php?action=apply',
            'check_application' => 'GET portal-client/backend/candidates.php?action=check_application'
        ]
    ]
];

echo json_encode([
    'status' => 'Backend is running',
    'version' => '1.0',
    'database' => 'job_portal_db',
    'endpoints' => $api_endpoints
], JSON_PRETTY_PRINT);
?>
