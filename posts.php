<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$pageTitle = 'Learning Requests - Campus Connect';
$bodyClass = 'app-page posts-page';
$mainClass = 'app-main feed-main page-shell';
$stmt = db()->query("SELECT p.*, u.name AS author_name, u.role AS author_role, u.department AS author_department, u.profile_photo AS author_photo, (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comment_count FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="feed-heading"><div><h1>Learning Requests</h1><p>Find a topic where your knowledge can help another student.</p></div><a class="figma-button" href="<?= url('create-post.php') ?>">Create Post</a></div>
<?php if (!$posts): ?>
    <section class="figma-empty-state"><img src="<?= url('assets/images/icon-post.png') ?>" alt=""><h2>No learning requests yet</h2><p>Be the first to publish a request.</p><a class="figma-button" href="<?= url('create-post.php') ?>">Create Post</a></section>
<?php else: ?>
    <section class="post-feed">
    <?php foreach ($posts as $post): ?>
        <article class="learning-post-card">
            <div class="post-author-row">
                <a href="<?= url('profile.php?id=' . (int)$post['user_id']) ?>" class="mini-avatar-wrap"><img src="<?= e(user_avatar(['profile_photo'=>$post['author_photo']])) ?>" alt=""></a>
                <div><a class="post-author-name" href="<?= url('profile.php?id=' . (int)$post['user_id']) ?>"><?= e($post['author_name']) ?></a><span><?= e(ucfirst($post['author_role'])) ?> · <?= e($post['author_department'] ?: 'Campus Connect') ?> · <?= e(time_ago($post['created_at'])) ?></span></div>
            </div>
            <h2><a href="<?= url('post-details.php?id=' . (int)$post['id']) ?>"><?= e($post['title']) ?></a></h2>
            <p><?= e(excerpt($post['description'], 260)) ?></p>
            <div class="post-card-footer"><span><?= (int)$post['comment_count'] ?> Comment<?= (int)$post['comment_count'] === 1 ? '' : 's' ?></span><a href="<?= url('post-details.php?id=' . (int)$post['id']) ?>">View Details</a></div>
        </article>
    <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
