<?php snippet("header"); ?>

<style>
/* Override body margin for home page */
body { margin-bottom: 0 !important; }
</style>

<main>
<div class="home-grid">

    <!-- ========== #01 HERO ========== -->
    <div class="grid-row grid-row--hero">
        <div class="grid-label">
            <span class="grid-label-text">#01</span>
            <span class="grid-label-name">START</span>
        </div>
        <div class="grid-cell grid-cell--hero" style="grid-column: 2 / -1;">
            <?php
            $heroBg = $page->hero_background_image()->toFile();
            if ($heroBg):
            ?>
            <div class="hero-bg" aria-hidden="true">
                <img src="<?= $heroBg->url() ?>" alt="" loading="eager">
            </div>
            <?php endif; ?>

            <!-- Micrographics -->
            <div class="micro" aria-hidden="true">
                <span class="micro-crosshair" style="top: 12%; left: 8%;"></span>
                <span class="micro-crosshair" style="top: 78%; right: 6%;"></span>
                <span class="micro-coord" style="top: 11%; left: 12%;">49.009 N</span>
                <span class="micro-coord" style="top: 79%; right: 10%;">8.414 E</span>
                <span class="micro-tag" style="bottom: 14%; left: 6%;">KA—2013</span>
                <span class="micro-tag" style="top: 8%; right: 4%;">BSKA</span>
                <span class="micro-bracket micro-bracket--tl" style="top: 20%; right: 12%;"></span>
                <span class="micro-bracket micro-bracket--br" style="bottom: 22%; right: 12%;"></span>
                <span class="micro-rule" style="bottom: 32%; left: 4%;"></span>
                <span class="micro-dot-grid" style="top: 40%; right: 3%;"></span>
            </div>

            <div class="hero-logo" aria-hidden="true">
                <img src="<?= url('assets/svg/block-wm.svg') ?>" alt="">
            </div>

            <h1 class="hero-title reveal-up" lang="de">
                <?= $page->hero_title()->or('SO SIEHT GEMEINSCHAFT AUS.')->html() ?>
            </h1>

            <?php if ($page->hero_subtext()->isNotEmpty()): ?>
            <div class="hero-sub reveal-up" style="--delay: 0.5s">
                <div class="hero-sub-body"><?= $page->hero_subtext()->kt() ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== #02 PROJECTS ========== -->
    <?php $projects = $page->projects()->toStructure(); ?>
    <?php foreach ($projects as $pi => $project):
        $bgImage = $project->background_image()->toFile();
        $stats = $project->stats()->toStructure();
        $isEven = $pi % 2 === 0;
    ?>
    <div class="grid-row grid-row--project">
        <div class="grid-label">
            <?php if ($pi === 0): ?>
            <span class="grid-label-text">#02</span>
            <span class="grid-label-name">PROJEKTE</span>
            <?php endif; ?>
        </div>
        <?php if ($isEven): ?>
        <!-- Text left, Image right -->
        <div class="grid-cell grid-cell--project-text reveal-up">
            <h2 class="project-title"><?= $project->title()->html() ?></h2>
            <?php if ($project->tagline()->isNotEmpty()): ?>
            <p class="project-tagline"><?= $project->tagline()->html() ?></p>
            <?php endif; ?>
            <?php if ($stats->isNotEmpty()): ?>
            <div class="project-stats">
                <?php foreach ($stats as $stat): ?>
                <div class="project-stat">
                    <span class="project-stat-value"><?= $stat->value() ?></span>
                    <span class="project-stat-label"><?= $stat->label()->html() ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($project->cta_text()->isNotEmpty()): ?>
            <a href="<?= $project->cta_link()->toUrl() ?? '#' ?>" class="project-cta">
                <?= $project->cta_text()->html() ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <div class="grid-cell grid-cell--project-image reveal-up" style="--delay: 0.1s">
            <?php if ($bgImage): ?>
            <img src="<?= $bgImage->url() ?>" alt="<?= $project->title()->html() ?>" loading="lazy">
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Image left, Text right -->
        <div class="grid-cell grid-cell--project-image reveal-up">
            <?php if ($bgImage): ?>
            <img src="<?= $bgImage->url() ?>" alt="<?= $project->title()->html() ?>" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="grid-cell grid-cell--project-text reveal-up" style="--delay: 0.1s">
            <h2 class="project-title"><?= $project->title()->html() ?></h2>
            <?php if ($project->tagline()->isNotEmpty()): ?>
            <p class="project-tagline"><?= $project->tagline()->html() ?></p>
            <?php endif; ?>
            <?php if ($stats->isNotEmpty()): ?>
            <div class="project-stats">
                <?php foreach ($stats as $stat): ?>
                <div class="project-stat">
                    <span class="project-stat-value"><?= $stat->value() ?></span>
                    <span class="project-stat-label"><?= $stat->label()->html() ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($project->cta_text()->isNotEmpty()): ?>
            <a href="<?= $project->cta_link()->toUrl() ?? '#' ?>" class="project-cta">
                <?= $project->cta_text()->html() ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- ========== #03 STATS ========== -->
    <div class="grid-row grid-row--stats">
        <div class="grid-label">
            <span class="grid-label-text">#03</span>
            <span class="grid-label-name">ZAHLEN</span>
        </div>
        <div class="grid-cell grid-cell--stats" style="grid-column: 2 / -1;">
            <?php if ($page->stats_section_title()->isNotEmpty()): ?>
            <h2 class="stats-title reveal-up"><?= $page->stats_section_title()->html() ?></h2>
            <?php endif; ?>
            <div class="stats-row">
                <?php foreach ($page->stats()->toStructure() as $i => $stat): ?>
                <div class="stats-item reveal-up" style="--delay: <?= $i * 0.1 ?>s">
                    <span class="stats-value counter" data-target="<?= preg_replace('/[^0-9]/', '', $stat->value()) ?>"><?= $stat->value() ?></span>
                    <span class="stats-label"><?= $stat->label()->html() ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ========== #04 VOLUNTEER ========== -->
    <div class="grid-row grid-row--volunteer">
        <div class="grid-label grid-label--light">
            <span class="grid-label-text">#04</span>
            <span class="grid-label-name">MITMACHEN</span>
        </div>
        <div class="grid-cell grid-cell--volunteer" style="grid-column: 2 / -1;">
            <h2 class="volunteer-title reveal-up">
                <?= $page->volunteer_title()->or('WIR BRAUCHEN DICH.')->html() ?>
            </h2>
            <?php if ($page->volunteer_subtext()->isNotEmpty()): ?>
            <p class="volunteer-subtext reveal-up" style="--delay: 0.1s"><?= $page->volunteer_subtext()->kt() ?></p>
            <?php endif; ?>
            <?php if ($page->volunteer_cta_text()->isNotEmpty()): ?>
            <a href="<?= $page->volunteer_cta_link()->toUrl() ?? '#' ?>" class="volunteer-cta reveal-up" style="--delay: 0.2s">
                <?= $page->volunteer_cta_text()->html() ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== #05 LOCATION ========== -->
    <div class="grid-row grid-row--location">
        <div class="grid-label">
            <span class="grid-label-text">#05</span>
            <span class="grid-label-name">KONTAKT</span>
        </div>
        <div class="grid-cell grid-cell--location-info reveal-up">
            <h2 class="location-title"><?= $page->location_title()->or('FINDE UNS')->html() ?></h2>

            <?php if ($page->location_address()->isNotEmpty()): ?>
            <address class="location-address"><?= $page->location_address()->html() ?></address>
            <?php endif; ?>

            <?php if ($page->location_hours()->isNotEmpty()): ?>
            <div class="location-hours"><?= $page->location_hours()->kt() ?></div>
            <?php endif; ?>

            <div class="location-contact">
                <?php if ($page->location_contact_email()->isNotEmpty()): ?>
                <a href="mailto:<?= $page->location_contact_email() ?>" class="contact-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <?= $page->location_contact_email() ?>
                </a>
                <?php endif; ?>
                <?php if ($page->location_contact_phone()->isNotEmpty()): ?>
                <a href="tel:<?= $page->location_contact_phone() ?>" class="contact-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <?= $page->location_contact_phone() ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid-cell grid-cell--location-map reveal-up" style="--delay: 0.15s">
            <?php if ($page->location_map_embed()->isNotEmpty()): ?>
            <div class="location-map-frame">
                <?= $page->location_map_embed() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== FOOTER ========== -->
    <div class="grid-row grid-row--footer">
        <div class="grid-label"></div>
        <div class="grid-cell grid-cell--footer" style="grid-column: 2 / -1;">
            <a href="/colophon" class="footer-link">IMPRESSUM</a>
            <span class="footer-sep" aria-hidden="true">/</span>
            <a href="/privacy" class="footer-link">DATENSCHUTZ</a>
            <span class="footer-sep" aria-hidden="true">/</span>
            <span class="footer-copy">&copy; <?= date('Y') ?> B&Uuml;RGERSTIFTUNG KARLSRUHE</span>
        </div>
    </div>

