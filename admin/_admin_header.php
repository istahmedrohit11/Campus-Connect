<?php
require_admin();
$bodyClass='app-page admin-page';
$mainClass='app-main admin-main page-shell';
include __DIR__.'/../includes/header.php';
$adminCurrent=basename($_SERVER['PHP_SELF']??'');
?>
<div class="admin-shell"><aside class="admin-sidebar"><h2>Admin Panel</h2><a class="<?= $adminCurrent==='dashboard.php'?'active':'' ?>" href="<?= url('admin/dashboard.php') ?>">Overview</a><a class="<?= $adminCurrent==='users.php'?'active':'' ?>" href="<?= url('admin/users.php') ?>">Users</a><a class="<?= $adminCurrent==='posts.php'?'active':'' ?>" href="<?= url('admin/posts.php') ?>">Posts</a><a class="<?= $adminCurrent==='skills.php'?'active':'' ?>" href="<?= url('admin/skills.php') ?>">Skills</a><a class="<?= $adminCurrent==='reports.php'?'active':'' ?>" href="<?= url('admin/reports.php') ?>">Reports</a><a href="<?= url('dashboard.php') ?>">Back to App</a></aside><section class="admin-content">
