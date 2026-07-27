<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) redirect('dashboard.php');
$pageTitle = 'Login - Campus Connect';
$bodyClass = 'login-page';
$mainClass = 'login-main auth-main';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'blocked') {
            flash('danger', 'Your account is blocked. Please contact admin.');
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            unset($_SESSION['csrf_token']);
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('dashboard.php');
        }
    } else {
        flash('danger', 'Invalid email or password.');
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="login-layout">
    <div class="login-form-panel">
        <form class="login-form" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="login-user-icon" aria-hidden="true">
                <span class="login-user-head"></span>
                <span class="login-user-body"></span>
            </div>
            <h1>Welcome Back</h1>
            <label class="sr-only" for="login-email">Email address</label>
            <input id="login-email" type="email" name="email" value="<?= e($email) ?>" placeholder="Enter Email ID" autocomplete="email" required>
            <label class="sr-only" for="login-password">Password</label>
            <input id="login-password" type="password" name="password" placeholder="Enter Password" autocomplete="current-password" required>
            <button class="login-submit" type="submit">Login</button>
        </form>
    </div>
    <div class="login-visual-panel">
        <img src="<?= url('assets/images/ulab-logo.jpg') ?>" alt="University of Liberal Arts Bangladesh">
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
