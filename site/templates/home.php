<?php snippet("header"); ?>

<style>
/* Override body margin for home page scroll */
body { margin-bottom: 0 !important; }
</style>

<?php
// Collect all section IDs for minimap
$sections = [
    ['id' => 'hero', 'label' => 'START'],
];

// Add project sections
foreach ($page->projects()->toStructure() as $project) {
    $sections[] = [
        'id' => 'project-' . Str::slug($project->title()),
        'label' => $project->nav_label()->or($project->title())->value()
    ];
}

// Add fixed sections
$sections[] = ['id' => 'stats', 'label' => 'ZAHLEN'];
$sections[] = ['id' => 'volunteer', 'label' => 'MITMACHEN'];
$sections[] = ['id' => 'location', 'label' => 'KONTAKT'];

$heroImages = $page->images()->shuffle()->limit(20);
?>

<main class="home-scroll-container" tabindex="-1">

    <!-- ========== MINIMAP NAVIGATION ========== -->
    <nav class="minimap" aria-label="Seitennavigation">
        <div class="minimap-track">
            <?php foreach ($sections as $i => $section): ?>
            <a href="#<?= $section['id'] ?>"
               class="minimap-dot"
               data-section="<?= $section['id'] ?>"
               aria-label="<?= $section['label'] ?>">
                <span class="minimap-label"><?= $section['label'] ?></span>
                <span class="minimap-point"></span>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- ========== HERO SECTION ========== -->
    <section id="hero" class="home-section hero-section">

        <?php
        // Hero Background Image (low opacity)
        $heroBg = $page->hero_background_image()->toFile();
        if ($heroBg):
        ?>
        <div class="hero-bg-image" aria-hidden="true">
            <img src="<?= $heroBg->url() ?>" alt="" loading="eager">
        </div>
        <?php endif; ?>

        <!-- Hero Kinetic Type Wall -->
        <?php
        $kineticWords = $page->hero_kinetic_words()->toStructure();
        // Predefined position/animation configs — cycled through
        $kineticConfigs = [
            ['x'=>3,'y'=>28,'dx'=>40,'dy'=>20,'dr'=>0.5,'dur'=>40,'spd'=>0.02],
            ['x'=>52,'y'=>8,'dx'=>-30,'dy'=>15,'dr'=>-1,'dur'=>32,'spd'=>0.03],
            ['x'=>8,'y'=>72,'dx'=>35,'dy'=>-20,'dr'=>0.8,'dur'=>35,'spd'=>0.04],
            ['x'=>72,'y'=>42,'dx'=>-25,'dy'=>-15,'dr'=>1.5,'dur'=>28,'spd'=>0.05],
            ['x'=>35,'y'=>5,'dx'=>20,'dy'=>12,'dr'=>-0.8,'dur'=>30,'spd'=>0.04],
            ['x'=>48,'y'=>78,'dx'=>-40,'dy'=>10,'dr'=>-1.2,'dur'=>38,'spd'=>0.03],
            ['x'=>0,'y'=>55,'dx'=>30,'dy'=>-25,'dr'=>1,'dur'=>26,'spd'=>0.05],
            ['x'=>72,'y'=>85,'dx'=>-20,'dy'=>-15,'dr'=>2,'dur'=>24,'spd'=>0.06],
            ['x'=>78,'y'=>20,'dx'=>15,'dy'=>-10,'dr'=>-1.5,'dur'=>33,'spd'=>0.05],
            ['x'=>62,'y'=>68,'dx'=>25,'dy'=>-15,'dr'=>0.5,'dur'=>36,'spd'=>0.04],
            ['x'=>85,'y'=>52,'dx'=>-30,'dy'=>20,'dr'=>1.8,'dur'=>22,'spd'=>0.06],
            ['x'=>20,'y'=>90,'dx'=>20,'dy'=>-10,'dr'=>-0.8,'dur'=>29,'spd'=>0.05],
            ['x'=>90,'y'=>35,'dx'=>-20,'dy'=>-30,'dr'=>1.2,'dur'=>31,'spd'=>0.04],
            ['x'=>15,'y'=>15,'dx'=>25,'dy'=>18,'dr'=>-0.6,'dur'=>34,'spd'=>0.03],
            ['x'=>58,'y'=>55,'dx'=>-35,'dy'=>25,'dr'=>1,'dur'=>27,'spd'=>0.05],
            ['x'=>40,'y'=>42,'dx'=>30,'dy'=>-20,'dr'=>-1.5,'dur'=>37,'spd'=>0.04],
        ];

        if ($kineticWords->isNotEmpty()):
        ?>
        <div class="hero-kinetic" aria-hidden="true">
            <?php foreach ($kineticWords as $i => $kw):
                $c = $kineticConfigs[$i % count($kineticConfigs)];
                $size = $kw->size()->value() ?: 'medium';
                $variant = $kw->variant()->value() ?: 'normal';
                $variantClass = $variant !== 'normal' ? ' kinetic-word--' . $variant : '';
            ?>
            <span class="kinetic-word kinetic-word--<?= $size ?><?= $variantClass ?>"
                  data-speed="<?= $c['spd'] ?>"
                  style="--x:<?= $c['x'] ?>%;--y:<?= $c['y'] ?>%;--dx:<?= $c['dx'] ?>px;--dy:<?= $c['dy'] ?>px;--dr:<?= $c['dr'] ?>deg;--duration:<?= $c['dur'] ?>s;"><?= htmlspecialchars(strtoupper($kw->word()->value())) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Hero Typography -->
        <div class="hero-text">
            <?php
            $words = $page->hero_words()->toStructure();
            if ($words->isEmpty()) {
                // Fallback default
                $words = [
                    ['word' => 'SO SIEHT', 'alignment' => 'left', 'color' => 'black'],
                    ['word' => 'GEMEINSCHAFT', 'alignment' => 'center', 'color' => 'brand'],
                    ['word' => 'AUS.', 'alignment' => 'right', 'color' => 'black'],
                ];
            }
            foreach ($words as $i => $line):
                $word = is_array($line) ? $line['word'] : $line->word()->value();
                $align = is_array($line) ? $line['alignment'] : $line->alignment()->value();
                $color = is_array($line) ? $line['color'] : $line->color()->value();
            ?>
            <div class="hero-word hero-word--<?= $align ?> hero-word--<?= $color ?>"
                 data-word="<?= $i ?>"
                 style="--delay: <?= $i * 0.15 ?>s">
                <span class="hero-word-inner"><?= htmlspecialchars($word) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($page->hero_subtext()->isNotEmpty()): ?>
        <div class="hero-subtext"><?= $page->hero_subtext()->kt() ?></div>
        <?php endif; ?>

        <!-- Scroll Hint -->
        <div class="scroll-hint">
            <div class="scroll-hint-pill">
                <span class="scroll-hint-text">UNSERE PROJEKTE</span>
                <svg class="scroll-hint-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12l7 7 7-7"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- ========== PROJECT SLIDES ========== -->
    <?php foreach ($page->projects()->toStructure() as $project):
        $slideId = 'project-' . Str::slug($project->title());
        $style = $project->style()->value() ?: 'hero-left';
        $colorScheme = $project->color_scheme()->value() ?: 'dark';
        $bgImage = $project->background_image()->toFile();
        $stats = $project->stats()->toStructure();
    ?>
    <section id="<?= $slideId ?>"
             class="home-section project-section project-section--<?= $style ?> project-section--<?= $colorScheme ?>"
             <?php if ($bgImage): ?>style="--bg-image: url('<?= $bgImage->url() ?>')"<?php endif; ?>>

        <?php if ($bgImage): ?>
        <div class="project-bg" aria-hidden="true">
            <img src="<?= $bgImage->url() ?>" alt="" loading="lazy">
        </div>
        <?php endif; ?>

        <div class="project-content">

            <?php if ($style === 'gallery-scroll'): ?>
            <!-- Gallery Style -->
            <div class="project-gallery-wrapper">
                <div class="project-text-area">
                    <h2 class="project-title reveal-text"><?= $project->title()->html() ?></h2>
                    <?php if ($project->tagline()->isNotEmpty()): ?>
                    <p class="project-tagline reveal-up"><?= $project->tagline()->html() ?></p>
                    <?php endif; ?>
                </div>
                <div class="project-gallery">
                    <?php foreach ($project->gallery_images()->toFiles() as $gImg): ?>
                    <div class="project-gallery-item">
                        <img src="<?= $gImg->url() ?>" alt="<?= $gImg->alt() ?>" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php elseif ($style === 'hero-center'): ?>
            <!-- Center Hero Style -->
            <div class="project-center">
                <h2 class="project-title project-title--center reveal-text"><?= $project->title()->html() ?></h2>
                <?php if ($project->tagline()->isNotEmpty()): ?>
                <p class="project-tagline project-tagline--center reveal-up"><?= $project->tagline()->html() ?></p>
                <?php endif; ?>
                <?php if ($stats->isNotEmpty()): ?>
                <div class="project-stats project-stats--center">
                    <?php foreach ($stats as $stat): ?>
                    <div class="stat-item reveal-up">
                        <span class="stat-value" data-target="<?= $stat->value() ?>"><?= $stat->value() ?></span>
                        <span class="stat-label"><?= $stat->label()->html() ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($style === 'minimal'): ?>
            <!-- Minimal Style -->
            <div class="project-minimal">
                <h2 class="project-title reveal-text"><?= $project->title()->html() ?></h2>
                <?php if ($project->tagline()->isNotEmpty()): ?>
                <p class="project-tagline reveal-up"><?= $project->tagline()->html() ?></p>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <!-- Hero Left Style (Default) -->
            <div class="project-hero-left">
                <div class="project-text-area">
                    <h2 class="project-title reveal-text"><?= $project->title()->html() ?></h2>
                    <?php if ($project->tagline()->isNotEmpty()): ?>
                    <p class="project-tagline reveal-up"><?= $project->tagline()->html() ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($stats->isNotEmpty()): ?>
                <div class="project-stats">
                    <?php foreach ($stats as $stat): ?>
                    <div class="stat-item reveal-left">
                        <span class="stat-value" data-target="<?= $stat->value() ?>"><?= $stat->value() ?></span>
                        <span class="stat-label"><?= $stat->label()->html() ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($project->cta_text()->isNotEmpty()): ?>
            <a href="<?= $project->cta_link()->toUrl() ?? '#' ?>" class="project-cta reveal-up">
                <span><?= $project->cta_text()->html() ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
            <?php endif; ?>

        </div>
    </section>
    <?php endforeach; ?>

    <!-- ========== STATS SECTION ========== -->
    <section id="stats" class="home-section stats-section">
        <?php if ($page->stats_section_title()->isNotEmpty()): ?>
        <h2 class="stats-title reveal-text"><?= $page->stats_section_title()->html() ?></h2>
        <?php endif; ?>

        <div class="stats-grid">
            <?php foreach ($page->stats()->toStructure() as $i => $stat): ?>
            <div class="stats-item reveal-up" style="--delay: <?= $i * 0.1 ?>s">
                <span class="stats-value counter" data-target="<?= preg_replace('/[^0-9]/', '', $stat->value()) ?>"><?= $stat->value() ?></span>
                <span class="stats-label"><?= $stat->label()->html() ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ========== VOLUNTEER CTA ========== -->
    <section id="volunteer" class="home-section volunteer-section">
        <div class="volunteer-content">
            <h2 class="volunteer-title reveal-text">
                <?= $page->volunteer_title()->or('WIR BRAUCHEN DICH.')->html() ?>
            </h2>
            <?php if ($page->volunteer_subtext()->isNotEmpty()): ?>
            <p class="volunteer-subtext reveal-up"><?= $page->volunteer_subtext()->kt() ?></p>
            <?php endif; ?>

            <?php if ($page->volunteer_cta_text()->isNotEmpty()): ?>
            <a href="<?= $page->volunteer_cta_link()->toUrl() ?? '#' ?>" class="volunteer-cta reveal-up">
                <span><?= $page->volunteer_cta_text()->html() ?></span>
            </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- ========== LOCATION SECTION ========== -->
    <section id="location" class="home-section location-section">
        <div class="location-content">
            <h2 class="location-title reveal-text">
                <?= $page->location_title()->or('FINDE UNS')->html() ?>
            </h2>

            <div class="location-grid">
                <div class="location-info">
                    <?php if ($page->location_address()->isNotEmpty()): ?>
                    <address class="location-address reveal-up">
                        <?= $page->location_address()->html() ?>
                    </address>
                    <?php endif; ?>

                    <?php if ($page->location_hours()->isNotEmpty()): ?>
                    <div class="location-hours reveal-up">
                        <?= $page->location_hours()->kt() ?>
                    </div>
                    <?php endif; ?>

                    <div class="location-contact reveal-up">
                        <?php if ($page->location_contact_email()->isNotEmpty()): ?>
                        <a href="mailto:<?= $page->location_contact_email() ?>" class="contact-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <?= $page->location_contact_email() ?>
                        </a>
                        <?php endif; ?>

                        <?php if ($page->location_contact_phone()->isNotEmpty()): ?>
                        <a href="tel:<?= $page->location_contact_phone() ?>" class="contact-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <?= $page->location_contact_phone() ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($page->location_map_embed()->isNotEmpty()): ?>
                <div class="location-map reveal-up">
                    <?= $page->location_map_embed() ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="location-footer reveal-up">
                <a href="/colophon" class="location-footer-link">IMPRESSUM</a>
                <span class="location-footer-sep" aria-hidden="true">/</span>
                <a href="/privacy" class="location-footer-link">DATENSCHUTZ</a>
                <span class="location-footer-sep" aria-hidden="true">/</span>
                <span class="location-footer-copy">&copy; <?= date('Y') ?> BÜRGERSTIFTUNG KARLSRUHE</span>
            </div>
        </div>
    </section>

