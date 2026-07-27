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

$postCount = 0;
$sessionCount = 0;
$comingLabels = [
    'posts' => 'Posts',
    'search' => 'Search',
    'notifications' => 'Notifications',
    'sessions' => 'Sessions',
];
$comingKey = (string)($_GET['coming'] ?? '');
$comingLabel = $comingLabels[$comingKey] ?? null;

include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Dashboard</h1>

<?php if ($comingLabel): ?>
    <div class="part-coming-alert" role="status">
        <strong><?= e($comingLabel) ?></strong> is coming in the next part.
    </div>
<?php endif; ?>

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
            <a href="<?= url('dashboard.php?coming=posts') ?>">Create Post</a>
        </div>
    </article>

    <article class="dashboard-card quick-search-card">
        <h2>Quick Search</h2>
        <form method="get" action="<?= url('dashboard.php') ?>">
            <input type="hidden" name="coming" value="search">
            <label for="dashboard-search">Search students by skill</label>
            <input id="dashboard-search" type="search" name="q" placeholder="Python, Photoshop, Excel">
            <button type="submit">Search</button>
        </form>
    </article>
</section>

<section class="recent-request-card">
    <h2>Recent Learning Requests</h2>
    <div class="empty-state">No posts yet.</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
