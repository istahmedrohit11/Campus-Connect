<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$user=current_user(); $type=(string)($_GET['type']??$_POST['type']??''); $id=(int)($_GET['id']??$_POST['target_id']??0); $target=report_target($type,$id);
if(!$target){flash('danger','The item you want to report was not found.');redirect('dashboard.php');}
$reason=(string)($_POST['reason']??'');$details=trim((string)($_POST['details']??''));
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
  $allowed=['Spam or misleading','Harassment or abuse','Inappropriate content','Fraud or safety concern','Other'];
  if(!in_array($reason,$allowed,true))throw new RuntimeException('Choose a report reason.');
  $stmt=db()->prepare("SELECT id FROM reports WHERE reporter_id=? AND target_type=? AND target_id=? AND status='pending' LIMIT 1");$stmt->execute([$user['id'],$type,$id]);if($stmt->fetchColumn())throw new RuntimeException('You already have a pending report for this item.');
  db()->prepare('INSERT INTO reports (reporter_id,target_type,target_id,reason,details,status,created_at) VALUES (?,?,?,?,?,\'pending\',NOW())')->execute([$user['id'],$type,$id,$reason,$details?:null]);
  flash('success','Your report was submitted for admin review.');redirect($type==='post'?'post-details.php?id='.$id:'dashboard.php');
 }catch(Throwable $e){flash('danger',$e->getMessage());}
}
$pageTitle='Submit Report - Campus Connect';$bodyClass='app-page report-page';$mainClass='app-main figma-form-page page-shell';include __DIR__.'/includes/header.php';
?>
<section class="figma-form-card report-card"><h1>Submit a Report</h1><p>You are reporting <strong><?= e(ucfirst($type)) ?>: <?= e($target['label']) ?></strong>.</p><form method="post"><?= csrf_field() ?><input type="hidden" name="type" value="<?= e($type) ?>"><input type="hidden" name="target_id" value="<?= $id ?>"><label for="report-reason">Reason</label><select id="report-reason" name="reason" required><option value="">Choose a reason</option><?php foreach(['Spam or misleading','Harassment or abuse','Inappropriate content','Fraud or safety concern','Other'] as $item): ?><option <?= $reason===$item?'selected':'' ?>><?= e($item) ?></option><?php endforeach; ?></select><label for="report-details">Details</label><textarea id="report-details" name="details" placeholder="Explain what happened"><?= e($details) ?></textarea><button class="figma-button figma-button-danger" type="submit">Submit Report</button></form></section>
<?php include __DIR__.'/includes/footer.php'; ?>
