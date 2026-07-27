CREATE DATABASE IF NOT EXISTS campus_connect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE campus_connect;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','teacher') NOT NULL DEFAULT 'student',
  department VARCHAR(120) DEFAULT NULL,
  semester VARCHAR(50) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  profile_photo VARCHAR(255) DEFAULT NULL,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  skill_name VARCHAR(120) NOT NULL UNIQUE,
  category VARCHAR(120) NOT NULL DEFAULT 'General',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  skill_id INT NOT NULL,
  skill_type ENUM('teach','learn') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_skill (user_id, skill_id, skill_type),
  CONSTRAINT fk_user_skills_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_skills_skill
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO skills (skill_name, category) VALUES
  ('HTML', 'Coding'),
  ('CSS', 'Coding'),
  ('JavaScript', 'Coding'),
  ('PHP', 'Coding'),
  ('MySQL', 'Coding'),
  ('Python', 'Coding'),
  ('Photoshop', 'Design'),
  ('Canva', 'Design'),
  ('PowerPoint', 'Academic'),
  ('Excel', 'Business'),
  ('CV Writing', 'Career'),
  ('Public Speaking', 'Soft Skill'),
  ('Digital Marketing', 'Business'),
  ('Video Editing', 'Creative'),
  ('Academic Writing', 'Academic'),
  ('Freelancing', 'Career');
