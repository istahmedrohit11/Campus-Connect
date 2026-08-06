-- Campus Connect database schema for XAMPP / MariaDB / MySQL
-- This file is safe to import from the phpMyAdmin home page because it
-- creates and selects the database before creating the tables.
-- It is also safe to run more than once: tables are created only when
-- missing, and bundled seed rows use INSERT IGNORE.

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `campus_connect`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `campus_connect`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `department` varchar(120) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(120) NOT NULL,
  `category` varchar(120) NOT NULL DEFAULT 'General',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_skills_name` (`skill_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `resource_url` varchar(500) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_posts_created` (`created_at`),
  KEY `idx_posts_user` (`user_id`),
  FULLTEXT KEY `ft_posts_title_description` (`title`,`description`),
  CONSTRAINT `fk_posts_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `body` text DEFAULT NULL,
  `resource_url` varchar(500) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_post` (`post_id`,`created_at`),
  KEY `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_post`
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(180) NOT NULL,
  `message` varchar(500) NOT NULL,
  `target_url` varchar(500) NOT NULL DEFAULT 'notifications.php',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`,`is_read`,`created_at`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `learner_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `topic` varchar(180) NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `session_type` enum('online','offline') NOT NULL DEFAULT 'online',
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sessions_learner` (`learner_id`,`status`),
  KEY `idx_sessions_mentor` (`mentor_id`,`status`),
  KEY `idx_sessions_skill` (`skill_id`),
  KEY `idx_sessions_post` (`post_id`),
  KEY `idx_sessions_comment` (`comment_id`),
  CONSTRAINT `fk_sessions_learner`
    FOREIGN KEY (`learner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sessions_mentor`
    FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sessions_skill`
    FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sessions_post`
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sessions_comment`
    FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `rater_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ratings_session_rater` (`session_id`,`rater_id`),
  KEY `idx_ratings_receiver` (`receiver_id`),
  KEY `idx_ratings_rater` (`rater_id`),
  CONSTRAINT `fk_ratings_session`
    FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ratings_rater`
    FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ratings_receiver`
    FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `target_type` enum('user','post','comment') NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','resolved') NOT NULL DEFAULT 'pending',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reports_status` (`status`,`created_at`),
  KEY `idx_reports_reporter` (`reporter_id`),
  KEY `idx_reports_resolver` (`resolved_by`),
  CONSTRAINT `fk_reports_reporter`
    FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reports_resolver`
    FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `skill_type` enum('teach','learn') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_skills` (`user_id`,`skill_id`,`skill_type`),
  KEY `idx_user_skills_type` (`skill_type`,`skill_id`),
  KEY `idx_user_skills_skill` (`skill_id`),
  CONSTRAINT `fk_user_skills_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_skills_skill`
    FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing data from the supplied database dump.
INSERT IGNORE INTO `users`
  (`id`,`name`,`email`,`password`,`role`,`department`,`semester`,`bio`,`profile_photo`,`status`,`created_at`,`updated_at`)
VALUES
  (1,'Istiaque Ahmed','istiaquearohit11@gmail.com','$2y$10$QoPMPNl8vm6pnzeJNucyKuyXyyjS.xjkGAfnhhIEHg5PjYcbqoG4K','student','CSE','10th Semester','','uploads/profiles/20260729071709_93de5d23c66055af.jpg','active','2026-07-29 11:16:27','2026-07-29 11:17:09'),
  (2,'Tasnin Khan','tasnin2026@gmail.com','$2y$10$N4lJPAxHwIfk0chu/XvafujFoNK8zLxgnUJhauWnsvNV3hn6EINTW','student','CSE','10th Semester',NULL,NULL,'active','2026-07-29 11:18:36',NULL),
  (3,'Asif Khan','asifkhan@gmail.com','$2y$10$GvlANw5yUq8RnNGlD34M1eHNFvNkbQRSWA/lhTLAIsz.dERYOgHLy','teacher','CSE','',NULL,NULL,'active','2026-07-29 11:24:21',NULL),
  (4,'Campus Connect Admin','admin@campusconnect.local','$2y$12$AMvbQNIKGFr7LgTM4pbl4ObVvVa7f0EEppaWsKxsL13grDtMYrb7K','admin','Administration','Staff','Platform administrator account.',NULL,'active','2026-07-29 11:41:14',NULL);

INSERT IGNORE INTO `skills` (`id`,`skill_name`,`category`,`created_at`) VALUES
  (1,'HTML','Coding','2026-07-29 11:13:43'),
  (2,'CSS','Coding','2026-07-29 11:13:43'),
  (3,'JavaScript','Coding','2026-07-29 11:13:43'),
  (4,'PHP','Coding','2026-07-29 11:13:43'),
  (5,'MySQL','Coding','2026-07-29 11:13:43'),
  (6,'Python','Coding','2026-07-29 11:13:43'),
  (7,'Photoshop','Design','2026-07-29 11:13:43'),
  (8,'Canva','Design','2026-07-29 11:13:43'),
  (9,'PowerPoint','Academic','2026-07-29 11:13:43'),
  (10,'Excel','Business','2026-07-29 11:13:43'),
  (11,'CV Writing','Career','2026-07-29 11:13:43'),
  (12,'Public Speaking','Soft Skill','2026-07-29 11:13:43'),
  (13,'Digital Marketing','Business','2026-07-29 11:13:43'),
  (14,'Video Editing','Creative','2026-07-29 11:13:43'),
  (15,'Academic Writing','Academic','2026-07-29 11:13:43'),
  (16,'Freelancing','Career','2026-07-29 11:13:43'),
  (17,'Python,C++,Video Editing,Youtube SEO','General','2026-07-29 11:17:56'),
  (18,'Figma','General','2026-07-29 11:19:29');

INSERT IGNORE INTO `posts`
  (`id`,`user_id`,`title`,`description`,`resource_url`,`attachment`,`created_at`,`updated_at`)
VALUES
  (1,2,'I want to learn Video Editing','basic video editing in capcut',NULL,NULL,'2026-07-29 11:20:48',NULL);

INSERT IGNORE INTO `comments`
  (`id`,`post_id`,`user_id`,`body`,`resource_url`,`attachment`,`created_at`)
VALUES
  (1,1,1,'Yes. I can help you',NULL,NULL,'2026-07-29 11:21:51'),
  (2,1,3,'I can sent you video link',NULL,NULL,'2026-07-29 11:25:35');

INSERT IGNORE INTO `notifications`
  (`id`,`user_id`,`type`,`title`,`message`,`target_url`,`is_read`,`created_at`)
VALUES
  (1,2,'comment','New comment on your post','Istiaque Ahmed commented on “I want to learn Video Editing”.','post-details.php?id=1',0,'2026-07-29 11:21:51'),
  (2,2,'comment','New comment on your post','Asif Khan commented on “I want to learn Video Editing”.','post-details.php?id=1',0,'2026-07-29 11:25:35');

INSERT IGNORE INTO `user_skills`
  (`id`,`user_id`,`skill_id`,`skill_type`,`created_at`)
VALUES
  (1,1,17,'teach','2026-07-29 11:17:56'),
  (2,2,18,'teach','2026-07-29 11:19:29'),
  (3,2,14,'learn','2026-07-29 11:20:02');

SET FOREIGN_KEY_CHECKS = 1;
