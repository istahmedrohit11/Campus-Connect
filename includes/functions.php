<?php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $item) {
        echo '<div class="alert alert-' . e($item['type']) . '" role="status">' . e($item['message']) . '</div>';
    }
    unset($_SESSION['flash']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $posted = $_POST['csrf_token'] ?? '';
        if (!$posted || !hash_equals($_SESSION['csrf_token'] ?? '', $posted)) {
            flash('danger', 'Security token mismatch. Please try again.');
            redirect('index.php');
        }
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please login first.');
        redirect('login.php');
    }
    $user = current_user();
    if (!$user) {
        logout_session();
        flash('warning', 'Your session has expired. Please login again.');
        redirect('login.php');
    }
    if ($user['status'] === 'blocked') {
        logout_session();
        flash('danger', 'Your account is blocked. Please contact admin.');
        redirect('login.php');
    }
}

function logout_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

function require_role(array $roles): void
{
    require_login();
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        flash('danger', 'You do not have permission to access that page.');
        redirect('dashboard.php');
    }
}

function user_avatar(?array $user): string
{
    if (!$user) return url('assets/images/default-avatar.png');
    if (!empty($user['profile_photo'])) return url($user['profile_photo']);
    return url('assets/images/default-avatar.png');
}

function user_initials(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string)$name)) ?: [];
    $parts = array_values(array_filter($parts));
    if (!$parts) return 'U';
    $first = function_exists('mb_substr') ? mb_substr($parts[0], 0, 1) : substr($parts[0], 0, 1);
    $lastPart = count($parts) > 1 ? $parts[count($parts) - 1] : '';
    $last = $lastPart !== '' ? (function_exists('mb_substr') ? mb_substr($lastPart, 0, 1) : substr($lastPart, 0, 1)) : '';
    return strtoupper($first . $last);
}

function allowed_upload_exts(): array
{
    return ['png', 'jpg', 'jpeg', 'gif', 'webp'];
}

function upload_file(string $field, string $folder, ?array $allowedExtensions = null): ?string
{
    if (empty($_FILES[$field]['name'])) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }
    if ($_FILES[$field]['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('File is too large. Maximum size is 5 MB.');
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed = $allowedExtensions ?? allowed_upload_exts();
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('File type is not allowed.');
    }
    $imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    if (in_array($ext, $imageExts, true) && @getimagesize($_FILES[$field]['tmp_name']) === false) {
        throw new RuntimeException('The uploaded image is not valid.');
    }
    $safeFolder = trim($folder, '/');
    $targetDir = __DIR__ . '/../uploads/' . $safeFolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $targetDir . '/' . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return 'uploads/' . $safeFolder . '/' . $filename;
}

function upload_profile_photo(string $field = 'profile_photo'): ?string
{
    return upload_file($field, 'profiles', ['png', 'jpg', 'jpeg', 'gif', 'webp']);
}

function get_or_create_skill(string $skillName, string $category = 'General'): int
{
    $name = trim($skillName);
    if ($name === '') {
        throw new InvalidArgumentException('Skill name is required.');
    }
    $stmt = db()->prepare('SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(?) LIMIT 1');
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $stmt = db()->prepare('INSERT INTO skills (skill_name, category) VALUES (?, ?)');
    $stmt->execute([$name, $category ?: 'General']);
    return (int)db()->lastInsertId();
}

function avg_rating(int $userId): string
{
    return 'No rating';
}

function get_user_skills(int $userId, string $type): array
{
    $stmt = db()->prepare('SELECT s.* FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = ? AND us.skill_type = ? ORDER BY s.skill_name ASC');
    $stmt->execute([$userId, $type]);
    return $stmt->fetchAll();
}

function time_ago(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hour(s) ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M d, Y', $timestamp);
}
?>
