# Quick Start Guide - Job Portal Backend

## ⚡ Setup in 5 Minutes

### 1️⃣ Start XAMPP
- Open XAMPP Control Panel
- Click "Start" Apache and MySQL modules
- Verify they show as "Running" (green)

### 2️⃣ Copy Project to htdocs
```
Copy job_portal_be folder to:
C:\xampp\htdocs\job_portal_be
```

### 3️⃣ Create Database
1. Open browser: http://localhost/phpmyadmin
2. Click on "SQL" tab (top menu)
3. Open `job_portal_be/database.sql` with notepad
4. Copy ALL content and paste in phpMyAdmin SQL tab
5. Click "Go" button to create database

**Alternative Method:**
1. In phpMyAdmin, right-click left sidebar → Create new database
2. Name: `job_portal_db`
3. Click "Create"
4. Go to SQL tab and paste database.sql content

### 4️⃣ Test Backend is Working
- Open: http://localhost/job_portal_be/
- Should see JSON with API endpoints

### 5️⃣ Access Admin Panel
1. Open: `http://localhost/job_portal_be/admin/frontend/login.html`
2. Login:
   - Email: `admin@jobportal.com`
   - Password: `admin123`

---

## 📁 Project Structure

```
job_portal_be/
├── index.php                        # Test API endpoint
├── database.sql                     # Database schema
├── README.md                        # Full documentation
├── QUICKSTART.md                    # This file
├── admin/
│   ├── backend/
│   │   ├── config.php              # DB configuration
│   │   ├── login.php               # Admin login
│   │   ├── jobs.php                # Job management
│   │   ├── applications.php        # Application tracking
│   │   └── dashboard.php           # Stats & dashboard
│   └── frontend/                   # Admin UI files
└── portal-client/
    ├── backend/
    │   ├── config.php              # DB configuration
    │   ├── jobs.php                # Job listings
    │   └── candidates.php          # Candidate registration
    └── frontend/                   # Job seeker UI files
```

---

## 🔌 API Testing

### Test Admin Login
```bash
URL: http://localhost/job_portal_be/admin/backend/login.php
Method: POST
Body (Form Data):
  email: admin@jobportal.com
  password: admin123
```

### Get All Jobs
```bash
URL: http://localhost/job_portal_be/admin/backend/jobs.php?action=list
Method: GET
```

### Get Active Jobs (Public)
```bash
URL: http://localhost/job_portal_be/portal-client/backend/jobs.php?action=list
Method: GET
```

### Search Jobs
```bash
URL: http://localhost/job_portal_be/portal-client/backend/jobs.php?action=search?keyword=php&location=Karachi
Method: GET
```

---

## 🎯 Next: Connect Frontend Files

The frontend files already exist. You need to:

1. **Update login.html** - Connect to login API
2. **Update add-job.html** - Connect to job creation API
3. **Update jobs.html** - Connect to job listing API
4. **Update apply.html** - Connect to job application API

Example JavaScript to connect:
```javascript
// Login
fetch('/job_portal_be/admin/backend/login.php', {
    method: 'POST',
    body: new FormData(form)
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## 📋 Database Tables Created

✅ `users` - Admin users  
✅ `jobs` - Job listings  
✅ `candidates` - Job seekers  
✅ `applications` - Job applications  

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't access phpMyAdmin | Verify MySQL is running in XAMPP |
| 404 error on backend | Check if project is in C:\xampp\htdocs\ |
| Database connection failed | Verify `job_portal_db` was created |
| Login not working | Check credentials: admin@jobportal.com / admin123 |
| File upload not working | Create `uploads/resumes/` folder manually |

---

## 🔐 Security Tips
- Change default admin password after first login
- Use HTTPS in production
- Validate all inputs on frontend & backend
- Store sensitive configs outside web root

---

## 📞 Support
Refer to README.md for detailed API documentation and features.

---

**Your backend is READY! 🚀**
