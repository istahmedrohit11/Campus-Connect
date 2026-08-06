<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    $signedInUser = current_user();
    redirect(is_admin($signedInUser) ? 'admin/dashboard.php' : 'dashboard.php');
}

$pageTitle = 'Choose Login Type - Campus Connect';
$bodyClass = 'login-choice-page';
$mainClass = 'login-choice-main';
$extraStyles = ['assets/css/login-options.css'];

$loginOptions = [
    'student' => [
        'title' => 'Login as Student',
        'description' => 'Find mentors, create posts & request sessions',
    ],
    'teacher' => [
        'title' => 'Login as Teacher',
        'description' => 'Teach skills, guide learners & manage sessions',
    ],
    'admin' => [
        'title' => 'Login as Admin',
        'description' => 'Manage users, posts, skills & reports',
    ],
];

include __DIR__ . '/includes/header.php';
?>
<section class="login-choice-layout" aria-labelledby="login-choice-title">
    <div class="login-choice-form-panel">
        <div class="login-choice-content">
            <div class="login-choice-user-icon" aria-hidden="true">
                <span class="login-choice-user-head"></span>
                <span class="login-choice-user-body"></span>
            </div>

            <h1 id="login-choice-title">Choose Login Type</h1>
            <p class="login-choice-subtitle">Select your account role to continue</p>

            <div class="login-option-list">
                <?php foreach ($loginOptions as $role => $option): ?>
                    <a class="login-option-card login-option-<?= e($role) ?>" href="<?= url('login.php?role=' . $role) ?>">
                        <span class="login-option-icon" aria-hidden="true">
                            <?php if ($role === 'student'): ?>
                                <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
                                    <path d="M6 25.5 32 12l26 13.5L32 39 6 25.5Z" fill="#ffffff"/>
                                    <path d="M16 31v12.5c7.7 7 24.3 7 32 0V31l-16 8.3L16 31Z" fill="#ffffff"/>
                                    <path d="M55 28v14" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="55" cy="46" r="3" fill="#ffffff"/>
                                </svg>
                            <?php elseif ($role === 'teacher'): ?>
                                <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
                                    <rect x="7" y="11" width="38" height="42" rx="4" fill="none" stroke="#ffffff" stroke-width="4"/>
                                    <path d="M15 20h22M15 28h16" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="45" cy="35" r="8" fill="#ffffff"/>
                                    <path d="M33 54c1.5-8 6.2-12 12-12s10.5 4 12 12H33Z" fill="#ffffff"/>
                                </svg>
                            <?php else: ?>
                                <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
                                    <path d="M32 6 55 15v16c0 14-9.2 23.4-23 28C18.2 54.4 9 45 9 31V15L32 6Z" fill="#000942"/>
                                    <path d="m21 32 7 7 15-16" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            <?php endif; ?>
                        </span>

                        <span class="login-option-copy">
                            <strong><?= e($option['title']) ?></strong>
                            <small><?= e($option['description']) ?></small>
                        </span>

                        <span class="login-option-arrow" aria-hidden="true">→</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <p class="login-choice-note">You will continue to the existing Campus Connect login form</p>
        </div>
    </div>

    <div class="login-choice-visual-panel" aria-hidden="true">
        <img src="<?= url('assets/images/ulab-logo.jpg') ?>" alt="">
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
