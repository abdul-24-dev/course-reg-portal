-- Insert sample departments
INSERT INTO departments (name, matric_prefix) VALUES 
('Computer Science', 'CMP'),
('Microbiology', 'MCB'),
('Physics', 'PHY'),
('Chemistry', 'CHM');

-- Insert sample sessions
INSERT INTO sessions (name) VALUES 
('2022/2023'),
('2023/2024');

-- Insert sample semesters
INSERT INTO semesters (name) VALUES 
('First Semester'),
('Second Semester'),
('Third Semester');

-- Insert sample students
INSERT INTO students (matric_number, full_name, department_id, email, password) VALUES
('FT23CMP0133', 'John Doe', 1, 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- Password: password
('FT23MCB0001', 'Jane Smith', 2, 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- Password: password

-- Insert sample courses
INSERT INTO courses (course_code, course_name, credit_unit, semester_id, session_id, department_id) VALUES
('CSC101', 'Introduction to Programming', 3, 1, 2, 1), -- First Semester, 2023/2024, Computer Science
('CSC102', 'Data Structures', 4, 1, 2, 1), -- First Semester, 2023/2024, Computer Science
('MCB101', 'Introduction to Microbiology', 3, 1, 2, 2); -- First Semester, 2023/2024, Microbiology