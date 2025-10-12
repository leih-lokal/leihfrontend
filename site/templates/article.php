<?php snippet("header"); ?>

<main class="container mx-auto">
  <!-- Hero Section -->
  <?php if ($cover = $page->cover()->toFile()): ?>
  <section class="relative h-64 md:h-96 overflow-hidden">
    <img src="<?= $cover->url() ?>"
         alt="<?= $page->title()->esc() ?>"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
      <div class="w-full px-4 md:px-8 pb-6 md:pb-8 text-white">
        <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold mb-2"><?= $page->title()->esc() ?></h1>
        <?php if ($page->subtitle()->isNotEmpty()): ?>
          <p class="text-lg md:text-xl text-white/90"><?= $page->subtitle()->esc() ?></p>
        <?php endif ?>
      </div>
    </div>
  </section>
  <?php else: ?>
  <section class="px-4 md:px-8 py-8 md:py-12 border-b border-leihlokal-500">
    <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold mb-2"><?= $page->title()->esc() ?></h1>
    <?php if ($page->subtitle()->isNotEmpty()): ?>
      <p class="text-lg md:text-xl text-gray-600"><?= $page->subtitle()->esc() ?></p>
    <?php endif ?>
  </section>
  <?php endif ?>

  <!-- Metadata -->
  <?php if ($page->author()->isNotEmpty() || $page->date()->isNotEmpty()): ?>
  <section class="px-4 md:px-8 py-4 border-b border-gray-200 text-sm text-gray-600">
    <?php if ($page->author()->isNotEmpty()): ?>
      <span>Von <?= $page->author()->esc() ?></span>
    <?php endif ?>
    <?php if ($page->date()->isNotEmpty()): ?>
      <span><?= $page->author()->isNotEmpty() ? ' • ' : '' ?><?= $page->date()->toDate('d.m.Y') ?></span>
    <?php endif ?>
  </section>
  <?php endif ?>

  <!-- Main Content Area -->
  <div class="flex flex-col lg:flex-row gap-8 px-4 md:px-8 py-8 md:py-12">

    <!-- Main Article Content -->
    <article class="flex-1 lg:w-2/3">
      <?php if ($page->intro()->isNotEmpty()): ?>
      <div class="text-xl md:text-2xl text-gray-700 mb-8 pb-8 border-b border-gray-200 leading-relaxed">
        <?= $page->intro()->kt() ?>
      </div>
      <?php endif ?>

      <div class="prose prose-sm md:prose-lg max-w-none prose-headings:font-bold prose-a:text-leihlokal-500 hover:prose-a:text-leihlokal-600 prose-img:rounded-lg">
        <?= $page->text()->kt() ?>
      </div>

      <!-- Image Gallery -->
      <?php if ($page->images()->count() > 0): ?>
      <section class="mt-12 pt-8 border-t border-gray-200">
        <h2 class="text-2xl font-bold mb-6">Bilder</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <?php foreach ($page->images() as $image): ?>
          <a href="<?= $image->url() ?>" data-lightbox="article-gallery" data-title="<?= $image->alt()->esc() ?>">
            <img src="<?= $image->url() ?>"
                 alt="<?= $image->alt()->esc() ?>"
                 class="w-full h-48 object-cover hover:opacity-90 transition-opacity cursor-pointer">
          </a>
          <?php endforeach ?>
        </div>
      </section>
      <?php endif ?>

      <!-- Subpages Section -->
      <?php if ($page->hasChildren()): ?>
      <section class="mt-12 pt-8 border-t border-gray-200">
        <h2 class="text-2xl font-bold mb-6">Weitere Artikel</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php foreach ($page->children()->listed() as $subpage): ?>
          <a href="<?= $subpage->url() ?>" class="group border border-gray-200 overflow-hidden hover:border-leihlokal-500 transition-colors">
            <?php if ($cover = $subpage->cover()->toFile()): ?>
            <div class="aspect-video overflow-hidden">
              <img src="<?= $cover->url() ?>"
                   alt="<?= $subpage->title()->esc() ?>"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            <?php endif ?>
            <div class="p-4">
              <h3 class="font-bold text-lg mb-2 group-hover:text-leihlokal-500 transition-colors"><?= $subpage->title()->esc() ?></h3>
              <?php if ($subpage->intro()->isNotEmpty()): ?>
                <p class="text-gray-600 text-sm line-clamp-2"><?= $subpage->intro()->excerpt(120) ?></p>
              <?php endif ?>
            </div>
          </a>
          <?php endforeach ?>
        </div>
      </section>
      <?php endif ?>
    </article>

    <!-- Sidebar -->
    <aside class="lg:w-1/3 space-y-6">
      <!-- Table of Contents (Generated from h2, h3 in content) -->
      <div class="bg-gray-50 border border-gray-200 p-4 md:p-6 sticky top-8">
        <h3 class="font-bold text-lg mb-4">Auf dieser Seite</h3>
        <nav id="toc" class="space-y-2 text-sm">
          <!-- Will be populated by JavaScript -->
        </nav>
      </div>

      <!-- Sidebar Content -->
      <?php if ($page->sidebar_content()->isNotEmpty()): ?>
      <div class="bg-leihlokal-50 border border-leihlokal-200 p-4 md:p-6">
        <div class="prose prose-sm">
          <?= $page->sidebar_content()->kt() ?>
        </div>
      </div>
      <?php endif ?>

      <!-- Related Links -->
      <?php if ($page->related_links()->isNotEmpty()): ?>
      <div class="border border-gray-200 p-4 md:p-6">
        <h3 class="font-bold text-lg mb-4">Weiterführende Links</h3>
        <ul class="space-y-2">
          <?php foreach ($page->related_links()->toStructure() as $link): ?>
          <li>
            <a href="<?= $link->url()->esc() ?>" class="text-leihlokal-500 hover:text-leihlokal-600 hover:underline" target="_blank" rel="noopener">
              <?= $link->title()->esc() ?>
              <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </li>
          <?php endforeach ?>
        </ul>
      </div>
      <?php endif ?>
    </aside>

  </div>
</main>

<!-- Table of Contents Generator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const article = document.querySelector('article .prose');
  const toc = document.getElementById('toc');

  if (article && toc) {
    const headings = article.querySelectorAll('h2, h3');

    if (headings.length > 0) {
      headings.forEach((heading, index) => {
        // Add ID to heading
        const id = 'heading-' + index;
        heading.id = id;

        // Create TOC link
        const link = document.createElement('a');
        link.href = '#' + id;
        link.textContent = heading.textContent;
        link.className = 'block text-gray-700 hover:text-leihlokal-500 transition-colors';

        if (heading.tagName === 'H3') {
          link.className += ' pl-4';
        }

        toc.appendChild(link);
      });
    } else {
      toc.innerHTML = '<p class="text-gray-500 text-sm">Keine Überschriften gefunden</p>';
    }
  }
});
</script>

<?php snippet("footer"); ?>
