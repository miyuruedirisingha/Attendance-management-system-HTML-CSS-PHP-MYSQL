# Attendance Management System

A complete web-based attendance management system built with HTML, CSS, PHP, and MySQL. Fully containerized with Docker for easy deployment on any platform including AWS EC2.

## Features

- **User Authentication**: Secure login system with session management
- **Dashboard**: Overview with statistics and recent attendance
- **Student Management**: Add, edit, delete, and view students
- **Class Management**: Organize students by classes and sections
- **Mark Attendance**: Easy-to-use interface for marking daily attendance
- **Attendance Reports**: View detailed reports and export to CSV
- **Responsive Design**: Works on desktop and mobile devices
- **🐳 Docker Support**: One-command deployment with Docker Compose
- **☁️ Cloud Ready**: Pre-configured for AWS EC2 deployment

## 🚀 Quick Start with Docker (Recommended)

### Prerequisites
- Docker & Docker Compose installed ([Get Docker](https://docs.docker.com/get-docker/))

### Deployment Steps

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd Attendance-management-system-HTML-CSS-PHP-MYSQL

# 2. Configure environment
cp .env.example .env
# Edit .env with your secure passwords

# 3. Deploy
docker-compose up -d
```

**Access your application:**
- 🌐 Web Application: http://localhost
- 🗄️ PhpMyAdmin: http://localhost:8080
- 👤 Default Login: `admin` / `admin123`

## 📦 Deployment Options

### Option 1: Docker on Local Machine
Perfect for development and testing
```bash
docker-compose up -d
```

### Option 2: Docker on AWS EC2 (Production)
Deploy to AWS EC2 with Docker
```bash
# One-time EC2 setup (installs Docker)
./ec2-setup.sh

# Deploy/redeploy application
./ec2-deploy.sh
```
📚 **Full Guide**: [EC2-DEPLOYMENT-GUIDE.md](EC2-DEPLOYMENT-GUIDE.md)

### Option 3: Traditional XAMPP Setup
For local development without Docker

**Prerequisites**: XAMPP (PHP 7.4+ and MySQL)

1. **Copy Files**
   - Copy folder to `C:\xampp\htdocs\`

2. **Create Database**
   - Start Apache and MySQL in XAMPP
   - Import `database.sql` via phpMyAdmin

3. **Access**
   - http://localhost/Attendance%20Management%20System/
   - Login: `admin` / `admin123`

## File Structure

```
Attendance Management System/
├── config.php              # Database configuration
├── login.php               # Login page
├── logout.php              # Logout handler
├── index.php               # Dashboard
├── students.php            # Student management
├── classes.php             # Class management
├── mark_attendance.php     # Mark attendance
├── view_attendance.php     # View reports
├── export_attendance.php   # CSV export
├── database.sql            # Database schema
├── css/
│   └── style.css          # Main stylesheet
└── includes/
    └── header.php         # Navigation header
```

## Default Database Structure

### Tables:
- **users**: Admin/teacher login accounts
- **classes**: Class information (Class 10-A, 10-B, etc.)
- **students**: Student records with roll numbers
- **attendance**: Daily attendance records

## Usage Guide

### 1. Managing Students
- Go to "Students" page
- Fill the form to add new students
- Edit or delete existing students
- Students are linked to classes

### 2. Managing Classes
- Go to "Classes" page
- Add classes with name and section
- View total students per class

### 3. Marking Attendance
- Go to "Mark Attendance" page
- Select date and class
- Mark status for each student (Present, Absent, Late, Excused)
- Add optional remarks
- Use quick buttons to mark all present/absent

### 4. Viewing Reports
- Go to "View Reports" page
- Select class and date range
- View summary with attendance percentages
- View detailed daily records
- Export to CSV for further analysis

## Default Login Credentials

- **Username**: admin
- **Password**: admin123

⚠️ **Important**: Change the default password after first login for security!

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: Apache (via XAMPP)

## Browser Support

- Chrome (recommended)
- Firefox
- Edge
- Safari

## Security Notes

- Passwords are hashed using PHP's password_hash()
- SQL injection protection using mysqli_real_escape_string()
- Session-based authentication
- Login required for all pages except login page

## Troubleshooting

### Cannot connect to database
- Ensure MySQL is running in XAMPP
- Check database name, username, and password in config.php

### Page not found
- Check the URL path matches your folder name
- Ensure files are in htdocs folder

### Styling not loading
- Clear browser cache
- Check css/style.css file exists

## Future Enhancements

- Email notifications for low attendance
- SMS integration
- Biometric integration
- Mobile app
- Advanced reporting with charts
- Parent portal
- Bulk import via Excel

## License

Free to use for educational purposes.

## Support

For issues or questions, modify the code as needed for your requirements.
