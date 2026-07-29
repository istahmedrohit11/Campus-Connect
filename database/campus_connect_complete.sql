-- Campus Connect complete database installer
-- Includes: database, all 9 tables, default skills, and a working admin account.
-- Default admin email: admin@campusconnect.local
-- Default admin password: Admin123!
-- Generated: 2026-07-29

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS campus_connect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE campus_connect;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
  department VARCHAR(120) DEFAULT NULL,
  semester VARCHAR(50) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  profile_photo VARCHAR(255) DEFAULT NULL,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
  KEY idx_user_skills_type (skill_type, skill_id),
  CONSTRAINT fk_user_skills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_skills_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  resource_url VARCHAR(500) DEFAULT NULL,
  attachment VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_posts_created (created_at),
  FULLTEXT KEY ft_posts_title_description (title, description),
  CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  body TEXT DEFAULT NULL,
  resource_url VARCHAR(500) DEFAULT NULL,
  attachment VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_comments_post (post_id, created_at),
  CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  learner_id INT NOT NULL,
  mentor_id INT NOT NULL,
  skill_id INT DEFAULT NULL,
  post_id INT DEFAULT NULL,
  comment_id INT DEFAULT NULL,
  topic VARCHAR(180) NOT NULL,
  session_date DATE NOT NULL,
  session_time TIME NOT NULL,
  session_type ENUM('online','offline') NOT NULL DEFAULT 'online',
  message TEXT DEFAULT NULL,
  status ENUM('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  completed_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_sessions_learner (learner_id, status),
  KEY idx_sessions_mentor (mentor_id, status),
  CONSTRAINT fk_sessions_learner FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sessions_mentor FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sessions_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE SET NULL,
  CONSTRAINT fk_sessions_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL,
  CONSTRAINT fk_sessions_comment FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  rater_id INT NOT NULL,
  receiver_id INT NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  feedback TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_session_rater (session_id, rater_id),
  KEY idx_ratings_receiver (receiver_id),
  CONSTRAINT fk_ratings_session FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_ratings_rater FOREIGN KEY (rater_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ratings_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_rating_value CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50) NOT NULL DEFAULT 'general',
  title VARCHAR(180) NOT NULL,
  message VARCHAR(500) NOT NULL,
  target_url VARCHAR(500) NOT NULL DEFAULT 'notifications.php',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notifications_user (user_id, is_read, created_at),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  target_type ENUM('user','post','comment') NOT NULL,
  target_id INT NOT NULL,
  reason VARCHAR(120) NOT NULL,
  details TEXT DEFAULT NULL,
  status ENUM('pending','resolved') NOT NULL DEFAULT 'pending',
  resolved_by INT DEFAULT NULL,
  resolved_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_reports_status (status, created_at),
  CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_resolver FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
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

-- Upgrade older Campus Connect installations so the admin role is accepted.
ALTER TABLE users
  MODIFY role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student';

-- Create the default administrator or repair it if it already exists.
-- The password below is a PHP password_hash() bcrypt hash for: Admin123!
INSERT INTO users
  (name, email, password, role, department, semester, bio, status, created_at)
VALUES
  (
    'Campus Connect Admin',
    'admin@campusconnect.local',
    '$2y$12$jW9iBHgSDpSXZnireVWJN.Ou3B5YBVGNULakpT6CBy7cotYjmA.hS',
    'admin',
    'Administration',
    'Staff',
    'Platform administrator account.',
    'active',
    NOW()
  )
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password = VALUES(password),
  role = 'admin',
  department = VALUES(department),
  semester = VALUES(semester),
  bio = VALUES(bio),
  status = 'active';

SET FOREIGN_KEY_CHECKS = 1;
