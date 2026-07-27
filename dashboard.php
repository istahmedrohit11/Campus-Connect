<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
$pageTitle = 'Dashboard - Campus Connect';
$bodyClass = 'app-page dashboard-page';
$mainClass = 'app-main dashboard-main page-shell';

$stmt = db()->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ? AND skill_type = 'teach'");
$stmt->execute([$user['id']]);
$teachCount = (int)$stmt->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ? AND skill_type = 'learn'");
$stmt->execute([$user['id']]);
$learnCount = (int)$stmt->fetchColumn();
$stmt = db()->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
$stmt->execute([$user['id']]);
$postCount = (int)$stmt->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM sessions WHERE (learner_id = ? OR mentor_id = ?) AND status IN ('pending','accepted')");
$stmt->execute([$user['id'], $user['id']]);
$sessionCount = (int)$stmt->fetchColumn();
$stmt = db()->query("SELECT p.*, u.name AS author_name, u.role AS author_role, (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comment_count FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC LIMIT 3");
$recentPosts = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Dashboard</h1>
<section class="dashboard-stats" aria-label="Dashboard statistics">
    <article class="dashboard-stat stat-teach"><strong><?= $teachCount ?></strong><span>Skills/Teach</span></article>
    <article class="dashboard-stat stat-learn"><strong><?= $learnCount ?></strong><span>Skills/Learn</span></article>
    <article class="dashboard-stat stat-post"><strong><?= $postCount ?></strong><span>My Post</span></article>
    <article class="dashboard-stat stat-session"><strong><?= $sessionCount ?></strong><span>Active Session</span></article>
</section>
<section class="dashboard-panels">
    <article class="dashboard-card welcome-card">
        <h2>Hello, <?= e($user['name']) ?></h2>
        <p>You are logged in as <strong><?= e($user['role']) ?></strong></p>
        <p>Rating: <?= e(avg_rating((int)$user['id'])) ?></p>
        <div class="dashboard-actions">
            <a href="<?= url('edit-profile.php') ?>">Edit Profile</a>
            <a href="<?= url('skills.php') ?>">Manage Skills</a>
            <a href="<?= url('create-post.php') ?>">Create Post</a>
        </div>
    </article>
    <article class="dashboard-card quick-search-card">
        <h2>Quick Search</h2>
        <form method="get" action="<?= url('search.php') ?>">
            <label for="dashboard-search">Search students by skill</label>
            <input id="dashboard-search" type="search" name="q" placeholder="Python, Photoshop, Excel">
            <button type="submit">Search</button>
        </form>
    </article>
</section>
<section class="recent-request-card">
    <div class="section-heading-row"><h2>Recent Learning Requests</h2><a href="<?= url('posts.php') ?>">See all</a></div>
    <?php if (!$recentPosts): ?>
        <div class="empty-state">No posts yet.</div>
    <?php else: ?>
        <div class="dashboard-recent-list">
            <?php foreach ($recentPosts as $post): ?>
                <a href="<?= url('post-details.php?id=' . (int)$post['id']) ?>">
                    <strong><?= e($post['title']) ?></strong>
                    <span><?= e($post['author_name']) ?> · <?= e(time_ago($post['created_at'])) ?> · <?= (int)$post['comment_count'] ?> comments</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
