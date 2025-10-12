<?php snippet("header"); ?>

<main>
  <!-- Hero Section -->
  <section class="relative min-h-[60vh] md:min-h-[70vh] flex items-center justify-center overflow-hidden">
    <?php if ($heroImage = $page->hero_image()->toFile()): ?>
    <div class="absolute inset-0">
      <img src="<?= $heroImage->url() ?>"
           alt="<?= $page->hero_title()->esc() ?>"
           class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/40 to-black/60"></div>
    </div>
    <?php else: ?>
    <div class="absolute inset-0 bg-gradient-to-br from-leihlokal-600 to-leihlokal-800"></div>
    <?php endif ?>

    <div class="relative container mx-auto px-4 md:px-8 text-center text-white z-10">
      <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-4 md:mb-6">
        <?= $page->hero_title()->or($page->title())->esc() ?>
      </h1>
      <?php if ($page->hero_subtitle()->isNotEmpty()): ?>
      <p class="text-lg md:text-2xl mb-6 md:mb-8 max-w-3xl mx-auto text-white/90">
        <?= $page->hero_subtitle()->esc() ?>
      </p>
      <?php endif ?>
      <?php if ($page->hero_cta_text()->isNotEmpty() && $page->hero_cta_link()->isNotEmpty()): ?>
      <a href="<?= $page->hero_cta_link()->esc() ?>"
         class="inline-block bg-white text-leihlokal-600 hover:bg-leihlokal-50 px-6 md:px-8 py-3 md:py-4 rounded-lg font-bold text-lg transition-colors">
        <?= $page->hero_cta_text()->esc() ?>
      </a>
      <?php endif ?>
    </div>
  </section>

  <!-- Intro Section -->
  <?php if ($page->intro()->isNotEmpty()): ?>
  <section class="container mx-auto px-4 md:px-8 py-12 md:py-16">
    <div class="max-w-3xl mx-auto text-center">
      <div class="prose prose-lg md:prose-xl mx-auto">
        <?= $page->intro()->kt() ?>
      </div>
    </div>
  </section>
  <?php endif ?>

  <!-- Stats Section -->
  <?php if ($page->stats()->isNotEmpty()): ?>
  <section class="bg-gray-50 border-y border-gray-200 py-12 md:py-16">
    <div class="container mx-auto px-4 md:px-8">
      <?php if ($page->stats_headline()->isNotEmpty()): ?>
      <h2 class="text-3xl md:text-4xl font-bold text-center mb-8 md:mb-12">
        <?= $page->stats_headline()->esc() ?>
      </h2>
      <?php endif ?>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
        <?php foreach ($page->stats()->toStructure() as $stat): ?>
        <div class="text-center">
          <?php if ($stat->icon()->isNotEmpty()): ?>
          <div class="text-4xl md:text-5xl mb-3"><?= $stat->icon()->esc() ?></div>
          <?php endif ?>
          <div class="text-3xl md:text-5xl font-bold text-leihlokal-500 mb-2">
            <?= $stat->number()->esc() ?>
          </div>
          <div class="text-sm md:text-base text-gray-600 font-medium">
            <?= $stat->label()->esc() ?>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
  </section>
  <?php endif ?>

  <!-- Feature Sections -->
  <?php if ($page->features()->isNotEmpty()): ?>
  <div class="container mx-auto">
    <?php foreach ($page->features()->toStructure() as $index => $feature):
      $isLeft = $feature->layout()->value() === 'left';
      $image = $feature->image()->toFile();
    ?>
    <section class="py-12 md:py-16 px-4 md:px-8 <?= $index > 0 ? 'border-t border-gray-200' : '' ?>">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center <?= $isLeft ? 'md:flex-row' : 'md:flex-row-reverse' ?>">

        <!-- Image -->
        <div class="<?= $isLeft ? 'md:order-1' : 'md:order-2' ?>">
          <?php if ($image): ?>
          <div class="overflow-hidden shadow-lg">
            <img src="<?= $image->url() ?>"
                 alt="<?= $feature->heading()->esc() ?>"
                 class="w-full h-auto">
          </div>
          <?php else: ?>
          <div class="aspect-video bg-gray-200 flex items-center justify-center">
            <span class="text-gray-400 text-lg">Bild fehlt</span>
          </div>
          <?php endif ?>
        </div>

        <!-- Text -->
        <div class="<?= $isLeft ? 'md:order-2' : 'md:order-1' ?>">
          <h2 class="text-3xl md:text-4xl font-bold mb-4 md:mb-6">
            <?= $feature->heading()->esc() ?>
          </h2>
          <div class="prose prose-lg mb-6">
            <?= $feature->text()->kt() ?>
          </div>
          <?php if ($feature->cta_text()->isNotEmpty() && $feature->cta_link()->isNotEmpty()): ?>
          <a href="<?= $feature->cta_link()->esc() ?>"
             class="inline-block bg-leihlokal-500 hover:bg-leihlokal-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            <?= $feature->cta_text()->esc() ?> →
          </a>
          <?php endif ?>
        </div>

      </div>
    </section>
    <?php endforeach ?>
  </div>
  <?php endif ?>

  <!-- Subpages as Feature Cards -->
  <?php if ($page->hasChildren()): ?>
  <section class="bg-gray-50 py-12 md:py-16 border-t border-gray-200">
    <div class="container mx-auto px-4 md:px-8">
      <h2 class="text-3xl md:text-4xl font-bold text-center mb-8 md:mb-12">
        Mehr entdecken
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <?php foreach ($page->children()->listed() as $subpage):
          $cover = $subpage->cover()->toFile() ?? $subpage->hero_image()->toFile();
        ?>
        <a href="<?= $subpage->url() ?>"
           class="group bg-white overflow-hidden shadow-md hover:shadow-xl transition-shadow">
          <?php if ($cover): ?>
          <div class="aspect-video overflow-hidden">
            <img src="<?= $cover->url() ?>"
                 alt="<?= $subpage->title()->esc() ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          </div>
          <?php endif ?>

          <div class="p-6">
            <h3 class="text-xl font-bold mb-2 group-hover:text-leihlokal-500 transition-colors">
              <?= $subpage->title()->esc() ?>
            </h3>
            <?php if ($subpage->intro()->isNotEmpty()): ?>
            <p class="text-gray-600 line-clamp-3">
              <?= $subpage->intro()->excerpt(150) ?>
            </p>
            <?php elseif ($subpage->hero_subtitle()->isNotEmpty()): ?>
            <p class="text-gray-600 line-clamp-3">
              <?= $subpage->hero_subtitle()->excerpt(150) ?>
            </p>
            <?php endif ?>

            <div class="mt-4 text-leihlokal-500 font-medium group-hover:underline">
              Mehr erfahren →
            </div>
          </div>
        </a>
        <?php endforeach ?>
      </div>
    </div>
  </section>
  <?php endif ?>
</main>

<?php snippet("footer"); ?>