</div>

<!-- Scroll-driven spinning circle mark -->
<div class="circle-mark" aria-hidden="true">
    <?php echo file_get_contents($kirby->root('assets') . '/svg/circletext.svg'); ?>
</div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Scroll-reveal via IntersectionObserver
    const revealEls = document.querySelectorAll('.reveal-up');
    const revealObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.08, rootMargin: '-40px' });

    revealEls.forEach(function(el) { revealObs.observe(el); });

    // Simple counter animation (no anime.js)
    var counters = document.querySelectorAll('.counter');
    var counterObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                entry.target.classList.add('counted');
                var el = entry.target;
                var target = parseInt(el.dataset.target, 10);
                var display = el.textContent;
                var suffix = display.replace(/[0-9]/g, '');
                var start = 0;
                var duration = 1800;
                var startTime = null;

                function step(ts) {
                    if (!startTime) startTime = ts;
                    var progress = Math.min((ts - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(eased * target) + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(c) { counterObs.observe(c); });

    // Scroll-driven rotation for circle mark
    var circleMark = document.querySelector('.circle-mark');
    if (circleMark) {
        var svg = circleMark.querySelector('svg');
        if (svg) {
            var currentRotation = 0;
            var lastScroll = window.scrollY;

            function updateCircleRotation() {
                var scrollY = window.scrollY;
                var delta = scrollY - lastScroll;
                currentRotation += delta * 0.15;
                lastScroll = scrollY;
                svg.style.transform = 'rotate(' + currentRotation + 'deg)';
                requestAnimationFrame(updateCircleRotation);
            }
            requestAnimationFrame(updateCircleRotation);
        }
    }
});
</script>

</body>
</html>
