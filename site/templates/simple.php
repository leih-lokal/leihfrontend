<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page->title()->esc() ?></title>
  <link rel="stylesheet" href="<?= url('assets/css/tailwind.css') ?>">
</head>
<body class="bg-white">

  <main class="container mx-auto px-6 py-12 max-w-3xl">

    <!-- Logo -->
    <div class="flex justify-center mb-8">
      <a href="<?= $site->url() ?>">
        <img src="<?= url('assets/svg/block-wm.svg') ?>"
             alt="leih.lokal"
             class="h-24 w-auto hover:opacity-80 transition-opacity cursor-pointer">
      </a>
    </div>

    <!-- Page Title -->
    <h1 class="text-3xl md:text-4xl font-bold text-center mb-8">
      <?= $page->title()->esc() ?>
    </h1>

    <!-- Content -->
    <div class="prose prose-lg max-w-none mx-auto">
      <?= $page->text()->kt() ?>
    </div>

  </main>

</body>
</html>
