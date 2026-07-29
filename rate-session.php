<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user=current_user(); $sessionId=(int)($_GET['id']??$_POST['session_id']??0);
$stmt=db()->prepare("SELECT s.*, learner.name learner_name, mentor.name mentor_name FROM sessions s JOIN users learner ON learner.id=s.learner_id JOIN users mentor ON mentor.id=s.mentor_id WHERE s.id=? LIMIT 1"); $stmt->execute([$sessionId]); $session=$stmt->fetch();
if(!$session || $session['status']!=='completed' || ((int)$session['learner_id']!==(int)$user['id'] && (int)$session['mentor_id']!==(int)$user['id'])) {flash('danger','Completed session not found.');redirect('sessions.php');}
if(has_rated_session($sessionId,(int)$user['id'])) {flash('warning','You already rated this session.');redirect('sessions.php#session-'.$sessionId);}
$receiverId=(int)$session['learner_id']===(int)$user['id']?(int)$session['mentor_id']:(int)$session['learner_id'];
$receiverName=(int)$session['learner_id']===(int)$user['id']?$session['mentor_name']:$session['learner_name'];
$rating=(int)($_POST['rating']??0); $feedback=trim((string)($_POST['feedback']??''));
if($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    try {
        if($rating<1 || $rating>5) throw new RuntimeException('Choose a rating from 1 to 5 stars.');
        $stmt=db()->prepare('INSERT INTO ratings (session_id,rater_id,receiver_id,rating,feedback,created_at) VALUES (?,?,?,?,?,NOW())');
        $stmt->execute([$sessionId,$user['id'],$receiverId,$rating,$feedback?:null]);
        create_notification($receiverId,'rating','New rating received',$user['name'].' gave you '.$rating.' star'.($rating===1?'':'s').' for “'.$session['topic'].'”.','profile.php?id='.$receiverId);
        flash('success','Thank you! Your rating and feedback were submitted.');
        redirect('sessions.php?rated=1#session-'.$sessionId);
    } catch(PDOException $e){flash('danger','You already rated this session.');} catch(Throwable $e){flash('danger',$e->getMessage());}
}
$pageTitle='Rating and Feedback - Campus Connect';$bodyClass='app-page rating-page';$mainClass='app-main figma-form-page page-shell';include __DIR__.'/includes/header.php';
?>
<section class="figma-form-card rating-card">
    <h1>Rating & Feedback</h1><p class="rating-intro">How was your learning session with <strong><?= e($receiverName) ?></strong>?</p>
    <form method="post"><input type="hidden" name="session_id" value="<?= $sessionId ?>"><?= csrf_field() ?>
        <fieldset class="star-rating"><legend>Select Rating</legend><?php for($i=5;$i>=1;$i--): ?><input id="star-<?= $i ?>" type="radio" name="rating" value="<?= $i ?>" <?= $rating===$i?'checked':'' ?> required><label for="star-<?= $i ?>" title="<?= $i ?> stars">★</label><?php endfor; ?></fieldset>
        <div class="rating-caption" data-rating-caption>Choose 1 to 5 stars</div>
        <label for="rating-feedback">Your Feedback</label><textarea id="rating-feedback" name="feedback" placeholder="Share what went well and what could improve"><?= e($feedback) ?></textarea>
        <button class="figma-button" type="submit">Submit Feedback</button>
    </form>
</section>
<?php include __DIR__.'/includes/footer.php'; ?>
