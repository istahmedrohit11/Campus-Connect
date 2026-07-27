<?php
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$bodyClass = trim($bodyClass ?? '');
$mainClass = trim($mainClass ?? 'site-main page-shell');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
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
                <a href="<?= url('dashboard.php?coming=posts') ?>">Post</a>
                <a href="<?= url('dashboard.php?coming=search') ?>">Search</a>
                <a href="<?= url('dashboard.php?coming=notifications') ?>">Notification</a>
                <a class="<?= in_array($currentPage, ['profile.php', 'edit-profile.php', 'skills.php'], true) ? 'active' : '' ?>" href="<?= url('profile.php?id=' . $user['id']) ?>">Profile</a>
                <form class="logout-form" method="post" action="<?= url('logout.php') ?>">
                    <?= csrf_field() ?>
                    <button class="nav-auth-button" type="submit">LOGOUT</button>
                </form>
            <?php else: ?>
                <a class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= url('index.php') ?>">HOME</a>
                <a href="<?= url('index.php#contact') ?>">CONTRACT</a>
                <a class="nav-auth-button <?= $currentPage === 'login.php' ? 'active' : '' ?>" href="<?= url('login.php') ?>">LOGIN</a>
                <a class="nav-signup <?= $currentPage === 'register.php' ? 'active' : '' ?>" href="<?= url('register.php') ?>">SIGNUP</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="<?= e($mainClass) ?>">
    <div class="flash-stack">
        <?php show_flash(); ?>
    </div>
