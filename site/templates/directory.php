<?php snippet("header"); ?>

<main class="container mx-auto">
  <!-- Header Section -->
  <section class="px-4 md:px-8 py-8 md:py-12 border-b border-leihlokal-500">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4"><?= $page->title()->esc() ?></h1>
    <?php if ($page->subtitle()->isNotEmpty()): ?>
      <p class="text-lg md:text-xl text-gray-600"><?= $page->subtitle()->esc() ?></p>
    <?php endif ?>
  </section>

  <!-- Main Content Area -->
  <div class="flex flex-col lg:flex-row gap-8 px-4 md:px-8 py-8 md:py-12">

    <!-- Main Content -->
    <div class="flex-1 lg:w-2/3">

      <!-- Introduction -->
      <?php if ($page->intro()->isNotEmpty()): ?>
      <div class="text-lg md:text-xl text-gray-700 mb-8 pb-8 border-b border-gray-200 leading-relaxed">
        <?= $page->intro()->kt() ?>
      </div>
      <?php endif ?>

      <!-- Directory List -->
      <?php if ($page->hasChildren()): ?>
      <section>
        <div class="space-y-0 border-t border-gray-200">
          <?php foreach ($page->children()->listed() as $subpage): ?>
          <a href="<?= $subpage->url() ?>"
             class="group flex items-center justify-between py-4 md:py-6 border-b border-gray-200 hover:bg-gray-50 transition-colors px-4 -mx-4">
            <div class="flex-1">
              <h2 class="text-lg md:text-xl font-bold mb-1 group-hover:text-leihlokal-500 transition-colors">
                <?= $subpage->title()->esc() ?>
              </h2>
              <?php if ($subpage->intro()->isNotEmpty()): ?>
                <p class="text-sm text-gray-600"><?= $subpage->intro()->excerpt(120) ?></p>
              <?php elseif ($subpage->subtitle()->isNotEmpty()): ?>
                <p class="text-sm text-gray-600"><?= $subpage->subtitle()->excerpt(120) ?></p>
              <?php endif ?>
            </div>
            <div class="ml-4 flex-shrink-0 text-leihlokal-500 opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>
          <?php endforeach ?>
        </div>
      </section>
      <?php else: ?>
      <!-- Empty state -->
      <div class="text-center py-12 text-gray-500">
        <p>Keine Unterseiten vorhanden.</p>
      </div>
      <?php endif ?>

    </div>

    <!-- Sidebar (empty placeholder for future use) -->
    <aside class="lg:w-1/3">
      <!-- Sidebar content can be added here in the future -->
    </aside>

  </div>
</main>

<?php snippet("footer"); ?>
