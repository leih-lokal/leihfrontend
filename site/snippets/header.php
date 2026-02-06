<!DOCTYPE html>
<html lang="de" class="relative min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->title() ?> | BSKA</title>
    <link rel="icon" href="/favicon-48x48.png" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
    <link rel="stylesheet" href="<?= url("assets/css/tailwind.css") ?>?v=<?= filemtime($kirby->root('assets') . '/css/tailwind.css') ?>">
    <?php
    $pageCssFile = 'assets/css/pages/' . $page->intendedTemplate() . '.css';
    $pageCssPath = $kirby->root('index') . '/' . $pageCssFile;
    if (file_exists($pageCssPath)): ?>
    <link rel="stylesheet" href="<?= url($pageCssFile) ?>?v=<?= filemtime($pageCssPath) ?>">
    <?php endif; ?>
    <script src="<?= url("assets/js/main.js") ?>" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" media="print" onload="this.media='all'">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox-plus-jquery.min.js" defer></script>
</head>
<body class="mb-[100px] font-sans leading-relaxed text-gray-700">
<header class="sticky top-0 z-50 bg-white border-b-2 border-leihlokal-500">
    <div class="flex justify-between items-stretch">
        <div class="flex-none p-4 md:border-r-2 border-leihlokal-500">
            <a href="<?= $site->url() ?>" class="no-underline text-gray-900 font-bold uppercase text-sm tracking-wider">
                <?= $site->title() ?>
            </a>
        </div>

        <!-- Mobile menu button -->
        <button id="mobile-menu-button" class="md:hidden flex items-center gap-2 px-4 py-3 border-l-2 border-leihlokal-500 font-bold text-xs uppercase tracking-wider text-gray-900 focus:outline-none focus:bg-leihlokal-50" aria-label="Menü öffnen">
            <span id="menu-label" class="menu-label-text">MENÜ</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Desktop navigation -->
        <nav class="hidden md:flex flex-1">
            <ul class="flex list-none m-0 p-0 w-full">
                <?php foreach ($site->children()->listed() as $item): ?>
                <?php
                // Filter out virtual item pages from dropdown
                $realChildren = $item
                  ->children()
                  ->filterBy("intendedTemplate", "!=", "item");
                $hasRealChildren = $realChildren->isNotEmpty();
                ?>
                <li class="flex-1 border-r-2 border-leihlokal-500 last:border-r-0 relative nav-item">
                    <a href="<?= $item->url() ?>"
                       class="nav-link flex items-center justify-center p-4 no-underline text-gray-900 font-semibold text-sm uppercase tracking-wider hover:bg-leihlokal-50 hover:text-leihlokal-600 transition-colors duration-200 w-full h-full">
                        <?php if ($item->title()->value() == "leih.lokal"): ?>
                            <img src="<?= url(
                              "assets/svg/leihlokal.svg",
                            ) ?>" alt="leih.lokal" class="h-6">
                        <?php elseif (
                          $item->title()->value() == "Frei_Räume"
                        ): ?>
                            <img src="<?= url(
                              "assets/svg/frei-raume.svg",
                            ) ?>" alt="Frei_Räume" class="h-6">
                        <?php else: ?>
                            <?= $item->title() ?>
                        <?php endif; ?>
                        <?php if ($hasRealChildren): ?>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <?php endif; ?>
                    </a>

                    <?php if ($hasRealChildren): ?>
                    <div class="nav-dropdown absolute top-full left-0 right-0 bg-white border-x-2 border-b-2 border-leihlokal-500 opacity-0 invisible transition-all duration-200 z-50">
                        <ul class="list-none m-0 p-0">
                            <?php foreach (
                              $realChildren->listed()
                              as $subitem
                            ): ?>
                            <li class="border-b border-gray-200 last:border-b-0">
                                <a href="<?= $subitem->url() ?>"
                                   class="block p-3 no-underline text-gray-900 font-medium hover:bg-leihlokal-50 hover:text-leihlokal-600 transition-colors duration-200 text-sm">
                                    <?= $subitem->title() ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>

    <!-- Mobile navigation -->
    <nav id="mobile-menu" class="hidden md:hidden border-t-2 border-leihlokal-500 bg-white">
        <ul class="list-none m-0 p-0">
            <?php foreach ($site->children()->listed() as $item): ?>
            <?php
            // Filter out virtual item pages from dropdown (mobile)
            $realChildren = $item
              ->children()
              ->filterBy("intendedTemplate", "!=", "item");
            $hasRealChildren = $realChildren->isNotEmpty();
            ?>
            <li class="border-b-2 border-leihlokal-500 last:border-b-0">
                <?php if ($hasRealChildren): ?>
                    <div class="mobile-nav-item">
                        <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-leihlokal-50 transition-colors duration-200"
                             onclick="this.parentElement.classList.toggle('open')">
                            <a href="<?= $item->url() ?>"
                               class="flex-1 no-underline text-gray-900 font-semibold"
                               onclick="event.stopPropagation()">
                                <?php if (
                                  $item->title()->value() == "leih.lokal"
                                ): ?>
                                    <img src="<?= url(
                                      "assets/svg/leihlokal.svg",
                                    ) ?>" alt="leih.lokal" class="h-6">
                                <?php elseif (
                                  $item->title()->value() == "Frei_Räume"
                                ): ?>
                                    <img src="<?= url(
                                      "assets/svg/frei-raume.svg",
                                    ) ?>" alt="Frei_Räume" class="h-6">
                                <?php else: ?>
                                    <?= $item->title() ?>
                                <?php endif; ?>
                            </a>
                            <svg class="w-5 h-5 text-gray-900 transition-transform mobile-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <ul class="hidden mobile-submenu list-none m-0 p-0 bg-leihlokal-50">
                            <?php foreach (
                              $realChildren->listed()
                              as $subitem
                            ): ?>
                            <li class="border-t border-leihlokal-200">
                                <a href="<?= $subitem->url() ?>"
                                   class="block pl-6 pr-4 py-3 no-underline text-gray-900 font-medium hover:bg-leihlokal-100 transition-colors duration-200 text-sm">
                                    <?= $subitem->title() ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $item->url() ?>"
                       class="block p-4 no-underline text-gray-900 font-semibold hover:bg-leihlokal-50 transition-colors duration-200">
                        <?php if ($item->title()->value() == "leih.lokal"): ?>
                            <img src="<?= url(
                              "assets/svg/leihlokal.svg",
                            ) ?>" alt="leih.lokal" class="h-6">
                        <?php elseif (
                          $item->title()->value() == "Frei_Räume"
                        ): ?>
                            <img src="<?= url(
                              "assets/svg/frei-raume.svg",
                            ) ?>" alt="Frei_Räume" class="h-6">
                        <?php else: ?>
                            <?= $item->title() ?>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>

<style>
/* Desktop dropdown hover */
.nav-item:hover .nav-dropdown {
    opacity: 1;
    visibility: visible;
}

/* Mobile submenu */
.mobile-nav-item.open .mobile-submenu {
    display: block;
}
.mobile-nav-item.open .mobile-chevron {
    transform: rotate(180deg);
}
</style>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');

    const menuLabel = document.getElementById('menu-label');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            const isHidden = mobileMenu.classList.contains('hidden');

            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                if (menuLabel) menuLabel.textContent = 'SCHLIESSEN';
            } else {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                if (menuLabel) menuLabel.textContent = 'MENÜ';
            }
        });
    }
});
</script>
