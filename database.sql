-- Create Database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users Table (for admin/teacher login)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'teacher') DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes Table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    class_id INT,
    date_of_birth DATE,
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, attendance_date)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@attendance.com', 'admin');

-- Insert sample classes
INSERT INTO classes (class_name, section, description) VALUES
('Class 10', 'A', 'Grade 10 Section A'),
('Class 10', 'B', 'Grade 10 Section B'),
('Class 11', 'A', 'Grade 11 Section A'),
('Class 12', 'A', 'Grade 12 Section A');

-- Insert sample students
INSERT INTO students (roll_number, first_name, last_name, email, phone, class_id, date_of_birth) VALUES
('2024001', 'John', 'Doe', 'john.doe@example.com', '1234567890', 1, '2008-05-15'),
('2024002', 'Jane', 'Smith', 'jane.smith@example.com', '1234567891', 1, '2008-06-20'),
('2024003', 'Michael', 'Johnson', 'michael.j@example.com', '1234567892', 1, '2008-04-10'),
('2024004', 'Emily', 'Brown', 'emily.b@example.com', '1234567893', 2, '2008-07-25'),
('2024005', 'David', 'Wilson', 'david.w@example.com', '1234567894', 2, '2008-03-12');
