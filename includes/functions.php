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

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function redirect_absolute(string $location): void
{
    header('Location: ' . $location);
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $posted = (string)($_POST['csrf_token'] ?? '');
    if ($posted === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $posted)) {
        flash('danger', 'Security token mismatch. Please try again.');
        redirect('index.php');
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) return null;
    static $cachedId = null;
    static $user = null;
    $sessionId = (int)$_SESSION['user_id'];
    if ($cachedId !== $sessionId) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$sessionId]);
        $user = $stmt->fetch() ?: null;
        $cachedId = $sessionId;
    }
    return $user;
}

function user_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please login first.');
        redirect('login-options.php');
    }
    $user = current_user();
    if (!$user) {
        logout_session();
        flash('warning', 'Your session has expired. Please login again.');
        redirect('login-options.php');
    }
    if (($user['status'] ?? 'active') === 'blocked') {
        logout_session();
        flash('danger', 'Your account is blocked. Please contact admin.');
        redirect('login-options.php');
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

function is_admin(?array $user = null): bool
{
    $user ??= current_user();
    return ($user['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    require_role(['admin']);
}

function user_avatar(?array $user): string
{
    if (!$user || empty($user['profile_photo'])) return url('assets/images/default-avatar.png');
    return url((string)$user['profile_photo']);
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

function allowed_profile_exts(): array
{
    return ['png', 'jpg', 'jpeg', 'gif', 'webp'];
}

function allowed_attachment_exts(): array
{
    return ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
}

function upload_file(string $field, string $folder, ?array $allowedExtensions = null): ?string
{
    if (empty($_FILES[$field]['name'])) return null;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }
    if ((int)$file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('File is too large. Maximum size is 5 MB.');
    }
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $allowed = $allowedExtensions ?? allowed_attachment_exts();
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('File type is not allowed.');
    }
    if (in_array($ext, allowed_profile_exts(), true) && @getimagesize((string)$file['tmp_name']) === false) {
        throw new RuntimeException('The uploaded image is not valid.');
    }
    $safeFolder = preg_replace('/[^a-z0-9_-]/i', '', trim($folder, '/')) ?: 'files';
    $targetDir = __DIR__ . '/../uploads/' . $safeFolder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Could not create the upload folder.');
    }
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = $targetDir . '/' . $filename;
    if (!move_uploaded_file((string)$file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return 'uploads/' . $safeFolder . '/' . $filename;
}

function upload_profile_photo(string $field = 'profile_photo'): ?string
{
    return upload_file($field, 'profiles', allowed_profile_exts());
}

function delete_uploaded_file(?string $path): void
{
    if (!$path || !str_starts_with($path, 'uploads/')) return;
    $absolute = realpath(__DIR__ . '/../' . $path);
    $uploads = realpath(__DIR__ . '/../uploads');
    if ($absolute && $uploads && str_starts_with($absolute, $uploads . DIRECTORY_SEPARATOR) && is_file($absolute)) {
        @unlink($absolute);
    }
}

function get_or_create_skill(string $skillName, string $category = 'General'): int
{
    $name = trim($skillName);
    if ($name === '') throw new InvalidArgumentException('Skill name is required.');
    $stmt = db()->prepare('SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(?) LIMIT 1');
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $stmt = db()->prepare('INSERT INTO skills (skill_name, category) VALUES (?, ?)');
    $stmt->execute([$name, trim($category) ?: 'General']);
    return (int)db()->lastInsertId();
}

function avg_rating(int $userId): string
{
    try {
        $stmt = db()->prepare('SELECT AVG(rating) AS average_rating, COUNT(*) AS rating_count FROM ratings WHERE receiver_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || (int)$row['rating_count'] === 0) return 'No rating';
        return number_format((float)$row['average_rating'], 1) . ' / 5 (' . (int)$row['rating_count'] . ')';
    } catch (Throwable) {
        return 'No rating';
    }
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
    if (!$timestamp) return '';
    $diff = max(0, time() - $timestamp);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hour(s) ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M d, Y', $timestamp);
}

function excerpt(?string $text, int $length = 180): string
{
    $text = trim(strip_tags((string)$text));
    if (function_exists('mb_strlen') && mb_strlen($text) > $length) return mb_substr($text, 0, $length - 1) . '…';
    if (strlen($text) > $length) return substr($text, 0, $length - 1) . '…';
    return $text;
}

function unread_notification_count(int $userId): int
{
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function create_notification(int $userId, string $type, string $title, string $message, string $targetUrl = 'notifications.php'): void
{
    if ($userId <= 0) return;
    $stmt = db()->prepare('INSERT INTO notifications (user_id, type, title, message, target_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$userId, $type, $title, $message, ltrim($targetUrl, '/')]);
}

function fetch_post(int $postId): ?array
{
    $stmt = db()->prepare("SELECT p.*, u.name AS author_name, u.role AS author_role, u.department AS author_department, u.profile_photo AS author_photo,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p JOIN users u ON u.id = p.user_id WHERE p.id = ? LIMIT 1");
    $stmt->execute([$postId]);
    return $stmt->fetch() ?: null;
}

function delete_post_by_id(int $postId): bool
{
    $post = fetch_post($postId);
    if (!$post) return false;
    $stmt = db()->prepare('SELECT attachment FROM comments WHERE post_id = ?');
    $stmt->execute([$postId]);
    foreach ($stmt->fetchAll() as $comment) delete_uploaded_file($comment['attachment'] ?? null);
    delete_uploaded_file($post['attachment'] ?? null);
    $stmt = db()->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    return $stmt->rowCount() > 0;
}

function attachment_label(?string $path): string
{
    return $path ? basename($path) : '';
}

function session_status_label(string $status): string
{
    return ucfirst($status);
}

function session_other_user(array $session, int $currentUserId): array
{
    if ((int)$session['learner_id'] === $currentUserId) {
        return ['id' => (int)$session['mentor_id'], 'name' => $session['mentor_name'], 'role' => 'Mentor'];
    }
    return ['id' => (int)$session['learner_id'], 'name' => $session['learner_name'], 'role' => 'Learner'];
}

function has_rated_session(int $sessionId, int $userId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM ratings WHERE session_id = ? AND rater_id = ? LIMIT 1');
    $stmt->execute([$sessionId, $userId]);
    return (bool)$stmt->fetchColumn();
}

function valid_http_url(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') return null;
    if (strlen($value) > 500 || !filter_var($value, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Please enter a valid URL.');
    }
    $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) throw new RuntimeException('Only HTTP and HTTPS links are allowed.');
    return $value;
}

function report_target(string $type, int $id): ?array
{
    if ($type === 'user') {
        $stmt = db()->prepare('SELECT id, name AS label FROM users WHERE id = ?');
    } elseif ($type === 'post') {
        $stmt = db()->prepare('SELECT id, title AS label FROM posts WHERE id = ?');
    } elseif ($type === 'comment') {
        $stmt = db()->prepare("SELECT c.id, CONCAT('Comment by ', u.name) AS label FROM comments c JOIN users u ON u.id = c.user_id WHERE c.id = ?");
    } else {
        return null;
    }
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
?>
