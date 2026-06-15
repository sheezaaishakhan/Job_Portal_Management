# Job Portal Backend - Setup Guide

## Overview
This is a complete PHP backend for a job portal with admin panel and job seeker portal.

## Requirements
- XAMPP (PHP, MySQL)
- Apache Server
- MySQL Database

## Database Setup

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Click "Start" for Apache and MySQL

### Step 2: Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click on "SQL" tab or use the "New" button
3. Paste the contents of `database.sql` file
4. Click "Go" to execute

**OR** Import SQL file:
1. Go to phpMyAdmin
2. Click "Import" tab
3. Select `database.sql` file
4. Click "Go"

### Default Admin Login
- **Email:** admin@jobportal.com
- **Password:** admin123

## File Structure

```
job_portal_be/
├── database.sql                    # Database schema
├── admin/
│   └── backend/
│       ├── config.php             # Database configuration
│       ├── login.php              # Admin login
│       ├── jobs.php               # Job management
│       ├── applications.php        # Application management
│       └── dashboard.php          # Dashboard statistics
└── portal-client/
    └── backend/
        ├── config.php             # Database configuration
        ├── jobs.php               # List and search jobs
        └── candidates.php         # Candidate registration & application
```

## API Endpoints

### Admin Panel APIs

#### Login
```
POST admin/backend/login.php
Body: email, password
```

#### Jobs Management
```
GET admin/backend/jobs.php?action=list&status=Active
GET admin/backend/jobs.php?action=get&id=1
POST admin/backend/jobs.php?action=add
POST admin/backend/jobs.php?action=edit
POST admin/backend/jobs.php?action=delete
```

#### Applications Management
```
GET admin/backend/applications.php?action=list&job_id=1
POST admin/backend/applications.php?action=update_status
GET admin/backend/applications.php?action=get&id=1
```

#### Dashboard
```
GET admin/backend/dashboard.php
```

### Portal Client APIs

#### Jobs
```
GET portal-client/backend/jobs.php?action=list&page=1
GET portal-client/backend/jobs.php?action=get&id=1
GET portal-client/backend/jobs.php?action=search&keyword=php&location=Karachi&job_type=Full-time
```

#### Candidates & Applications
```
POST portal-client/backend/candidates.php?action=register
POST portal-client/backend/candidates.php?action=apply
GET portal-client/backend/candidates.php?action=check_application&job_id=1&candidate_id=1
```

## Configuration

### Database Connection
Edit the config.php files to change database settings:
```php
define('DB_HOST', 'localhost');  // Database host
define('DB_USER', 'root');       // Database user
define('DB_PASS', '');           // Database password
define('DB_NAME', 'job_portal_db');  // Database name
```

## Testing with Frontend

### Admin Panel Testing
1. Navigate to: `admin/frontend/login.html`
2. Use email: `admin@jobportal.com` and password: `admin123`
3. Access dashboard, add jobs, manage applications

### Job Seeker Portal Testing
1. Navigate to: `portal-client/frontend/index.html`
2. Browse available jobs
3. Register as candidate and apply for jobs

## Project Status
✅ Complete backend structure created
✅ Database schema with all tables
✅ Admin panel APIs
✅ Job seeker portal APIs
✅ Authentication system
✅ Job management system
✅ Application tracking system
✅ Dashboard statistics

## Features Implemented
- **Admin Panel**
  - User authentication
  - Job posting and management
  - View and manage applications
  - Dashboard with statistics
  - Job status tracking (Active/Inactive/Closed)

- **Job Seeker Portal**
  - Browse active jobs
  - Search and filter jobs
  - Candidate registration
  - Apply for jobs
  - Track application status

## File Upload
- Resume uploads are stored in `uploads/resumes/` directory
- Allowed formats: PDF, DOC, DOCX
- Ensure `uploads/` folder exists and has write permissions

## Security Notes
1. Use HTTPS in production
2. Implement input validation on frontend
3. Use prepared statements for sensitive operations
4. Hash passwords properly before storing
5. Implement rate limiting for login attempts

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify database name matches `DB_NAME` in config.php
- Ensure user credentials are correct

### API Not Responding
- Check Apache is running
- Verify file paths are correct
- Check browser console for errors

### File Upload Issues
- Create `uploads/resumes/` directory manually
- Set folder permissions to 755
- Check file size limits in php.ini

## Next Steps
1. Connect frontend HTML files with backend APIs
2. Implement JWT tokens for better security
3. Add email notifications
4. Implement pagination for better performance
5. Add role-based access control
6. Add more validation rules
7. Implement file compression for uploads
