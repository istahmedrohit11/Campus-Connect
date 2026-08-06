<?php
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$bodyClass = trim($bodyClass ?? '');
$mainClass = trim($mainClass ?? 'site-main page-shell');
$unreadCount = $user ? unread_notification_count((int)$user['id']) : 0;
$extraStyles = is_array($extraStyles ?? null) ? $extraStyles : [];
$styleVersion = (string) (@filemtime(__DIR__ . '/../assets/css/style.css') ?: time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= e($styleVersion) ?>">
    <?php foreach ($extraStyles as $extraStyle): ?>
        <?php $extraStyleVersion = (string) (@filemtime(__DIR__ . '/../' . ltrim($extraStyle, '/')) ?: $styleVersion); ?>
        <link rel="stylesheet" href="<?= e(url($extraStyle)) ?>?v=<?= e($extraStyleVersion) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="site-header">
    <div class="header-inner">
        <a class="site-brand" href="<?= url('index.php') ?>" aria-label="Campus Connect home">
            <img src="<?= url('assets/images/campus-connect-logo.png') ?>" alt="Campus Connect">
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="primary-navigation" aria-label="Open navigation">
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" id="primary-navigation" data-nav aria-label="Primary navigation">
            <?php if ($user): ?>
                <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= url('dashboard.php') ?>">Dashboard</a>
                <a class="<?= in_array($currentPage, ['posts.php', 'create-post.php', 'post-details.php'], true) ? 'active' : '' ?>" href="<?= url('posts.php') ?>">Post</a>
                <a class="<?= $currentPage === 'search.php' ? 'active' : '' ?>" href="<?= url('search.php') ?>">Search</a>
                <a class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>" href="<?= url('notifications.php') ?>">Notification<?php if ($unreadCount): ?><span class="notification-count"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span><?php endif; ?></a>
                <a class="<?= in_array($currentPage, ['sessions.php', 'request-session.php', 'rate-session.php'], true) ? 'active' : '' ?>" href="<?= url('sessions.php') ?>">Sessions</a>
                <a class="<?= in_array($currentPage, ['profile.php', 'edit-profile.php', 'skills.php'], true) ? 'active' : '' ?>" href="<?= url('profile.php?id=' . (int)$user['id']) ?>">Profile</a>
                <?php if (is_admin($user)): ?><a href="<?= url('admin/dashboard.php') ?>">Admin</a><?php endif; ?>
                <form class="logout-form" method="post" action="<?= url('logout.php') ?>">
                    <?= csrf_field() ?>
                    <button class="nav-auth-button" type="submit">LOGOUT</button>
                </form>
            <?php else: ?>
                <a class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= url('index.php') ?>">HOME</a>
                <a href="<?= url('index.php#contact') ?>">CONTACT</a>
                <a class="nav-auth-button <?= in_array($currentPage, ['login-options.php', 'login.php'], true) ? 'active' : '' ?>" href="<?= url('login-options.php') ?>">LOGIN</a>
                <a class="nav-signup <?= $currentPage === 'register.php' ? 'active' : '' ?>" href="<?= url('register.php') ?>">SIGNUP</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="<?= e($mainClass) ?>">
    <div class="flash-stack">
        <?php show_flash(); ?>
    </div>
