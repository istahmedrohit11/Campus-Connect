<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$current = current_user();
$id = (int)($_GET['id'] ?? $current['id']);
$profile = user_by_id($id);
if (!$profile) { flash('danger', 'User not found.'); redirect('dashboard.php'); }
$teach = get_user_skills($id, 'teach');
$learn = get_user_skills($id, 'learn');
$stmt = db()->prepare("SELECT p.*, (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comment_count FROM posts p WHERE p.user_id=? ORDER BY p.created_at DESC LIMIT 3");
$stmt->execute([$id]);
$recentPosts = $stmt->fetchAll();
$pageTitle = $profile['name'] . ' - Profile';
$bodyClass = 'app-page profile-page';
$mainClass = 'app-main profile-main';
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
        <p class="profile-meta"><span><?= e($profile['department'] ?: 'Department not added') ?></span><span><?= e($profile['semester'] ?: 'Semester/year not added') ?></span><span class="role-badge"><?= e($profile['role']) ?></span></p>
        <p><?= nl2br(e($profile['bio'] ?: 'No biography added yet.')) ?></p>
        <p><strong>Average Rating:</strong> <?= e(avg_rating($id)) ?></p>
        <div class="profile-actions">
            <?php if ($id === (int)$current['id']): ?>
                <a class="figma-button" href="<?= url('edit-profile.php') ?>">Edit Profile</a>
                <a class="figma-button figma-button-light" href="<?= url('skills.php') ?>">Manage Skills</a>
            <?php else: ?>
                <a class="figma-button" href="<?= url('request-session.php?mentor_id=' . $id) ?>">Request Session</a>
                <a class="figma-button figma-button-danger" href="<?= url('report.php?type=user&id=' . $id) ?>">Report User</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="profile-skill-grid">
    <article class="profile-skill-card">
        <h2>Skills I Can Teach</h2>
        <?php if (!$teach): ?><div class="profile-empty">No teaching skills added.</div><?php else: ?><div class="skill-badge-list"><?php foreach ($teach as $skill): ?><span class="skill-badge skill-teach"><?= e($skill['skill_name']) ?></span><?php endforeach; ?></div><?php endif; ?>
    </article>
    <article class="profile-skill-card">
        <h2>Skills I Want to Learn</h2>
        <?php if (!$learn): ?><div class="profile-empty">No learning skills added.</div><?php else: ?><div class="skill-badge-list"><?php foreach ($learn as $skill): ?><span class="skill-badge skill-learn"><?= e($skill['skill_name']) ?></span><?php endforeach; ?></div><?php endif; ?>
    </article>
</section>
<section class="profile-post-card">
    <div class="section-heading-row"><h2>Recent Learning Requests</h2><?php if ($id === (int)$current['id']): ?><a href="<?= url('create-post.php') ?>">Create post</a><?php endif; ?></div>
    <?php if (!$recentPosts): ?>
        <div class="profile-empty">No learning request posted yet.</div>
    <?php else: ?>
        <div class="profile-post-list"><?php foreach ($recentPosts as $post): ?><a href="<?= url('post-details.php?id=' . (int)$post['id']) ?>"><strong><?= e($post['title']) ?></strong><span><?= e(time_ago($post['created_at'])) ?> · <?= (int)$post['comment_count'] ?> comments</span></a><?php endforeach; ?></div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
