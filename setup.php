<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . DB_NAME . '`');
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
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
            CONSTRAINT fk_user_skills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_user_skills_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $seedSkills = [
            ['HTML', 'Coding'], ['CSS', 'Coding'], ['JavaScript', 'Coding'],
            ['PHP', 'Coding'], ['MySQL', 'Coding'], ['Python', 'Coding'],
            ['Photoshop', 'Design'], ['Canva', 'Design'], ['PowerPoint', 'Academic'],
            ['Excel', 'Business'], ['CV Writing', 'Career'],
            ['Public Speaking', 'Soft Skill'], ['Digital Marketing', 'Business'],
            ['Video Editing', 'Creative'], ['Academic Writing', 'Academic'],
            ['Freelancing', 'Career'],
        ];
        $insert = $pdo->prepare('INSERT IGNORE INTO skills (skill_name, category) VALUES (?, ?)');
        foreach ($seedSkills as $skill) {
            $insert->execute($skill);
        }
        $message = 'Part 1 setup completed successfully. You can now register a student or teacher account.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect Part 1 Setup</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<main class="container">
    <div class="card">
        <h1>Campus Connect Part 1 Setup</h1>
        <p>This creates only the account, profile and skills tables required for Part 1.</p>
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post"><?= csrf_field() ?><button class="btn btn-primary" type="submit">Run Part 1 Setup</button></form>
        <p class="small">XAMPP normally uses the root database user with an empty password. Update includes/config.php if your settings differ.</p>
    </div>
</main>
</body>
</html>
