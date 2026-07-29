<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user=current_user();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $sessionId=(int)($_POST['session_id']??0);
    $action=(string)($_POST['action']??'');
    $stmt=db()->prepare('SELECT * FROM sessions WHERE id=? LIMIT 1'); $stmt->execute([$sessionId]); $session=$stmt->fetch();
    try {
        if(!$session || ((int)$session['learner_id']!==(int)$user['id'] && (int)$session['mentor_id']!==(int)$user['id'])) throw new RuntimeException('Session not found.');
        $newStatus=''; $notice=''; $recipient=0;
        if(in_array($action,['accept','reject'],true)) {
            if((int)$session['mentor_id']!==(int)$user['id'] || $session['status']!=='pending') throw new RuntimeException('This request can no longer be changed.');
            $newStatus=$action==='accept'?'accepted':'rejected'; $recipient=(int)$session['learner_id'];
            $notice=$action==='accept'?'Your session request was accepted.':'Your session request was declined.';
        } elseif($action==='cancel') {
            if((int)$session['learner_id']!==(int)$user['id'] || !in_array($session['status'],['pending','accepted'],true)) throw new RuntimeException('This session cannot be cancelled.');
            $newStatus='cancelled'; $recipient=(int)$session['mentor_id']; $notice='A learning session was cancelled by the learner.';
        } elseif($action==='complete') {
            if($session['status']!=='accepted') throw new RuntimeException('Only accepted sessions can be completed.');
            $newStatus='completed'; $recipient=(int)$session['learner_id']===(int)$user['id']?(int)$session['mentor_id']:(int)$session['learner_id']; $notice='A learning session was marked completed. You can now leave feedback.';
        } else throw new RuntimeException('Invalid session action.');
        if($newStatus==='completed') db()->prepare('UPDATE sessions SET status=?, completed_at=NOW() WHERE id=?')->execute([$newStatus,$sessionId]);
        else db()->prepare('UPDATE sessions SET status=? WHERE id=?')->execute([$newStatus,$sessionId]);
        create_notification($recipient,'session','Session '.ucfirst($newStatus),$notice,'sessions.php#session-'.$sessionId);
        flash('success',$notice);
    } catch(Throwable $e){flash('danger',$e->getMessage());}
    redirect('sessions.php#session-'.$sessionId);
}
$stmt=db()->prepare("SELECT s.*, learner.name learner_name, learner.profile_photo learner_photo, mentor.name mentor_name, mentor.profile_photo mentor_photo, sk.skill_name, p.title post_title FROM sessions s JOIN users learner ON learner.id=s.learner_id JOIN users mentor ON mentor.id=s.mentor_id LEFT JOIN skills sk ON sk.id=s.skill_id LEFT JOIN posts p ON p.id=s.post_id WHERE s.learner_id=? OR s.mentor_id=? ORDER BY FIELD(s.status,'pending','accepted','completed','rejected','cancelled'), s.created_at DESC");
$stmt->execute([$user['id'],$user['id']]); $sessions=$stmt->fetchAll();
$pageTitle='Sessions - Campus Connect'; $bodyClass='app-page sessions-page'; $mainClass='app-main sessions-main page-shell';
include __DIR__.'/includes/header.php';
?>
<?php if(isset($_GET['sent'])): ?><div class="session-success-banner"><strong>Request sent successfully!</strong><span>Your request is pending. You may cancel it before the mentor responds.</span></div><?php endif; ?>
<?php if(isset($_GET['rated'])): ?><div class="session-success-banner"><strong>Feedback submitted successfully!</strong><span>The rating is now included in the other participant’s profile average.</span></div><?php endif; ?>
<div class="sessions-heading"><div><h1>My Sessions</h1><p>Manage every learning request from pending to completed.</p></div><a class="figma-button" href="<?= url('search.php') ?>">Find a Mentor</a></div>
<?php if(!$sessions): ?><section class="figma-empty-state"><img src="<?= url('assets/images/icon-calendar.png') ?>" alt=""><h2>No sessions yet</h2><p>Search for a mentor and request your first learning session.</p></section><?php else: ?>
<section class="session-list">
<?php foreach($sessions as $session): $other=session_other_user($session,(int)$user['id']); $isMentor=(int)$session['mentor_id']===(int)$user['id']; ?>
<article id="session-<?= (int)$session['id'] ?>" class="session-card status-<?= e($session['status']) ?>">
    <div class="session-card-top"><div><span class="session-status-badge"><?= e(session_status_label($session['status'])) ?></span><h2><?= e($session['topic']) ?></h2><p>With <a href="<?= url('profile.php?id='.$other['id']) ?>"><?= e($other['name']) ?></a> · You are the <?= e($isMentor?'Mentor':'Learner') ?></p></div><span class="session-date-box"><strong><?= e(date('d',strtotime($session['session_date']))) ?></strong><?= e(date('M',strtotime($session['session_date']))) ?></span></div>
    <div class="session-info-grid"><span><b>Skill</b><?= e($session['skill_name']?:'General') ?></span><span><b>Time</b><?= e(date('g:i A',strtotime($session['session_time']))) ?></span><span><b>Type</b><?= e(ucfirst($session['session_type'])) ?></span><?php if($session['post_title']): ?><span><b>Related Post</b><?= e($session['post_title']) ?></span><?php endif; ?></div>
    <?php if($session['message']): ?><p class="session-message"><?= nl2br(e($session['message'])) ?></p><?php endif; ?>
    <div class="session-actions">
    <?php if($isMentor && $session['status']==='pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>"><button name="action" value="accept" class="figma-button figma-button-success">Accept</button><button name="action" value="reject" class="figma-button figma-button-danger">Reject</button></form><?php endif; ?>
    <?php if(!$isMentor && in_array($session['status'],['pending','accepted'],true)): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>"><button name="action" value="cancel" data-confirm="Cancel this session?" class="figma-button figma-button-danger">Cancel Request</button></form><?php endif; ?>
    <?php if($session['status']==='accepted'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>"><button name="action" value="complete" data-confirm="Mark this session completed?" class="figma-button figma-button-success">Mark Completed</button></form><?php endif; ?>
    <?php if($session['status']==='completed' && !has_rated_session((int)$session['id'],(int)$user['id'])): ?><a class="figma-button" href="<?= url('rate-session.php?id='.(int)$session['id']) ?>">Rate & Feedback</a><?php elseif($session['status']==='completed'): ?><span class="rating-complete-label">Feedback submitted</span><?php endif; ?>
    </div>
</article>
<?php endforeach; ?>
</section><?php endif; ?>
<?php include __DIR__.'/includes/footer.php'; ?>
