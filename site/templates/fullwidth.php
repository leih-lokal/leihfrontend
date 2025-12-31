<?php snippet("header"); ?>

<main class="container mx-auto">
  <!-- Banner Section (optional) -->
  <?php if (
    $page->show_banner()->toBool() &&
    ($banner = $page->banner_image()->toFile())
  ): ?>
  <section class="relative h-48 md:h-64 overflow-hidden mb-8">
    <img src="<?= $banner->url() ?>"
         alt="<?= $page->title()->esc() ?>"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent flex items-end">
      <div class="w-full px-6 md:px-8 pb-6 text-white">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4"><?= $page
          ->title()
          ->esc() ?></h1>
        <?php if ($page->subtitle()->isNotEmpty()): ?>
          <p class="text-lg md:text-xl text-white/90 mt-2"><?= $page
            ->subtitle()
            ->esc() ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php else: ?>
  <!-- Simple Header (no banner) -->
  <section class="px-4 md:px-8 py-8 md:py-12 border-b border-leihlokal-500">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4"><?= $page
      ->title()
      ->esc() ?></h1>
    <?php if ($page->subtitle()->isNotEmpty()): ?>
      <p class="text-lg md:text-xl text-gray-600"><?= $page
        ->subtitle()
        ->esc() ?></p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <!-- Main Content Area -->
  <div class="px-4 md:px-8 py-8 md:py-12">

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto">

      <!-- Introduction -->
      <?php if ($page->intro()->isNotEmpty()): ?>
      <div class="text-lg md:text-xl text-gray-700 mb-8 pb-8 border-b border-gray-200 leading-relaxed">
        <?= $page->intro()->kt() ?>
      </div>
      <?php endif; ?>

      <!-- Content Sections -->
      <?php if ($page->content_sections()->isNotEmpty()): ?>
        <?php foreach (
          $page->content_sections()->toStructure()
          as $index => $section
        ):

          $sectionImage = $section->image()->toFile();
          $imagePos = $section->image_position()->value();
          ?>
        <section class="mb-12 <?= $index > 0
          ? "pt-8 border-t border-gray-200"
          : "" ?>">

          <?php if ($section->heading()->isNotEmpty()): ?>
          <h2 class="text-2xl md:text-3xl font-bold mb-6"><?= $section
            ->heading()
            ->esc() ?></h2>
          <?php endif; ?>

          <!-- Full Width Image -->
          <?php if ($sectionImage && $imagePos === "full"): ?>
          <div class="mb-6 overflow-hidden">
            <img src="<?= $sectionImage->url() ?>"
                 alt="<?= $section->heading()->esc() ?>"
                 class="w-full h-auto">
          </div>
          <?php endif; ?>

          <!-- Text with Side Image -->
          <?php if (
            $sectionImage &&
            ($imagePos === "left" || $imagePos === "right")
          ): ?>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="<?= $imagePos === "left"
              ? "md:order-1"
              : "md:order-2" ?>">
              <img src="<?= $sectionImage->url() ?>"
                   alt="<?= $section->heading()->esc() ?>"
                   class="w-full h-auto shadow-md">
            </div>
            <div class="md:col-span-2 <?= $imagePos === "left"
              ? "md:order-2"
              : "md:order-1" ?>">
              <div class="prose prose-lg max-w-none">
                <?= $section->text()->kt() ?>
              </div>
            </div>
          </div>
          <?php else: ?>
          <!-- Text Only -->
          <div class="prose prose-lg max-w-none mb-6">
            <?= $section->text()->kt() ?>
          </div>
          <?php endif; ?>

          <!-- Callout Box -->
          <?php if ($section->callout()->isNotEmpty()): ?>
          <div class="bg-leihlokal-50 border-l-4 border-leihlokal-500 p-4 md:p-6 my-6 rounded-r-lg">
            <div class="prose prose-sm">
              <?= $section->callout()->kt() ?>
            </div>
          </div>
          <?php endif; ?>

        </section>
        <?php
        endforeach; ?>
      <?php endif; ?>

      <!-- Additional Content -->
      <?php if ($page->additional_content()->isNotEmpty()): ?>
      <section class="mt-12 pt-8 border-t border-gray-200">
        <div class="prose prose-lg max-w-none">
          <?= $page->additional_content() ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- Subpages Section -->
      <?php if ($page->hasChildren()): ?>
      <section class="mt-12 pt-8 border-t border-gray-200">
        <h2 class="text-2xl md:text-3xl font-bold mb-6">Weitere Seiten</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php foreach ($page->children()->listed() as $subpage):
            $cover =
              $subpage->cover()->toFile() ??
              ($subpage->banner_image()->toFile() ??
                $subpage->hero_image()->toFile()); ?>
          <a href="<?= $subpage->url() ?>" class="group border border-gray-200 rounded-lg overflow-hidden hover:border-leihlokal-500 hover:shadow-md transition-all">
            <?php if ($cover): ?>
            <div class="aspect-video overflow-hidden">
              <img src="<?= $cover->url() ?>"
                   alt="<?= $subpage->title()->esc() ?>"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            <?php endif; ?>
            <div class="p-4 md:p-6">
              <h3 class="font-bold text-lg mb-2 group-hover:text-leihlokal-500 transition-colors"><?= $subpage
                ->title()
                ->esc() ?></h3>
              <?php if ($subpage->intro()->isNotEmpty()): ?>
                <p class="text-gray-600 text-sm line-clamp-2"><?= $subpage
                  ->intro()
                  ->excerpt(120) ?></p>
              <?php elseif ($subpage->subtitle()->isNotEmpty()): ?>
                <p class="text-gray-600 text-sm line-clamp-2"><?= $subpage
                  ->subtitle()
                  ->excerpt(120) ?></p>
              <?php endif; ?>
              <div class="mt-3 text-leihlokal-500 text-sm font-medium group-hover:underline">
                Mehr lesen →
              </div>
            </div>
          </a>
          <?php
          endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>

  </div>
</main>

<?php snippet("footer"); ?>
