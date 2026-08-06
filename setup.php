<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$message = '';
$error = '';
$adminEmail = 'admin@campusconnect.local';
$adminPassword = 'Admin123!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        // The old setup attempted CREATE TABLE before selecting a database,
        // which caused XAMPP/MariaDB error #1046: No database selected.
        $safeDbName = str_replace('`', '``', DB_NAME);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$safeDbName}`");

        $sql = file_get_contents(__DIR__ . '/database/campus_connect.sql');
        if ($sql === false) {
            throw new RuntimeException('Could not read the database schema.');
        }

        // Remove full-line SQL comments before splitting the import script.
        // The project schema contains no stored routines, so semicolon splitting
        // is safe and works with the PDO driver bundled with XAMPP.
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($sql)) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }

        // Safely upgrade a Part 1 installation without deleting user data.
        try { $pdo->exec("ALTER TABLE users MODIFY role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student'"); } catch (Throwable $ignored) {}
        try { $pdo->exec("ALTER TABLE users ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at"); } catch (Throwable $ignored) {}

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$adminEmail]);
        if (!$stmt->fetchColumn()) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department, semester, bio, status, created_at) VALUES (?, ?, ?, 'admin', ?, ?, ?, 'active', NOW())");
            $stmt->execute([
                'Campus Connect Admin',
                $adminEmail,
                password_hash($adminPassword, PASSWORD_DEFAULT),
                'Administration',
                'Staff',
                'Platform administrator account.',
            ]);
        }
        $message = 'Setup completed. All nine tables and the complete Campus Connect workflow are ready.';
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
    <title>Campus Connect Setup</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="setup-page">
<main class="setup-shell">
    <section class="setup-card">
        <img src="<?= BASE_URL ?>/assets/images/campus-connect-logo.png" alt="Campus Connect">
        <h1>Campus Connect Setup</h1>
        <p>Run this once after copying the project to XAMPP. It creates or upgrades the complete nine-table database without removing existing Part 1 data.</p>
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post"><?= csrf_field() ?><button class="figma-button" type="submit">Run Full Setup</button></form>
        <div class="setup-credentials">
            <strong>Default admin</strong><br>
            Email: <code><?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?></code><br>
            Password: <code><?= htmlspecialchars($adminPassword, ENT_QUOTES, 'UTF-8') ?></code>
        </div>
        <p class="small">Change the admin password after your first login. XAMPP normally uses database user <code>root</code> with an empty password.</p>
    </section>
</main>
</body>
</html>
