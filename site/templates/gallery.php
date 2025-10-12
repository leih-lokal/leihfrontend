<?php snippet("header"); ?>

<?php
// Get gallery settings
$layout = $page->layout()->or('grid')->value();
$columns = $page->columns()->or('3')->value();
$enableLightbox = $page->lightbox()->or('true')->toBool();
$showCaptions = $page->show_captions()->or('true')->toBool();
$lazyLoad = $page->lazy_load()->or('true')->toBool();
$enableCategories = $page->enable_categories()->toBool();

// Get categories from images
$categories = [];
if ($enableCategories) {
    foreach ($page->images() as $image) {
        if ($image->category()->isNotEmpty()) {
            $cat = $image->category()->value();
            if (!in_array($cat, $categories)) {
                $categories[] = $cat;
            }
        }
    }
}

// Grid columns class mapping
$gridClasses = [
    '2' => 'grid-cols-1 md:grid-cols-2',
    '3' => 'grid-cols-2 md:grid-cols-3',
    '4' => 'grid-cols-2 md:grid-cols-4'
];
$gridClass = $gridClasses[$columns] ?? 'grid-cols-2 md:grid-cols-3';
?>

<main>
  <!-- Header Section -->
  <section class="container mx-auto px-4 md:px-8 py-8 md:py-12 border-b border-gray-200">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4"><?= $page->title()->esc() ?></h1>
    <?php if ($page->intro()->isNotEmpty()): ?>
    <div class="prose prose-lg max-w-3xl">
      <?= $page->intro()->kt() ?>
    </div>
    <?php endif ?>
  </section>

  <!-- Category Filter -->
  <?php if ($enableCategories && count($categories) > 0): ?>
  <section class="container mx-auto px-4 md:px-8 py-6 border-b border-gray-200">
    <div class="flex flex-wrap gap-2">
      <button class="category-filter px-4 py-2 bg-leihlokal-500 text-white font-medium transition-colors active"
              data-category="all">
        Alle
      </button>
      <?php foreach ($categories as $category): ?>
      <button class="category-filter px-4 py-2 bg-gray-200 hover:bg-gray-300 font-medium transition-colors"
              data-category="<?= $category ?>">
        <?= $category ?>
      </button>
      <?php endforeach ?>
    </div>
  </section>
  <?php endif ?>

  <!-- Gallery Grid -->
  <?php if ($page->images()->count() > 0): ?>
  <section class="container mx-auto px-4 md:px-8 py-8 md:py-12">
    <div class="gallery-grid grid <?= $gridClass ?> gap-4 md:gap-6 <?= $layout === 'masonry' ? 'masonry' : '' ?>">
      <?php foreach ($page->images() as $image): ?>
      <div class="gallery-item <?= $enableCategories && $image->category()->isNotEmpty() ? 'category-' . $image->category()->value() : '' ?>"
           data-category="<?= $image->category()->value() ?>">
        <?php
        // Build lightbox title with caption and photographer
        $lightboxTitle = '';
        if ($image->caption()->isNotEmpty()) {
            $lightboxTitle = $image->caption()->esc();
        }
        if ($image->photographer()->isNotEmpty()) {
            $lightboxTitle .= ($lightboxTitle ? ' | ' : '') . 'Foto: ' . $image->photographer()->esc();
        }
        ?>

        <?php if ($enableLightbox): ?>
        <a href="<?= $image->url() ?>"
           data-lightbox="gallery"
           <?= $lightboxTitle ? 'data-title="' . $lightboxTitle . '"' : '' ?>
           class="block group relative overflow-hidden">
        <?php else: ?>
        <div class="block group relative overflow-hidden">
        <?php endif ?>

          <img src="<?= $image->url() ?>"
               alt="<?= $image->alt()->or($image->caption())->or($image->filename())->esc() ?>"
               class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
               <?= $lazyLoad ? 'loading="lazy"' : '' ?>>

          <?php if ($showCaptions && ($image->caption()->isNotEmpty() || $image->photographer()->isNotEmpty())): ?>
          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-white opacity-0 group-hover:opacity-100 transition-opacity">
            <?php if ($image->caption()->isNotEmpty()): ?>
            <p class="text-sm font-medium mb-1"><?= $image->caption()->esc() ?></p>
            <?php endif ?>
            <?php if ($image->photographer()->isNotEmpty()): ?>
            <p class="text-xs text-white/80">Foto: <?= $image->photographer()->esc() ?></p>
            <?php endif ?>
            <?php if ($image->date()->isNotEmpty()): ?>
            <p class="text-xs text-white/70 mt-1"><?= $image->date()->toDate('d.m.Y') ?></p>
            <?php endif ?>
          </div>
          <?php endif ?>

        <?php if ($enableLightbox): ?>
        </a>
        <?php else: ?>
        </div>
        <?php endif ?>
      </div>
      <?php endforeach ?>
    </div>
  </section>
  <?php else: ?>
  <section class="container mx-auto px-4 md:px-8 py-12 text-center text-gray-500">
    <p>Keine Bilder in dieser Galerie.</p>
  </section>
  <?php endif ?>

  <!-- Sub-Galleries (Child Pages) -->
  <?php if ($page->hasChildren()): ?>
  <section class="bg-gray-50 border-t border-gray-200 py-12 md:py-16">
    <div class="container mx-auto px-4 md:px-8">
      <h2 class="text-3xl md:text-4xl font-bold mb-8 md:mb-12">Weitere Galerien</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <?php foreach ($page->children()->listed() as $subpage):
          $thumbnail = $subpage->images()->first() ?? $subpage->cover()->toFile();
        ?>
        <a href="<?= $subpage->url() ?>"
           class="group bg-white overflow-hidden shadow-md hover:shadow-xl transition-all">
          <?php if ($thumbnail): ?>
          <div class="aspect-[4/3] overflow-hidden">
            <img src="<?= $thumbnail->url() ?>"
                 alt="<?= $subpage->title()->esc() ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
          </div>
          <?php else: ?>
          <div class="aspect-[4/3] bg-gray-200 flex items-center justify-center">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <?php endif ?>

          <div class="p-6">
            <h3 class="text-xl font-bold mb-2 group-hover:text-leihlokal-500 transition-colors">
              <?= $subpage->title()->esc() ?>
            </h3>
            <?php if ($subpage->intro()->isNotEmpty()): ?>
            <p class="text-gray-600 text-sm line-clamp-2">
              <?= $subpage->intro()->excerpt(100) ?>
            </p>
            <?php endif ?>
            <div class="mt-3 text-sm text-gray-500">
              <?= $subpage->images()->count() ?> Bilder
            </div>
          </div>
        </a>
        <?php endforeach ?>
      </div>
    </div>
  </section>
  <?php endif ?>
</main>

<!-- Category Filter Script -->
<?php if ($enableCategories && count($categories) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterButtons = document.querySelectorAll('.category-filter');
  const galleryItems = document.querySelectorAll('.gallery-item');

  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const category = this.dataset.category;

      // Update active button
      filterButtons.forEach(btn => {
        btn.classList.remove('active', 'bg-leihlokal-500', 'text-white');
        btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
      });
      this.classList.add('active', 'bg-leihlokal-500', 'text-white');
      this.classList.remove('bg-gray-200', 'hover:bg-gray-300');

      // Filter items
      galleryItems.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
});
</script>
<?php endif ?>

<!-- Masonry Layout Script (if needed) -->
<?php if ($layout === 'masonry'): ?>
<style>
.masonry {
  column-count: <?= $columns ?>;
  column-gap: 1.5rem;
}
.masonry .gallery-item {
  break-inside: avoid;
  margin-bottom: 1.5rem;
}
@media (max-width: 768px) {
  .masonry {
    column-count: 2;
  }
}
</style>
<?php endif ?>

<?php snippet("footer"); ?>
