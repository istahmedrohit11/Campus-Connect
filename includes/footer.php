</main>
<footer class="site-footer" id="contact">
    <p>&copy; <?= date('Y') ?> Campus Connect. Student skill exchange platform.</p>
</footer>
<?php $scriptVersion = (string) (@filemtime(__DIR__ . '/../assets/js/main.js') ?: time()); ?>
<script src="<?= url('assets/js/main.js') ?>?v=<?= e($scriptVersion) ?>"></script>
</body>
</html>
