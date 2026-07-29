<?php
require_once __DIR__.'/../includes/init.php';require_admin();
$counts=[];foreach(['users','posts','skills','sessions','reports'] as $table){$counts[$table]=(int)db()->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();}
$counts['pending_reports']=(int)db()->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$pageTitle='Admin Dashboard - Campus Connect';include __DIR__.'/_admin_header.php';
?>
<div class="admin-title"><div><h1>Platform Overview</h1><p>Manage Campus Connect from one dashboard.</p></div></div><section class="admin-stat-grid"><?php foreach(['users'=>'Users','posts'=>'Posts','skills'=>'Skills','sessions'=>'Sessions','pending_reports'=>'Pending Reports'] as $key=>$label): ?><article><strong><?= $counts[$key] ?></strong><span><?= e($label) ?></span></article><?php endforeach; ?></section><section class="admin-panel"><h2>Quick Actions</h2><div class="admin-quick-links"><a href="<?= url('admin/users.php') ?>">Manage Users</a><a href="<?= url('admin/posts.php') ?>">Moderate Posts</a><a href="<?= url('admin/skills.php') ?>">Manage Skills</a><a href="<?= url('admin/reports.php') ?>">Review Reports</a></div></section>
<?php include __DIR__.'/_admin_footer.php'; ?>
