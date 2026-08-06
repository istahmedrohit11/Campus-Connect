<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    $signedInUser = current_user();
    redirect(is_admin($signedInUser) ? 'admin/dashboard.php' : 'dashboard.php');
}

$allowedRoles = ['student', 'teacher', 'admin'];
$selectedRole = strtolower(trim((string)($_POST['role'] ?? $_GET['role'] ?? '')));

if (!in_array($selectedRole, $allowedRoles, true)) {
    redirect('login-options.php');
}

$roleLabels = [
    'student' => 'Student',
    'teacher' => 'Teacher',
    'admin' => 'Admin',
];
$roleLabel = $roleLabels[$selectedRole];

$pageTitle = $roleLabel . ' Login - Campus Connect';
$bodyClass = 'login-page login-role-' . $selectedRole;
$mainClass = 'login-main auth-main';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        flash('danger', 'Please enter a valid email address and password.');
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1');
        $stmt->execute([$email, $selectedRole]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                flash('danger', 'Your account is blocked. Please contact admin.');
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                unset($_SESSION['csrf_token']);
                flash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect($selectedRole === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
            }
        } else {
            flash('danger', 'Invalid ' . strtolower($roleLabel) . ' email or password.');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="login-layout">
    <div class="login-form-panel">
        <form class="login-form" method="post" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="role" value="<?= e($selectedRole) ?>">
            <div class="login-user-icon" aria-hidden="true">
                <span class="login-user-head"></span>
                <span class="login-user-body"></span>
            </div>
            <h1><?= e($roleLabel) ?> Login</h1>
            <label class="sr-only" for="login-email">Email address</label>
            <input id="login-email" type="email" name="email" value="<?= e($email) ?>" placeholder="Enter Email ID" autocomplete="email" maxlength="160" required>
            <label class="sr-only" for="login-password">Password</label>
            <input id="login-password" type="password" name="password" placeholder="Enter Password" autocomplete="current-password" required>
            <button class="login-submit" type="submit">Login</button>
            <a class="change-login-role" href="<?= url('login-options.php') ?>">Choose a different login type</a>
        </form>
    </div>
    <div class="login-visual-panel">
        <img src="<?= url('assets/images/ulab-logo.jpg') ?>" alt="University of Liberal Arts Bangladesh">
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
