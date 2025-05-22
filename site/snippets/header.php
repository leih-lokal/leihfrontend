<!DOCTYPE html>
<html class="relative min-h-full">
<head>
    <meta charset="UTF-8">
    <title><?= $page->title() ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/pages/<?php echo $page->intendedTemplate() ?>.css">
    <script src="assets/js/main.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox-plus-jquery.min.js" defer></script>
</head>
<body class="mb-[100px] font-sans leading-relaxed text-gray-700">
<header class="border-b border-leihlokal-500">
    <div class="flex justify-between items-stretch">
        <div class="flex-none p-4 border-r border-leihlokal-500">
            <a href="<?= $site->url() ?>" class="no-underline text-gray-700 font-bold">
                <?= $site->title() ?>
            </a>
        </div>
        <nav class="flex-1 flex">
            <ul class="flex list-none m-0 p-0 w-full">
                <?php foreach($site->children()->listed() as $item): ?>
                <li class="flex-1 flex border-r border-leihlokal-500 last:border-r-0">
                    <a href="<?= $item->url() ?>" 
                       class="flex-1 flex items-center justify-center p-4 no-underline text-gray-700 hover:bg-gray-100 transition-colors duration-300">
                        <?= $item->title() ?>
                    </a>
                </li>
                <?php endforeach ?>
            </ul>
        </nav>
    </div>
</header>