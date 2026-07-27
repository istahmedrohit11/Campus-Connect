<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
}

verify_csrf();
logout_session();
flash('success', 'You have logged out successfully.');
redirect('login.php');
