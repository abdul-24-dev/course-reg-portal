-- Create the database
CREATE DATABASE course_registration_db;
USE course_registration_db;

-- Create departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    matric_prefix VARCHAR(10) NOT NULL UNIQUE -- e.g., "CMP" for Computer Science, "MCB" for Microbiology
);

-- Create sessions tablei d
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE -- e.g., "2023/2024"
);

-- Create semesters table
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE -- e.g., "First Semester", "Second Semester"
);

-- Create students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Create courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    credit_unit INT NOT NULL,
    semester_id INT NOT NULL,
    session_id INT NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Create student_courses table (course registration)
CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);