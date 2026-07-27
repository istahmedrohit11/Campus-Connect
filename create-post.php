<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
$pageTitle = 'Create Learning Request - Campus Connect';
$bodyClass = 'app-page create-post-page';
$mainClass = 'app-main figma-form-page page-shell';
$form = ['title' => '', 'description' => '', 'resource_url' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = trim((string)($_POST[$key] ?? ''));
    try {
        if ($form['title'] === '' || $form['description'] === '') throw new RuntimeException('Title and description are required.');
        if (strlen($form['title']) > 180) throw new RuntimeException('Title must be 180 characters or fewer.');
        $resourceUrl = valid_http_url($form['resource_url']);
        $attachment = upload_file('attachment', 'posts');
        $stmt = db()->prepare('INSERT INTO posts (user_id,title,description,resource_url,attachment,created_at) VALUES (?,?,?,?,?,NOW())');
        $stmt->execute([$user['id'], $form['title'], $form['description'], $resourceUrl, $attachment]);
        $postId = (int)db()->lastInsertId();
        flash('success', 'Learning request published successfully.');
        redirect('post-details.php?id=' . $postId);
    } catch (Throwable $e) { flash('danger', $e->getMessage()); }
}
include __DIR__ . '/includes/header.php';
?>
<section class="figma-form-card create-post-card">
    <h1>Create A New Post</h1>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="post-title">Title</label>
        <input id="post-title" type="text" name="title" maxlength="180" value="<?= e($form['title']) ?>" placeholder="What do you want to learn?" required>
        <label for="post-description">Description</label>
        <textarea id="post-description" name="description" placeholder="Describe the topic and the help you need" required><?= e($form['description']) ?></textarea>
        <label for="resource-url">Resource Link <span>(optional)</span></label>
        <input id="resource-url" type="url" name="resource_url" maxlength="500" value="<?= e($form['resource_url']) ?>" placeholder="https://example.com/resource">
        <label for="post-attachment">Add File <span>(optional, max 5 MB)</span></label>
        <input id="post-attachment" class="figma-file-input" type="file" name="attachment" accept=".png,.jpg,.jpeg,.gif,.webp,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip">
        <button class="figma-button" type="submit">Create Post</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
