# Job Portal Backend - Installation Steps

## 📋 Prerequisites Checklist

Before starting, make sure you have:
- ✅ XAMPP installed
- ✅ Apache installed (comes with XAMPP)
- ✅ MySQL installed (comes with XAMPP)
- ✅ PHP 7.4+ (comes with XAMPP)

---

## 🚀 Installation Steps

### Step 1: Download & Copy Project
1. Place `job_portal_be` folder in: `C:\xampp\htdocs\job_portal_be`
2. Folder path should be: `C:\xampp\htdocs\job_portal_be\index.php`

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Wait for both to show as "Running" (green color)

### Step 3: Create Database

#### Method 1: Using phpMyAdmin (Recommended)
1. Open browser: `http://localhost/phpmyadmin`
2. Click "Import" tab (top navigation)
3. Click "Choose File" button
4. Select `job_portal_be/database.sql`
5. Scroll down and click "Go"
6. Wait for "Import has been completed" message
7. Check if `job_portal_db` appears in left sidebar

#### Method 2: Using SQL Tab
1. Open browser: `http://localhost/phpmyadmin`
2. Click "SQL" tab
3. Open file `job_portal_be/database.sql` with notepad
4. Copy all content
5. Paste in the SQL text area
6. Click "Go" button
7. Wait for success message

#### Method 3: Using Command Line
1. Open Command Prompt as Administrator
2. Navigate to XAMPP MySQL bin folder:
   ```cmd
   cd C:\xampp\mysql\bin
   ```
3. Login to MySQL:
   ```cmd
   mysql -u root
   ```
4. Run the SQL file:
   ```sql
   SOURCE C:\xampp\htdocs\job_portal_be\database.sql;
   ```

### Step 4: Verify Installation

1. Open browser: `http://localhost/job_portal_be/`
2. Should see JSON response with API endpoints
3. If you see an error, check:
   - Apache is running
   - MySQL is running
   - Files are in correct folder

### Step 5: Test the Backend

#### Option A: Using API Tester (Recommended)
1. Open: `http://localhost/job_portal_be/api-tester.html`
2. Test Login with default credentials
3. Try adding a job
4. Try searching for jobs

#### Option B: Using Postman
1. Install Postman from `https://www.postman.com/downloads/`
2. Create new POST request
3. URL: `http://localhost/job_portal_be/admin/backend/login.php`
4. Body (form-data):
   - email: `admin@jobportal.com`
   - password: `admin123`
5. Send and check response

---

## 📁 Files Created

```
job_portal_be/
├── index.php                     # API home page
├── api-tester.html              # Browser-based API testing tool
├── database.sql                 # Database schema
├── README.md                    # Full documentation
├── QUICKSTART.md               # Quick start guide
├── INSTALL.md                  # This file
├── .htaccess                   # URL rewriting rules
├── .gitignore                  # Git ignore file
│
├── admin/
│   ├── backend/
│   │   ├── config.php          # Database configuration
│   │   ├── helpers.php         # Utility functions
│   │   ├── login.php           # Admin authentication
│   │   ├── jobs.php            # Job management API
│   │   ├── applications.php    # Application management API
│   │   └── dashboard.php       # Dashboard statistics API
│   └── frontend/               # Admin UI files (existing)
│
└── portal-client/
    ├── backend/
    │   ├── config.php          # Database configuration
    │   ├── jobs.php            # Job listing & search API
    │   └── candidates.php      # Candidate registration & application API
    └── frontend/               # Job seeker UI files (existing)
```

---

## 🔐 Default Credentials

**Admin Login:**
- Email: `admin@jobportal.com`
- Password: `admin123`

---

## 🧪 Testing URLs

After completing setup, test these URLs in browser:

1. **API Home**: `http://localhost/job_portal_be/`
2. **API Tester**: `http://localhost/job_portal_be/api-tester.html`
3. **Admin Login**: `http://localhost/job_portal_be/admin/frontend/login.html`
4. **Job Portal**: `http://localhost/job_portal_be/portal-client/frontend/index.html`

---

## 🐛 Common Issues & Solutions

| Problem | Solution |
|---------|----------|
| Database connection failed | Check MySQL is running. Verify credentials in config.php |
| Can't see phpMyAdmin | Make sure Apache is running |
| 404 error on API calls | Check files are in C:\xampp\htdocs\job_portal_be\ |
| Login shows blank page | Check browser console for errors (F12) |
| Database.sql won't import | Ensure MySQL is running. Try command line method |

---

## 📝 Next Steps

1. ✅ Test all APIs using api-tester.html
2. ✅ Connect frontend files to backend APIs
3. ✅ Test job creation and application workflows
4. ✅ Add more admin users if needed
5. ✅ Configure SMTP for email notifications (optional)

---

## 🆘 Need Help?

If something doesn't work:
1. Check browser console (F12)
2. Check XAMPP logs
3. Check network tab in browser dev tools
4. Verify all folder paths match exactly

---

## ✅ Installation Complete!

Your Job Portal backend is ready to use. Start with the API Tester to verify everything works correctly.

**Happy Coding! 🎉**
