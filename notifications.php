<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
if (isset($_GET['open'])) {
    $id = (int)$_GET['open'];
    $stmt = db()->prepare('SELECT target_url FROM notifications WHERE id=? AND user_id=? LIMIT 1');
    $stmt->execute([$id, $user['id']]);
    $target = $stmt->fetchColumn();
    if ($target) {
        db()->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?')->execute([$id, $user['id']]);
        $target = ltrim((string)$target, '/');
        if (preg_match('/^[a-zA-Z0-9_\-\.]+\.php(?:\?.*)?(?:#.*)?$/', $target)) redirect($target);
    }
    redirect('notifications.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    db()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$user['id']]);
    flash('success', 'All notifications marked as read.');
    redirect('notifications.php');
}
$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100');
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();
$pageTitle = 'Notifications - Campus Connect';
$bodyClass = 'app-page notifications-page';
$mainClass = 'app-main notifications-main page-shell';
include __DIR__ . '/includes/header.php';
?>
<div class="notification-heading"><h1>Notifications</h1><?php if ($notifications): ?><form method="post"><?= csrf_field() ?><button type="submit">Mark all as read</button></form><?php endif; ?></div>
<?php if (!$notifications): ?>
    <section class="figma-empty-state notification-empty"><div class="notification-bell">!</div><h2>No notifications yet</h2><p>Your comments, sessions and ratings will appear here.</p></section>
<?php else: ?>
    <section class="notification-list">
        <?php foreach ($notifications as $notification): ?><a class="notification-item <?= !$notification['is_read'] ? 'is-new' : '' ?>" href="<?= url('notifications.php?open=' . (int)$notification['id']) ?>"><span class="notification-icon"><?= e(strtoupper(substr($notification['type'], 0, 1))) ?></span><span class="notification-copy"><strong><?= e($notification['title']) ?><?php if (!$notification['is_read']): ?><em>New</em><?php endif; ?></strong><span><?= e($notification['message']) ?></span><small><?= e(time_ago($notification['created_at'])) ?></small></span></a><?php endforeach; ?>
    </section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
