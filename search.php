<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$q = trim((string)($_GET['q'] ?? ''));
$mentors = [];
$posts = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = db()->prepare("SELECT DISTINCT u.*, GROUP_CONCAT(DISTINCT s.skill_name ORDER BY s.skill_name SEPARATOR ', ') AS matching_skills FROM users u JOIN user_skills us ON us.user_id=u.id AND us.skill_type='teach' JOIN skills s ON s.id=us.skill_id WHERE u.status='active' AND u.id<>? AND (s.skill_name LIKE ? OR s.category LIKE ? OR u.name LIKE ?) GROUP BY u.id ORDER BY u.name");
    $stmt->execute([current_user()['id'], $like, $like, $like]);
    $mentors = $stmt->fetchAll();
    $stmt = db()->prepare("SELECT p.*, u.name AS author_name, u.role AS author_role, u.profile_photo AS author_photo, (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comment_count FROM posts p JOIN users u ON u.id=p.user_id WHERE p.title LIKE ? OR p.description LIKE ? ORDER BY p.created_at DESC");
    $stmt->execute([$like, $like]);
    $posts = $stmt->fetchAll();
}
$pageTitle = 'Search - Campus Connect';
$bodyClass = 'app-page search-page';
$mainClass = 'app-main search-main page-shell';
include __DIR__ . '/includes/header.php';
?>
<h1 class="screen-title">Search</h1>
<section class="figma-search-box">
    <form method="get"><label for="main-search">Find mentors or learning requests</label><div><input id="main-search" type="search" name="q" value="<?= e($q) ?>" placeholder="Search by skill, mentor or post"><button type="submit">Search</button></div></form>
</section>
<?php if ($q === ''): ?>
    <section class="figma-empty-state search-empty"><img src="<?= url('assets/images/icon-search.png') ?>" alt=""><h2>Start your search</h2><p>Enter a teaching skill or learning topic above.</p></section>
<?php elseif (!$mentors && !$posts): ?>
    <section class="figma-empty-state search-empty"><img src="<?= url('assets/images/icon-search.png') ?>" alt=""><h2>No result found</h2><p>Try another skill or a broader keyword.</p></section>
<?php else: ?>
    <section class="search-results-section"><h2>Mentors (<?= count($mentors) ?>)</h2><div class="mentor-result-grid">
        <?php foreach ($mentors as $mentor): ?><article class="mentor-result-card"><img src="<?= e(user_avatar($mentor)) ?>" alt=""><div><h3><?= e($mentor['name']) ?></h3><p><?= e($mentor['department'] ?: ucfirst($mentor['role'])) ?></p><span><?= e($mentor['matching_skills']) ?></span><div><a href="<?= url('profile.php?id=' . (int)$mentor['id']) ?>">View Profile</a><a href="<?= url('request-session.php?mentor_id=' . (int)$mentor['id']) ?>">Request Session</a></div></div></article><?php endforeach; ?>
    </div></section>
    <section class="search-results-section"><h2>Learning Requests (<?= count($posts) ?>)</h2><div class="search-post-grid">
        <?php foreach ($posts as $post): ?><article class="search-post-card"><h3><a href="<?= url('post-details.php?id=' . (int)$post['id']) ?>"><?= e($post['title']) ?></a></h3><p><?= e(excerpt($post['description'], 130)) ?></p><span><?= e($post['author_name']) ?> · <?= e(time_ago($post['created_at'])) ?> · <?= (int)$post['comment_count'] ?> comments</span></article><?php endforeach; ?>
    </div></section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
