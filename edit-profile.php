<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
$pageTitle = 'Edit Profile - Campus Connect';
$bodyClass = 'app-page edit-profile-page';
$mainClass = 'app-main edit-profile-main';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $name = trim($_POST['name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        if ($name === '') throw new RuntimeException('Name is required.');
        if (strlen($name) > 120 || strlen($department) > 120 || strlen($semester) > 50) {
            throw new RuntimeException('One or more profile fields are too long.');
        }
        $photo = upload_profile_photo();
        if ($photo) {
            $oldPhoto = $user['profile_photo'] ?? null;
            $stmt = db()->prepare('UPDATE users SET name = ?, department = ?, semester = ?, bio = ?, profile_photo = ? WHERE id = ?');
            $stmt->execute([$name, $department, $semester, $bio, $photo, $user['id']]);
            if ($oldPhoto && $oldPhoto !== $photo) delete_uploaded_file($oldPhoto);
        } else {
            $stmt = db()->prepare('UPDATE users SET name = ?, department = ?, semester = ?, bio = ? WHERE id = ?');
            $stmt->execute([$name, $department, $semester, $bio, $user['id']]);
        }
        flash('success', 'Profile updated successfully.');
        redirect('profile.php?id=' . $user['id']);
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="edit-profile-stage">
    <form class="edit-profile-card" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <h1>Edit Profile</h1>

        <label for="profile-name">Name</label>
        <input id="profile-name" type="text" name="name" value="<?= e($user['name']) ?>" maxlength="120" required>

        <div class="edit-profile-grid">
            <div>
                <label for="profile-department">Department</label>
                <input id="profile-department" type="text" name="department" value="<?= e($user['department']) ?>" maxlength="120">
            </div>
            <div>
                <label for="profile-semester">Semester/Year</label>
                <input id="profile-semester" type="text" name="semester" value="<?= e($user['semester']) ?>" maxlength="50">
            </div>
        </div>

        <label for="profile-bio">Short Bio</label>
        <textarea id="profile-bio" name="bio" maxlength="2000"><?= e($user['bio']) ?></textarea>

        <label for="profile-photo">Profile Photo</label>
        <input id="profile-photo" class="profile-file-input" type="file" name="profile_photo" accept=".png,.jpg,.jpeg,.gif,.webp,image/png,image/jpeg,image/gif,image/webp">
        <p class="upload-help">PNG, JPG, GIF or WEBP. Maximum 5 MB.</p>

        <button class="save-profile-button" type="submit">Save Profile</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
