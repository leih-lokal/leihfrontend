<!DOCTYPE html>
<html class="relative min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->title() ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/pages/' . $page->intendedTemplate() . '.css') ?>">
    <script src="<?= url('assets/js/main.js') ?>" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox-plus-jquery.min.js" defer></script>
</head>
<body class="mb-[100px] font-sans leading-relaxed text-gray-700">
<header class="border-b border-leihlokal-500">
    <div class="flex justify-between items-stretch">
        <div class="flex-none p-4 md:border-r border-leihlokal-500">
            <a href="<?= $site->url() ?>" class="no-underline text-gray-700 font-bold">
                <?= $site->title() ?>
            </a>
        </div>

        <!-- Mobile menu button -->
        <button id="mobile-menu-button" class="md:hidden p-4 border-l border-leihlokal-500 focus:outline-none focus:bg-gray-100" aria-label="Toggle menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Desktop navigation -->
        <nav class="hidden md:flex flex-1">
            <ul class="flex list-none m-0 p-0 w-full">
                <?php foreach($site->children()->listed() as $item): ?>
                <li class="flex-1 border-r border-leihlokal-500 last:border-r-0 relative nav-item">
                    <a href="<?= $item->url() ?>"
                       class="flex items-center justify-center p-4 no-underline text-gray-700 hover:bg-gray-100 transition-colors duration-300 w-full h-full">
                        <?php if ($item->title()->value() == 'Leih.Lokal'): ?>
                            <img src="<?= url('assets/svg/leihlokal.svg') ?>" alt="Leih.Lokal" class="h-6">
                        <?php elseif ($item->title()->value() == 'Frei_Räume'): ?>
                            <img src="<?= url('assets/svg/frei-raume.svg') ?>" alt="Frei_Räume" class="h-6">
                        <?php else: ?>
                            <?= $item->title() ?>
                        <?php endif ?>
                        <?php if ($item->hasChildren()): ?>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <?php endif ?>
                    </a>

                    <?php if ($item->hasChildren()): ?>
                    <div class="nav-dropdown absolute top-full left-0 right-0 bg-white border-x border-b border-leihlokal-500 opacity-0 invisible transition-all duration-200 z-50">
                        <ul class="list-none m-0 p-0">
                            <?php foreach ($item->children()->listed() as $subitem): ?>
                            <li class="border-b border-gray-200 last:border-b-0">
                                <a href="<?= $subitem->url() ?>"
                                   class="block p-3 no-underline text-gray-700 hover:bg-gray-100 transition-colors duration-200 text-sm">
                                    <?= $subitem->title() ?>
                                </a>
                            </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                    <?php endif ?>
                </li>
                <?php endforeach ?>
            </ul>
        </nav>
    </div>

    <!-- Mobile navigation -->
    <nav id="mobile-menu" class="hidden md:hidden border-t border-leihlokal-500">
        <ul class="list-none m-0 p-0">
            <?php foreach($site->children()->listed() as $item): ?>
            <li class="border-b border-leihlokal-500 last:border-b-0">
                <?php if ($item->hasChildren()): ?>
                    <div class="mobile-nav-item">
                        <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-100 transition-colors duration-300"
                             onclick="this.parentElement.classList.toggle('open')">
                            <a href="<?= $item->url() ?>"
                               class="flex-1 no-underline text-gray-700"
                               onclick="event.stopPropagation()">
                                <?php if ($item->title()->value() == 'Leih.Lokal'): ?>
                                    <img src="<?= url('assets/svg/leihlokal.svg') ?>" alt="Leih.Lokal" class="h-6">
                                <?php elseif ($item->title()->value() == 'Frei_Räume'): ?>
                                    <img src="<?= url('assets/svg/frei-raume.svg') ?>" alt="Frei_Räume" class="h-6">
                                <?php else: ?>
                                    <?= $item->title() ?>
                                <?php endif ?>
                            </a>
                            <svg class="w-5 h-5 text-gray-700 transition-transform mobile-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <ul class="hidden mobile-submenu list-none m-0 p-0 bg-gray-50">
                            <?php foreach ($item->children()->listed() as $subitem): ?>
                            <li class="border-t border-gray-200">
                                <a href="<?= $subitem->url() ?>"
                                   class="block pl-6 pr-4 py-3 no-underline text-gray-700 hover:bg-gray-100 transition-colors duration-200 text-sm">
                                    <?= $subitem->title() ?>
                                </a>
                            </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $item->url() ?>"
                       class="block p-4 no-underline text-gray-700 hover:bg-gray-100 transition-colors duration-300">
                        <?php if ($item->title()->value() == 'Leih.Lokal'): ?>
                            <img src="<?= url('assets/svg/leihlokal.svg') ?>" alt="Leih.Lokal" class="h-6">
                        <?php elseif ($item->title()->value() == 'Frei_Räume'): ?>
                            <img src="<?= url('assets/svg/frei-raume.svg') ?>" alt="Frei_Räume" class="h-6">
                        <?php else: ?>
                            <?= $item->title() ?>
                        <?php endif ?>
                    </a>
                <?php endif ?>
            </li>
            <?php endforeach ?>
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

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            const isHidden = mobileMenu.classList.contains('hidden');

            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        });
    }
});
</script>