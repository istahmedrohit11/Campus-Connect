<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
$pageTitle = 'Manage Skills - Campus Connect';
$bodyClass = 'app-page skills-page';
$mainClass = 'app-main skills-main page-shell';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $skillName = trim($_POST['skill_name'] ?? '');
            $category = trim($_POST['category'] ?? 'General');
            $type = $_POST['skill_type'] ?? 'teach';
            if (strlen($skillName) > 120 || strlen($category) > 120) throw new RuntimeException('Skill fields must be 120 characters or fewer.');
            if (!in_array($type, ['teach', 'learn'], true)) throw new RuntimeException('Invalid skill type.');
            $skillId = get_or_create_skill($skillName, $category);
            $stmt = db()->prepare('INSERT IGNORE INTO user_skills (user_id, skill_id, skill_type) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $skillId, $type]);
            flash('success', 'Skill added successfully.');
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = db()->prepare('DELETE FROM user_skills WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $user['id']]);
            flash('success', 'Skill removed.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }
    redirect('skills.php');
}

$stmt = db()->prepare('SELECT us.id AS user_skill_id, us.skill_type, s.skill_name, s.category FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = ? ORDER BY us.skill_type, s.skill_name');
$stmt->execute([$user['id']]);
$skills = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title skills-title">Manage Skills</h1>

<section class="skills-card add-skill-card">
    <h2>Add Skill</h2>
    <form method="post" class="add-skill-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div>
            <label for="skill-name">Skill Name</label>
            <input id="skill-name" type="text" name="skill_name" placeholder="Python, Photoshop, Excel" maxlength="120" required>
        </div>
        <div>
            <label for="skill-category">Catagory</label>
            <input id="skill-category" type="text" name="category" placeholder="Coding, Design, Career" maxlength="120">
        </div>
        <div>
            <label for="skill-type">Type</label>
            <select id="skill-type" name="skill_type">
                <option value="teach">I can teach</option>
                <option value="learn">I want to learn</option>
            </select>
        </div>
        <button class="add-skill-button" type="submit">Add</button>
    </form>
</section>

<section class="skills-card my-skills-card">
    <h2>My Skill</h2>
    <?php if (!$skills): ?>
        <div class="skills-empty">No skills added yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="skills-table">
                <thead><tr><th>Skill</th><th>Catagory</th><th>Type</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($skills as $skill): ?>
                    <tr>
                        <td><?= e($skill['skill_name']) ?></td>
                        <td><?= e($skill['category']) ?></td>
                        <td><span class="table-skill-type <?= $skill['skill_type'] === 'teach' ? 'teach' : 'learn' ?>"><?= e(ucfirst($skill['skill_type'])) ?></span></td>
                        <td>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$skill['user_skill_id'] ?>">
                                <button class="remove-skill-button" data-confirm="Remove this skill?" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
