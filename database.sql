-- ============================================
-- Student Activities CRUD - Database Schema
-- ============================================
-- Run this SQL script in phpMyAdmin to create the database and table

CREATE DATABASE IF NOT EXISTS student_upload_center;
USE student_upload_center;

CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  filename VARCHAR(255) NOT NULL,
  file_type VARCHAR(50),
  file_size BIGINT,
  uploaded_by VARCHAR(100) DEFAULT 'Anonymous',
  uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_title (title),
  INDEX idx_uploaded_on (uploaded_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample data (optional - for testing)
-- ============================================
-- INSERT INTO activities (title, description, filename, file_type, file_size, uploaded_by) 
-- VALUES 
-- ('Sample Activity 1', 'This is a sample activity description', 'sample1.pdf', 'pdf', 102400, 'John Doe'),
-- ('Sample Activity 2', 'Another sample activity', 'sample2.docx', 'docx', 204800, 'Jane Smith');

