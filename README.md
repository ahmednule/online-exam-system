# Online Exam System

An online examination platform built with PHP and MySQL. Students register under a course, take timed exams with randomized questions, and get instant results. Admins manage the question bank and view performance analytics.

## Features

- **Student** — Register under a course, take timed exams (random questions per attempt), instant scoring with answer review
- **Admin** — Manage courses, units, and question bank; view all student attempts and performance analytics
- **Secure auth** — Password hashing, session management, password reset flow

## Requirements

- XAMPP (PHP 8.x, MySQL)
- Bootstrap 5 (loaded via CDN)

## Setup

1. Clone or copy the project into `C:\xampp\htdocs\online-exam-system`
2. Start Apache and MySQL from XAMPP Control Panel
3. Open phpMyAdmin and create a database named `online_exam_system`
4. Import or run the SQL below to create the tables

### Database Tables

```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    admission_no VARCHAR(30) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course (user_id, course_id)
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Seed Data

```sql
INSERT INTO courses (course_name) VALUES
('Computer Science'),
('Information Technology'),
('Business Administration');
```

5. Visit `http://localhost/online-exam-system` in your browser

## Project Structure

```
├── index.php                    # Public landing page
├── dashboard.php                # Post-login dashboard placeholder
├── config/
│   └── db.php                   # Database connection (PDO)
├── auth/
│   ├── login.php                # Sign in
│   ├── register.php             # Sign up with course selection
│   ├── logout.php               # End session
│   ├── session_check.php        # Protect pages from unauthenticated access
│   ├── forgot_password.php      # Request password reset
│   └── reset_password.php       # Complete password reset
├── assets/
│   ├── css/style.css            # Custom styles
│   └── js/                      # JavaScript files
└── README.md
```

## Configuration

Edit `config/db.php` to change database credentials if needed. Defaults are:

- Host: `localhost`
- Database: `online_exam_system`
- Username: `root`
- Password: `(empty)`