</main>

<!-- Custom cursor container -->
<div id="custom-cursor" class="custom-cursor" aria-hidden="true">
    <div class="cursor-dot"></div>
    <div class="cursor-text">
        <?php echo file_get_contents($kirby->root('assets') . '/svg/circletext.svg'); ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ========== INTERSECTION OBSERVER FOR REVEALS ==========
    const revealElements = document.querySelectorAll('.reveal-text, .reveal-up, .reveal-left');

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Don't unobserve - allow re-triggering on scroll back
            } else {
                entry.target.classList.remove('revealed');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '-50px'
    });

    revealElements.forEach(el => revealObserver.observe(el));

    // ========== FIT HERO WORDS TO CONTAINER WIDTH ==========
    function fitHeroWords() {
        const heroText = document.querySelector('.hero-text');
        if (!heroText) return;
        const available = heroText.clientWidth - parseFloat(getComputedStyle(heroText).paddingLeft) * 2;

        document.querySelectorAll('.hero-word').forEach(word => {
            const inner = word.querySelector('.hero-word-inner');
            if (!inner) return;

            // Reset to max size first
            word.style.fontSize = '';
            let size = parseFloat(getComputedStyle(word).fontSize);
            const min = 16;

            // Binary search for the largest size that fits
            let lo = min, hi = size;
            while (hi - lo > 1) {
                const mid = Math.floor((lo + hi) / 2);
                word.style.fontSize = mid + 'px';
                if (inner.scrollWidth > available) {
                    hi = mid;
                } else {
                    lo = mid;
                }
            }
            word.style.fontSize = lo + 'px';
        });
    }

    fitHeroWords();
    let fitTimer;
    window.addEventListener('resize', () => {
        clearTimeout(fitTimer);
        fitTimer = setTimeout(fitHeroWords, 150);
    });

    // ========== HERO TEXT ANIMATION ==========
    const heroWords = document.querySelectorAll('.hero-word');
    heroWords.forEach((word, i) => {
        setTimeout(() => {
            word.classList.add('revealed');
        }, 300 + (i * 150));
    });

    // ========== HERO SUBTEXT POSITIONING ==========
    function positionHeroSubtext() {
        const subtext = document.querySelector('.hero-subtext');
        const section = document.querySelector('.hero-section');
        if (!subtext || !section) return;

        const sr = section.getBoundingClientRect();
        const wordInners = document.querySelectorAll('.hero-word-inner');
        const wordBoxes = Array.from(wordInners).map(w => {
            const r = w.getBoundingClientRect();
            return {
                left: r.left - sr.left,
                right: r.right - sr.left,
                top: r.top - sr.top,
                bottom: r.bottom - sr.top,
            };
        });

        // On small screens, let subtext span wider and use less padding
        const isSmall = sr.width < 600;
        const sw = isSmall ? Math.min(sr.width - 32, 400) : Math.min(400, sr.width * 0.35);
        const sh = 80;
        const pad = isSmall ? 16 : Math.max(60, sr.width * 0.06);

        const candidates = [
            { left: pad, top: sr.height * 0.7, align: 'left' },
            { left: sr.width - sw - pad, top: sr.height * 0.18, align: 'right' },
            { left: sr.width - sw - pad, top: sr.height * 0.7, align: 'right' },
            { left: pad, top: sr.height * 0.18, align: 'left' },
            { left: pad, top: sr.height * 0.45, align: 'left' },
            { left: sr.width - sw - pad, top: sr.height * 0.45, align: 'right' },
        ];

        let best = candidates[0], bestScore = Infinity;
        candidates.forEach(c => {
            let score = 0;
            const cb = { left: c.left, right: c.left + sw, top: c.top, bottom: c.top + sh };
            wordBoxes.forEach(wb => {
                const ox = Math.max(0, Math.min(cb.right, wb.right) - Math.max(cb.left, wb.left));
                const oy = Math.max(0, Math.min(cb.bottom, wb.bottom) - Math.max(cb.top, wb.top));
                score += ox * oy;
                const dx = Math.max(0, wb.left - cb.right, cb.left - wb.right);
                const dy = Math.max(0, wb.top - cb.bottom, cb.top - wb.bottom);
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 80) score += (80 - dist) * 10;
            });
            if (score < bestScore) { bestScore = score; best = c; }
        });

        subtext.style.left = best.left + 'px';
        subtext.style.top = best.top + 'px';
        subtext.style.bottom = 'auto';
        subtext.style.textAlign = best.align;
        subtext.style.maxWidth = sw + 'px';
    }

    positionHeroSubtext();
    // Reveal after hero words finish animating
    setTimeout(() => {
        const subtext = document.querySelector('.hero-subtext');
        if (subtext) subtext.classList.add('visible');
    }, 300 + heroWords.length * 150 + 300);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(positionHeroSubtext, 200);
    });

    // ========== COUNTER ANIMATION ==========
    const counters = document.querySelectorAll('.counter');

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                entry.target.classList.add('counted');
                const target = entry.target.dataset.target;
                const numericTarget = parseInt(target.replace(/[^0-9]/g, ''));
                const suffix = target.replace(/[0-9]/g, '');

                anime({
                    targets: entry.target,
                    innerHTML: [0, numericTarget],
                    round: 1,
                    easing: 'easeOutExpo',
                    duration: 2000,
                    update: function(anim) {
                        entry.target.innerHTML = Math.round(anim.animations[0].currentValue) + suffix;
                    }
                });
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => counterObserver.observe(c));

    // ========== MINIMAP ACTIVE STATE ==========
    const sections = document.querySelectorAll('.home-section');
    const minimapDots = document.querySelectorAll('.minimap-dot');

    const minimapObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const sectionId = entry.target.id;
                minimapDots.forEach(dot => {
                    dot.classList.toggle('active', dot.dataset.section === sectionId);
                });
            }
        });
    }, {
        threshold: 0.5,
        rootMargin: '-10% 0px -10% 0px'
    });

    sections.forEach(s => minimapObserver.observe(s));

    // Minimap click handling
    const scrollContainer = document.querySelector('.home-scroll-container');
    minimapDots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = dot.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                dot.blur();
                if (scrollContainer) scrollContainer.focus({ preventScroll: true });
            }
        });
    });

    // ========== KINETIC TYPE PARALLAX ==========
    const heroSection = document.querySelector('.hero-section');
    const kineticWords = document.querySelectorAll('.kinetic-word');

    if (heroSection && kineticWords.length) {
        heroSection.addEventListener('mousemove', (e) => {
            const rect = heroSection.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            kineticWords.forEach(word => {
                const speed = parseFloat(word.dataset.speed) || 0.03;
                const px = x * speed * 1000;
                const py = y * speed * 1000;
                word.style.setProperty('--px', `${px}px`);
                word.style.setProperty('--py', `${py}px`);
            });
        });

        heroSection.addEventListener('mouseleave', () => {
            kineticWords.forEach(word => {
                word.style.setProperty('--px', '0px');
                word.style.setProperty('--py', '0px');
            });
        });
    }

    // ========== CUSTOM CURSOR ==========
    const cursor = document.getElementById('custom-cursor');
    const cursorDot = cursor.querySelector('.cursor-dot');
    const cursorRing = cursor.querySelector('.cursor-ring');

    let mouseX = 0, mouseY = 0;
    let cursorX = 0, cursorY = 0;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    // Detect background under cursor for color adaptation
    let currentCursorTheme = '';
    function updateCursorTheme() {
        const el = document.elementFromPoint(mouseX, mouseY);
        if (!el) return;
        const section = el.closest('.home-section, footer, header');
        let theme = 'dark'; // default for dark backgrounds → orange cursor
        if (section) {
            if (section.classList.contains('project-section--brand') ||
                section.classList.contains('volunteer-section')) {
                theme = 'brand'; // orange bg → white cursor
            } else if (section.classList.contains('project-section--light') ||
                       section.classList.contains('location-section') ||
                       section.tagName === 'HEADER') {
                theme = 'light'; // white bg → dark cursor
            }
        }
        if (theme !== currentCursorTheme) {
            currentCursorTheme = theme;
            cursor.dataset.theme = theme;
        }
    }

    function animateCursor() {
        cursorX += (mouseX - cursorX) * 0.15;
        cursorY += (mouseY - cursorY) * 0.15;

        cursor.style.transform = `translate(${cursorX}px, ${cursorY}px)`;
        updateCursorTheme();
        requestAnimationFrame(animateCursor);
    }
    animateCursor();

    // Cursor states
    const interactiveElements = document.querySelectorAll('a, button, .hero-grid-item');
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => cursor.classList.add('cursor-hover'));
        el.addEventListener('mouseleave', () => cursor.classList.remove('cursor-hover'));
    });

    // ========== SMOOTH SCROLL SNAP ==========
    // CSS handles the snap, but we ensure smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        // Skip minimap dots — they have their own handler
        if (anchor.classList.contains('minimap-dot')) return;
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                this.blur();
                if (scrollContainer) scrollContainer.focus({ preventScroll: true });
            }
        });
    });

});
</script>

</body>
</html>
