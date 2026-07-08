# Online Exam System

An online examination platform built with PHP and MySQL. Students register under a course, take timed exams with randomized questions, and get instant results. Admins manage the question bank, generate questions with AI (Gemini), and view performance analytics.

## Features

### Student
- Register under a course
- View assigned units/subjects
- Take timed exams with randomly selected questions (unique per attempt)
- Resume in-progress exams if interrupted
- Auto-submit when time expires
- Instant scoring with answer review
- View past results and performance stats

### Admin
- Manage courses, units, and the question bank (CRUD)
- Generate multiple-choice questions using Google Gemini AI
- Filter questions by unit
- View all student exam attempts with detailed answer review
- See performance analytics (per-course averages, top students, pass rate)
- Real-time notification panel for exam completions and system events

### General
- Password hashing (bcrypt)
- Password reset flow (token-based)
- Session-protected pages
- Role-based dashboard redirection
- Responsive Bootstrap 5 UI

## Requirements

- XAMPP (PHP 8.x, MySQL)
- Bootstrap 5 and Bootstrap Icons (loaded via CDN)
- cURL extension enabled (for AI question generation)

## Setup

1. Clone or copy the project into `C:\xampp\htdocs\online-exam-system`
2. Start Apache and MySQL from XAMPP Control Panel
3. Open phpMyAdmin and create a database named `online_exam_system`
4. Run the SQL below to create all tables
5. Run the seed data SQL
6. (Optional) Configure AI generation — see API Keys section
7. Visit `http://localhost/online-exam-system`

## Database Tables

```sql
-- ── Auth ──
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    admission_no VARCHAR(30) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Courses ──
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

-- ── Units & Questions ──
CREATE TABLE units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    unit_name VARCHAR(150) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL DEFAULT 30,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('a','b','c','d') NOT NULL,
    FOREIGN KEY (unit_id) REFERENCES units(unit_id) ON DELETE CASCADE
);

-- ── Exams ──
CREATE TABLE exam_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    unit_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME DEFAULT NULL,
    score INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    score_percent DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('in_progress','completed') DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(unit_id) ON DELETE CASCADE
);

CREATE TABLE exam_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('a','b','c','d') DEFAULT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
);

-- ── Notifications ──
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(500),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);
```

## Seed Data

```sql
INSERT INTO courses (course_name) VALUES
('Computer Science'),
('Information Technology'),
('Business Administration');

-- Sample units for Computer Science (course_id = 1)
INSERT INTO units (course_id, unit_name, description, duration_minutes) VALUES
(1, 'Data Structures', 'Arrays, linked lists, trees, graphs, and algorithms', 30),
(1, 'Database Systems', 'SQL, normalization, ER diagrams, transactions', 30),
(1, 'Web Development', 'HTML, CSS, JavaScript, PHP, and frameworks', 30);

-- Sample questions
INSERT INTO questions (unit_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'Which data structure uses FIFO?', 'Stack', 'Queue', 'Tree', 'Graph', 'b'),
(1, 'What is the time complexity of binary search?', 'O(n)', 'O(log n)', 'O(n^2)', 'O(1)', 'b'),
(2, 'Which language is used to query databases?', 'Java', 'Python', 'SQL', 'HTML', 'c'),
(3, 'Which HTML tag is used for a hyperlink?', '<link>', '<a>', '<href>', '<url>', 'b');
```

## Project Structure

```
├── index.php                        # Public landing page
├── dashboard.php                    # Role-based dashboard router
├── exam.php                         # Standalone timed exam page
├── config/
│   ├── db.php                       # PDO database connection
│   └── api_keys.php                 # API keys (gitignored)
├── auth/
│   ├── login.php                    # Sign in
│   ├── register.php                 # Sign up with course selection
│   ├── logout.php                   # End session
│   ├── session_check.php            # Protect pages from unauthenticated access
│   ├── forgot_password.php          # Request password reset
│   └── reset_password.php           # Complete password reset
├── dashboard/
│   ├── layout.php                   # Reusable dashboard shell (sidebar + topbar)
│   ├── sidebar.php                  # Role-aware navigation menu
│   ├── notifications.php            # Notification API endpoint
│   ├── admin/                       # Admin pages
│   │   ├── home.php                 # Admin dashboard (stats overview)
│   │   ├── courses.php              # CRUD courses
│   │   ├── units.php                # CRUD units (filterable by course)
│   │   ├── questions.php            # CRUD + AI-generated questions
│   │   ├── question_generator.php   # Gemini API endpoint for AI questions
│   │   ├── attempts.php             # View all student attempts
│   │   ├── attempt_detail.php       # Detailed answer review (AJAX target)
│   │   └── analytics.php            # Performance stats & top students
│   └── student/                     # Student pages
│       ├── home.php                 # Student dashboard (stats overview)
│       ├── units.php                # View assigned units with status
│       ├── results.php              # Past exam results & review
│       └── profile.php              # Account information
├── assets/
│   └── css/style.css                # All custom styles
└── README.md
```

## Role Flows

### Student Flow
```
Register → Choose course → Login → Dashboard
    → My Units → Pick a unit → Take Exam (timed, random questions)
    → Submit / Timer expires → Instant results with answer review
    → My Results → View past attempts & detailed review
```

### Admin Flow
```
Login → Dashboard (stats overview)
    → Courses → Add/Edit/Delete courses
    → Units → Add/Edit/Delete units per course
    → Question Bank → Add/Edit/Delete manually OR
        → Generate with AI → Select unit → Get AI-suggested questions → Accept
    → Attempts → View all student submissions → Click to review answers
    → Analytics → Per-course averages, top students, pass rate
```

## API Keys (AI Question Generation)

To use the AI question generator, you need a Google Gemini API key:

1. Get a free key at https://aistudio.google.com/apikey
2. Create `config/api_keys.php`:
   ```php
   <?php
   define("GEMINI_KEY", "your-api-key-here");
   ```
3. The file is already in `.gitignore` and won't be committed

## Configuration

Edit `config/db.php` to change database credentials if needed. Defaults:

- Host: `localhost`
- Database: `online_exam_system`
- Username: `root`
- Password: `(empty)`
