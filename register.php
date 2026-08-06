<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) redirect('dashboard.php');
$pageTitle = 'Register - Campus Connect';
$bodyClass = 'register-page';
$mainClass = 'register-main auth-main';

$form = [
    'name' => '',
    'email' => '',
    'role' => 'student',
    'department' => '',
    'semester' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $default) {
        $form[$key] = trim((string)($_POST[$key] ?? $default));
    }
    $password = $_POST['password'] ?? '';

    if ($form['name'] === '' || $form['email'] === '' || $password === '') {
        flash('danger', 'Name, email and password are required.');
    } elseif (strlen($form['name']) > 120) {
        flash('danger', 'Name must be 120 characters or fewer.');
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 160) {
        flash('danger', 'Please enter a valid email address.');
    } elseif (strlen($password) < 6) {
        flash('danger', 'Password must be at least 6 characters.');
    } elseif (!in_array($form['role'], ['student', 'teacher'], true)) {
        flash('danger', 'Invalid role selected.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password, role, department, semester, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $form['name'],
                $form['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $form['role'],
                $form['department'],
                $form['semester'],
            ]);
            flash('success', 'Registration successful. Please login.');
            redirect('login.php?role=' . rawurlencode($form['role']));
        } catch (PDOException $e) {
            flash('danger', 'This email is already registered.');
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="registration-stage">
    <form class="registration-card" method="post" novalidate>
        <?= csrf_field() ?>
        <h1>Create an Account?</h1>
        <p class="registration-intro">Enter your personal details to create an<br>account now.</p>

        <label for="register-name">Full Name</label>
        <input id="register-name" type="text" name="name" value="<?= e($form['name']) ?>" placeholder="Enter your name" autocomplete="name" maxlength="120" required>

        <label for="register-email">Email</label>
        <input id="register-email" type="email" name="email" value="<?= e($form['email']) ?>" placeholder="Enter your Email account" autocomplete="email" maxlength="160" required>

        <label for="register-password">Password</label>
        <input id="register-password" type="password" name="password" placeholder="Enter your password" autocomplete="new-password" minlength="6" required>

        <div class="registration-grid">
            <div>
                <label for="register-role">Role</label>
                <select id="register-role" name="role">
                    <option value="student" <?= $form['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="teacher" <?= $form['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                </select>
            </div>
            <div>
                <label for="register-department">Department</label>
                <input id="register-department" type="text" name="department" value="<?= e($form['department']) ?>" placeholder="CSE/BBA/English" maxlength="120">
            </div>
        </div>

        <label for="register-semester">Semester/Year</label>
        <input id="register-semester" type="text" name="semester" value="<?= e($form['semester']) ?>" placeholder="5th Semester" maxlength="50">

        <button class="registration-submit" type="submit">Create account</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
