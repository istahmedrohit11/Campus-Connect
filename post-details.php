<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user = current_user();
$postId = (int)($_GET['id'] ?? $_POST['post_id'] ?? 0);
$post = fetch_post($postId);
if (!$post) { flash('danger', 'Post not found.'); redirect('posts.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'delete_post') {
            if ((int)$post['user_id'] !== (int)$user['id'] && !is_admin($user)) throw new RuntimeException('You cannot delete this post.');
            delete_post_by_id($postId);
            flash('success', 'Post deleted.');
            redirect('posts.php');
        }
        if ($action === 'comment') {
            $body = trim((string)($_POST['body'] ?? ''));
            $resourceUrl = valid_http_url($_POST['resource_url'] ?? '');
            $attachment = upload_file('attachment', 'comments');
            if ($body === '' && !$resourceUrl && !$attachment) throw new RuntimeException('Write a comment or attach a link/file.');
            $stmt = db()->prepare('INSERT INTO comments (post_id,user_id,body,resource_url,attachment,created_at) VALUES (?,?,?,?,?,NOW())');
            $stmt->execute([$postId, $user['id'], $body ?: null, $resourceUrl, $attachment]);
            if ((int)$post['user_id'] !== (int)$user['id']) {
                create_notification((int)$post['user_id'], 'comment', 'New comment on your post', $user['name'] . ' commented on “' . $post['title'] . '”.', 'post-details.php?id=' . $postId);
            }
            flash('success', 'Comment added.');
            redirect('post-details.php?id=' . $postId . '#comments');
        }
    } catch (Throwable $e) { flash('danger', $e->getMessage()); redirect('post-details.php?id=' . $postId); }
}
$stmt = db()->prepare("SELECT c.*, u.name AS author_name, u.role AS author_role, u.profile_photo AS author_photo FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.created_at ASC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();
$isOwner = (int)$post['user_id'] === (int)$user['id'];
$pageTitle = $post['title'] . ' - Campus Connect';
$bodyClass = 'app-page post-detail-page';
$mainClass = 'app-main post-detail-main page-shell';
include __DIR__ . '/includes/header.php';
?>
<article class="post-detail-card">
    <div class="post-author-row">
        <a href="<?= url('profile.php?id=' . (int)$post['user_id']) ?>"><img class="mini-avatar" src="<?= e(user_avatar(['profile_photo'=>$post['author_photo']])) ?>" alt=""></a>
        <div><a class="post-author-name" href="<?= url('profile.php?id=' . (int)$post['user_id']) ?>"><?= e($post['author_name']) ?></a><span><?= e(ucfirst($post['author_role'])) ?> · <?= e(time_ago($post['created_at'])) ?></span></div>
    </div>
    <h1><?= e($post['title']) ?></h1>
    <p class="post-description"><?= nl2br(e($post['description'])) ?></p>
    <div class="resource-row">
        <?php if ($post['resource_url']): ?><a class="resource-chip" target="_blank" rel="noopener" href="<?= e($post['resource_url']) ?>">Open Resource Link</a><?php endif; ?>
        <?php if ($post['attachment']): ?><a class="resource-chip" download href="<?= url($post['attachment']) ?>">Download <?= e(attachment_label($post['attachment'])) ?></a><?php endif; ?>
    </div>
    <div class="post-detail-actions">
        <?php if (!$isOwner): ?><a class="figma-button" href="<?= url('request-session.php?mentor_id=' . (int)$post['user_id'] . '&post_id=' . $postId) ?>">Request Session</a><?php endif; ?>
        <a class="figma-button figma-button-light" href="<?= url('report.php?type=post&id=' . $postId) ?>">Report</a>
        <?php if ($isOwner || is_admin($user)): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= $postId ?>"><input type="hidden" name="action" value="delete_post"><button class="figma-button figma-button-danger" data-confirm="Delete this post and all comments?" type="submit">Delete</button></form><?php endif; ?>
    </div>
</article>
<section class="comments-panel" id="comments">
    <h2>Comments (<?= count($comments) ?>)</h2>
    <?php if (!$comments): ?><div class="comment-empty">No comments yet. Share a useful answer or resource.</div><?php endif; ?>
    <?php foreach ($comments as $comment): ?>
        <article class="comment-card">
            <img class="mini-avatar" src="<?= e(user_avatar(['profile_photo'=>$comment['author_photo']])) ?>" alt="">
            <div class="comment-body"><div class="comment-title-row"><strong><?= e($comment['author_name']) ?></strong><span><?= e(ucfirst($comment['author_role'])) ?> · <?= e(time_ago($comment['created_at'])) ?></span></div>
                <?php if ($comment['body']): ?><p><?= nl2br(e($comment['body'])) ?></p><?php endif; ?>
                <div class="resource-row"><?php if ($comment['resource_url']): ?><a target="_blank" rel="noopener" href="<?= e($comment['resource_url']) ?>">Resource link</a><?php endif; ?><?php if ($comment['attachment']): ?><a download href="<?= url($comment['attachment']) ?>"><?= e(attachment_label($comment['attachment'])) ?></a><?php endif; ?></div>
                <div class="comment-actions"><?php if ((int)$comment['user_id'] !== (int)$user['id']): ?><a href="<?= url('request-session.php?mentor_id=' . (int)$comment['user_id'] . '&post_id=' . $postId . '&comment_id=' . (int)$comment['id']) ?>">Request Session</a><?php endif; ?><a href="<?= url('report.php?type=comment&id=' . (int)$comment['id']) ?>">Report</a></div>
            </div>
        </article>
    <?php endforeach; ?>
    <form class="comment-form" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?><input type="hidden" name="post_id" value="<?= $postId ?>"><input type="hidden" name="action" value="comment">
        <h3>Add a Comment</h3>
        <textarea name="body" placeholder="Write your comment..."></textarea>
        <div class="comment-form-grid"><input type="url" name="resource_url" maxlength="500" placeholder="Optional resource URL"><input type="file" name="attachment" aria-label="Optional attachment"></div>
        <button class="figma-button" type="submit">Post Comment</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
