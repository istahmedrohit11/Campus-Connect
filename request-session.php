<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$learner = current_user();
$mentorId = (int)($_GET['mentor_id'] ?? $_POST['mentor_id'] ?? 0);
$postId = (int)($_GET['post_id'] ?? $_POST['post_id'] ?? 0);
$commentId = (int)($_GET['comment_id'] ?? $_POST['comment_id'] ?? 0);
$mentor = user_by_id($mentorId);
if (!$mentor || $mentor['status'] !== 'active' || $mentorId === (int)$learner['id']) { flash('danger', 'A valid mentor is required.'); redirect('search.php'); }
$skills = get_user_skills($mentorId, 'teach');
if (!$skills) {
    $stmt = db()->query('SELECT * FROM skills ORDER BY skill_name');
    $skills = $stmt->fetchAll();
}
$form = ['skill_id'=>'','topic'=>'','session_date'=>'','session_time'=>'','session_type'=>'online','message'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key=>$value) $form[$key] = trim((string)($_POST[$key] ?? ''));
    try {
        $skillId = (int)$form['skill_id'];
        if ($form['topic'] === '' || $form['session_date'] === '' || $form['session_time'] === '') throw new RuntimeException('Topic, date and time are required.');
        if (strlen($form['topic']) > 180) throw new RuntimeException('Topic must be 180 characters or fewer.');
        if (!in_array($form['session_type'], ['online','offline'], true)) throw new RuntimeException('Invalid session type.');
        $requestedAt = strtotime($form['session_date'] . ' ' . $form['session_time']);
        if (!$requestedAt || $requestedAt < time() - 300) throw new RuntimeException('Please select a future date and time.');
        if ($skillId > 0) {
            $stmt = db()->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id=? AND skill_type='teach'");
            $stmt->execute([$mentorId]);
            $mentorHasTeachingSkills = (int)$stmt->fetchColumn() > 0;
            if ($mentorHasTeachingSkills) {
                $stmt = db()->prepare("SELECT 1 FROM user_skills WHERE user_id=? AND skill_id=? AND skill_type='teach' LIMIT 1");
                $stmt->execute([$mentorId, $skillId]);
            } else {
                $stmt = db()->prepare('SELECT 1 FROM skills WHERE id=? LIMIT 1');
                $stmt->execute([$skillId]);
            }
            if (!$stmt->fetchColumn()) throw new RuntimeException('Selected skill is not available from this mentor.');
        } else $skillId = null;
        $stmt = db()->prepare("INSERT INTO sessions (learner_id,mentor_id,skill_id,post_id,comment_id,topic,session_date,session_time,session_type,message,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,'pending',NOW())");
        $stmt->execute([$learner['id'],$mentorId,$skillId,$postId ?: null,$commentId ?: null,$form['topic'],$form['session_date'],$form['session_time'],$form['session_type'],$form['message'] ?: null]);
        $sessionId=(int)db()->lastInsertId();
        create_notification($mentorId,'session','New session request',$learner['name'].' requested a learning session: '.$form['topic'].'.','sessions.php#session-'.$sessionId);
        flash('success','Session request sent. It is waiting for the mentor’s response.');
        redirect('sessions.php?sent=1#session-'.$sessionId);
    } catch (Throwable $e) { flash('danger',$e->getMessage()); }
}
$pageTitle='Request Session - Campus Connect';
$bodyClass='app-page request-session-page';
$mainClass='app-main figma-form-page page-shell';
include __DIR__.'/includes/header.php';
?>
<section class="figma-form-card request-session-card">
    <div class="request-mentor"><img src="<?= e(user_avatar($mentor)) ?>" alt=""><div><span>Request a session with</span><h1><?= e($mentor['name']) ?></h1><p><?= e($mentor['department'] ?: ucfirst($mentor['role'])) ?> · <?= e(avg_rating($mentorId)) ?></p></div></div>
    <form method="post">
        <?= csrf_field() ?><input type="hidden" name="mentor_id" value="<?= $mentorId ?>"><input type="hidden" name="post_id" value="<?= $postId ?>"><input type="hidden" name="comment_id" value="<?= $commentId ?>">
        <label for="session-skill">Skill</label>
        <select id="session-skill" name="skill_id"><option value="">Choose a skill</option><?php foreach($skills as $skill): ?><option value="<?= (int)$skill['id'] ?>" <?= (int)$form['skill_id']===(int)$skill['id']?'selected':'' ?>><?= e($skill['skill_name']) ?></option><?php endforeach; ?></select>
        <label for="session-topic">Topic</label><input id="session-topic" type="text" name="topic" maxlength="180" value="<?= e($form['topic']) ?>" placeholder="What would you like help with?" required>
        <div class="figma-form-grid"><div><label for="session-date">Date</label><input id="session-date" type="date" min="<?= date('Y-m-d') ?>" name="session_date" value="<?= e($form['session_date']) ?>" required></div><div><label for="session-time">Time</label><input id="session-time" type="time" name="session_time" value="<?= e($form['session_time']) ?>" required></div></div>
        <label for="session-type">Session Type</label><select id="session-type" name="session_type"><option value="online" <?= $form['session_type']==='online'?'selected':'' ?>>Online</option><option value="offline" <?= $form['session_type']==='offline'?'selected':'' ?>>Offline</option></select>
        <label for="session-message">Message</label><textarea id="session-message" name="message" placeholder="Add any details for the mentor"><?= e($form['message']) ?></textarea>
        <button class="figma-button" type="submit">Send Request</button>
    </form>
</section>
<?php include __DIR__.'/includes/footer.php'; ?>
