# EMIS - Education Management & Information System

A comprehensive, open-source web-based platform for managing all aspects of educational institutions. EMIS streamlines operations across multiple user roles including administrators, teachers, students, and parents.

## Features

- **Multi-Role Authentication System**: Secure role-based access for Admins, Teachers, Students, and Parents
- **Student Management**: Create, edit, and manage student records with enrollment tracking
- **Teacher Management**: Manage teacher profiles, assignments, and capabilities
- **Class Management**: Create and organize classes with session-based grouping
- **Attendance Tracking**: Record and monitor student attendance with multiple status options (Present, Absent, Leave)
- **Exam Scheduling**: Create exam routines and generate date sheets
- **Study Materials**: Teachers can upload course materials; students can access and download them
- **Results Management**: Manage and display student examination results
- **Class Routines**: Create and manage class timetables and schedules
- **Parent-Teacher Meetings (PTM)**: Scheduler for booking and managing parent-teacher meetings
- **Email Verification**: Secure account verification and password recovery
- **PDF Report Generation**: Export attendance and results as PDF documents
- **Notifications**: System for managing and displaying notifications to users

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Libraries**:
  - PHPMailer 6.9+ - Email handling and verification
  - DomPDF 2.0+ - PDF generation and report exports
- **Session Management**: PHP Sessions with database-backed verification

## Project Structure

```
├── admin/                    # Admin panel and management features
├── teacher/                  # Teacher dashboard and functionality
├── student/                  # Student portal and resources
├── parent/                   # Parent dashboard and access
├── config/                   # Database and configuration files
├── assets/                   # CSS, JavaScript, and images
├── helpers/                  # Utility functions (mailer, etc.)
├── partials/                 # Reusable HTML components
├── uploads/                  # User-uploaded materials and documents
├── vendor/                   # Composer dependencies
├── index.php                 # Main landing page
├── login.php                 # User authentication
├── register.php              # New user registration
├── verify.php                # Email verification
├── forget_password.php       # Password recovery initiation
├── reset_password.php        # Password reset handler
└── testemis.sql              # Database schema and sample data
```

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache with mod_rewrite recommended)
- SMTP configuration for email functionality

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/na9339732-beep/Rustum-emis.git
   cd Rustum-emis
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up the database**
   - Create a new MySQL database
   ```bash
   mysql -u root -p < testemis.sql
   ```
   - Or import `testemis.sql` through phpMyAdmin

4. **Configure database connection**
   
   Edit [config/db.php](config/db.php) with your database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "testemis";
   ```

5. **Configure email service** (Optional but recommended)
   
   Update [helpers/mailer.php](helpers/mailer.php) with your SMTP settings for email verification and password recovery to function properly.

6. **Set up web server**
   
   - Place the project in your web root (e.g., `htdocs` for XAMPP)
   - Ensure `mod_rewrite` is enabled for proper routing
   - Access the application at `http://localhost/finalEmis`

### Initial Setup

After installation, the system is ready for use. You can:

- Access the login page at the root URL
- Create new student or parent accounts via registration
- Contact your system administrator for teacher or admin account creation

## Usage

### User Roles

#### **Admin**
- Manage all teachers, students, and parents
- Create and manage classes and academic sessions
- Schedule exams and generate date sheets
- Monitor attendance records across all classes
- Manage system notifications
- View and manage departments

#### **Teacher**
- View assigned students and classes
- Upload study materials for students
- Record and manage class attendance
- Manage and input student examination results
- Set availability for parent-teacher meetings
- View class routine and schedules

#### **Student**
- View personal class routines and schedules
- Access study materials uploaded by teachers
- View examination results
- Monitor personal attendance records
- Download reports and materials

#### **Parent**
- View children's marks and examination results
- Monitor children's attendance
- Access class routines and schedules
- Book parent-teacher meetings with teachers

## Authentication

The system uses email-based authentication:

1. **Registration**: New students and parents can self-register
2. **Email Verification**: Account activation via verification link
3. **Password Recovery**: Secure password reset via email
4. **Session Management**: Persistent sessions with role-based redirects

## API Endpoints

Key entry points by role:

| Role | Entry Point | File |
|------|-------------|------|
| Admin | `/admin/index.php` | [admin/index.php](admin/index.php) |
| Teacher | `/teacher/index.php` | [teacher/index.php](teacher/index.php) |
| Student | `/student/index.php` | [student/index.php](student/index.php) |
| Parent | `/parent/index.php` | [parent/index.php](parent/index.php) |
| Login | `/login.php` | [login.php](login.php) |
| Register | `/register.php` | [register.php](register.php) |

## Database Schema

The system uses the following main tables:

- `users` - User accounts and authentication
- `students` - Student information and enrollment
- `teachers` - Teacher profiles and assignments
- `classes` - Class definitions and sessions
- `attendance` - Daily attendance records
- `exams` - Examination schedules and routines
- `results` - Student examination results
- `materials` - Study materials uploaded by teachers
- `sessions` - Academic sessions/terms
- `departments` - Administrative departments

See [testemis.sql](testemis.sql) for complete schema details.

## Email Configuration

To enable email functionality (verification, password recovery), configure your SMTP server in [helpers/mailer.php](helpers/mailer.php):

```php
$mail->Host = 'your_smtp_server';
$mail->Username = 'your_email@example.com';
$mail->Password = 'your_password';
$mail->Port = 587; // or 465 for SSL
```

## PDF Reports

The system uses DomPDF to generate PDF reports for:
- Attendance records (by teacher/class/student)
- Result sheets
- Class routines

Reports can be exported from respective management pages in the admin and teacher panels.

## Troubleshooting

### Email Not Sending
- Verify SMTP credentials in [helpers/mailer.php](helpers/mailer.php)
- Check firewall and port access to SMTP server
- Ensure email sending is enabled on your server

### Database Connection Issues
- Verify credentials in [config/db.php](config/db.php)
- Ensure MySQL service is running
- Check database exists and is accessible

### Login Issues
- Confirm email is verified
- Verify account is active (not disabled by admin)
- Ensure correct credentials are entered

## Support & Documentation

- **Database Schema**: See [testemis.sql](testemis.sql)
- **Configuration**: Refer to [config/db.php](config/db.php)
- **Email Functions**: Check [helpers/mailer.php](helpers/mailer.php)

For additional support or issues, please refer to the project's issue tracker.

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to submit pull requests, report issues, and suggest improvements.

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Maintainers

**na9339732-beep** - Project Creator and Maintainer

---

**Last Updated**: April 2026

For more information, visit the [project repository](https://github.com/na9339732-beep/Rustum-emis).
