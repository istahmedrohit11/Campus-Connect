<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$id = (int)($_GET['id'] ?? current_user()['id']);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$profile = $stmt->fetch();
if (!$profile) {
    flash('danger', 'User not found.');
    redirect('dashboard.php');
}
$teach = get_user_skills($id, 'teach');
$learn = get_user_skills($id, 'learn');
$pageTitle = $profile['name'] . ' - Profile';
$bodyClass = 'app-page profile-page';
$mainClass = 'app-main profile-main page-shell';
include __DIR__ . '/includes/header.php';
?>
<section class="profile-summary-card">
    <?php if (!empty($profile['profile_photo'])): ?>
        <img class="profile-avatar" src="<?= e(user_avatar($profile)) ?>" alt="<?= e($profile['name']) ?> profile photo">
    <?php else: ?>
        <span class="profile-avatar avatar-initials" aria-label="<?= e($profile['name']) ?> initials"><?= e(user_initials($profile['name'])) ?></span>
    <?php endif; ?>
    <div class="profile-summary-copy">
        <h1><?= e($profile['name']) ?></h1>
        <p class="profile-meta"><span class="role-badge"><?= e($profile['role']) ?></span><span><?= e($profile['department'] ?: 'Department not added') ?></span><?php if ($profile['semester']): ?><span><?= e($profile['semester']) ?></span><?php endif; ?></p>
        <p><?= nl2br(e($profile['bio'] ?: 'No bio added yet.')) ?></p>
        <p><strong>Rating:</strong> <?= e(avg_rating($id)) ?></p>
    </div>
</section>

<section class="profile-skill-grid">
    <article class="profile-skill-card">
        <h2>Can Teach</h2>
        <?php if (!$teach): ?>
            <div class="profile-empty">No teaching skills added.</div>
        <?php else: ?>
            <div class="skill-badge-list"><?php foreach ($teach as $skill): ?><span class="skill-badge skill-teach"><?= e($skill['skill_name']) ?></span><?php endforeach; ?></div>
        <?php endif; ?>
    </article>
    <article class="profile-skill-card">
        <h2>Want to Learn</h2>
        <?php if (!$learn): ?>
            <div class="profile-empty">No learning skills added.</div>
        <?php else: ?>
            <div class="skill-badge-list"><?php foreach ($learn as $skill): ?><span class="skill-badge skill-learn"><?= e($skill['skill_name']) ?></span><?php endforeach; ?></div>
        <?php endif; ?>
    </article>
</section>

<section class="profile-post-card">
    <h2>Recent Posts</h2>
    <div class="profile-empty">No posts yet.</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
