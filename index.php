<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Campus Connect - Student Skill Exchange Platform';
$bodyClass = 'landing-page';
$mainClass = 'landing-main';
include __DIR__ . '/includes/header.php';
?>
<section class="landing-hero">
    <div class="landing-hero-inner">
        <div class="landing-copy">
            <h1>Learn from students.<br>Teach what you know.</h1>
            <p>Campus Connect is a campus-based skill exchange platform where students can create profiles, share skills, post learning requests, request sessions, comment, upload resources, and rate mentors.</p>
        </div>

        <aside class="capability-card" aria-labelledby="capability-title">
            <h2 id="capability-title">What students can do</h2>
            <div class="capability-grid">
                <span><img src="<?= url('assets/images/icon-graduation.png') ?>" alt="">Add Teach Skills</span>
                <span><img src="<?= url('assets/images/icon-search.png') ?>" alt="">Search Mentors</span>
                <span><img src="<?= url('assets/images/icon-book.png') ?>" alt="">Add Learn Skills</span>
                <span><img src="<?= url('assets/images/icon-post.png') ?>" alt="">Post Request</span>
                <span><img src="<?= url('assets/images/icon-calendar.png') ?>" alt="">Request Sessions</span>
                <span><img src="<?= url('assets/images/icon-folder.png') ?>" alt="">Upload Files</span>
                <span><img src="<?= url('assets/images/icon-chat.png') ?>" alt="">Comment</span>
                <span><img src="<?= url('assets/images/icon-rating.png') ?>" alt="">Give Ratings</span>
            </div>
        </aside>

        <div class="landing-features">
            <article class="landing-feature">
                <h2>Peer Learning</h2>
                <p>Students can exchange skills like coding, design, Excel, presentation, freelancing and public speaking.</p>
            </article>
            <article class="landing-feature">
                <h2>Teacher Support</h2>
                <p>Teachers can comment on posts, guide students, and share PDF, Word, PPT or useful links.</p>
            </article>
            <article class="landing-feature">
                <h2>Campus Community</h2>
                <p>Ratings, session records and admin control make learning more organized and trustworthy.</p>
            </article>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
