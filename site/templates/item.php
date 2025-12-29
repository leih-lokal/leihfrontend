<?php snippet("header") ?>

<!-- Breadcrumbs -->
<div class="container mx-auto px-4 py-4">
  <nav class="text-sm">
    <a href="<?= $site->url() ?>" class="text-leihlokal-600 hover:text-leihlokal-700">Home</a>
    <span class="mx-2">/</span>
    <a href="<?= $page->parent()->url() ?>" class="text-leihlokal-600 hover:text-leihlokal-700"><?= $page->parent()->title() ?></a>
    <span class="mx-2">/</span>
    <span class="text-gray-600"><?= $page->title() ?></span>
  </nav>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto">

    <!-- Item Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row items-start sm:justify-between gap-6">
        <div class="flex-1 w-full sm:w-auto">
          <h1 class="text-3xl sm:text-4xl font-bold mb-2"><?= $page->name() ?></h1>
          <?php if ($page->brand()->isNotEmpty()): ?>
          <p class="text-lg sm:text-xl text-gray-600 mb-3">
            <?= $page->brand() ?>
            <?php if ($page->itemmodel()->isNotEmpty()): ?>
              - <?= $page->itemmodel() ?>
            <?php endif ?>
          </p>
          <?php endif ?>

          <!-- Status Badge -->
          <?php
          $status = $page->availability()->value();
          $statusColors = [
            'instock' => 'bg-green-100 text-green-800 border-green-300',
            'reserved' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'outofstock' => 'bg-red-100 text-red-800 border-red-300',
            'onbackorder' => 'bg-orange-100 text-orange-800 border-orange-300',
          ];
          $statusLabels = [
            'instock' => 'Verfügbar',
            'reserved' => 'Reserviert',
            'outofstock' => 'Ausgeliehen',
            'onbackorder' => 'In Reparatur',
          ];
          $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';
          $statusLabel = $statusLabels[$status] ?? ucfirst($status);
          ?>
          <div class="inline-block px-4 py-2 border-2 <?= $statusColor ?> font-bold text-sm sm:text-base">
            <?= $statusLabel ?>
          </div>
        </div>

        <!-- Prominent Item ID -->
        <div class="flex-shrink-0">
          <?php
          $iid = str_pad($page->iid(), 4, '0', STR_PAD_LEFT);
          $firstTwo = substr($iid, 0, 2);
          $lastTwo = substr($iid, 2, 2);
          ?>
          <div class="text-5xl sm:text-6xl lg:text-7xl font-bold font-mono leading-none">
            <span class="border border-2 border-leihlokal-500 bg-leihlokal-500 text-white p-2"><?= $firstTwo ?></span><span class="border border-2 border-leihlokal-500 p-2 text-leihlokal-500"><?= $lastTwo ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

      <!-- Left: Image -->
      <div class="lg:col-span-1">
        <?php
        $images = $page->itemimages()->value();
        $imageArray = !empty($images) ? explode(',', $images) : [];
        $firstImage = $imageArray[0] ?? null;
        $recordId = $page->recordid()->value(); // PocketBase record ID
        ?>

        <?php if ($firstImage): ?>
          <div class="border border-gray-200 p-4 bg-white">
            <a href="https://buergerstiftung-karlsruhe.de/api/files/item/<?= $recordId ?>/<?= trim($firstImage) ?>"
               data-lightbox="item-<?= $page->iid() ?>"
               data-title="<?= $page->name() ?>">
              <img
                src="https://buergerstiftung-karlsruhe.de/api/files/item/<?= $recordId ?>/<?= trim($firstImage) ?>?thumb=512x512f"
                alt="<?= $page->name() ?>"
                class="w-full h-auto cursor-pointer hover:opacity-90 transition-opacity"
                onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f3f4f6\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'18\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EKein Bild verfügbar%3C/text%3E%3C/svg%3E';"
              >
            </a>

            <?php if (count($imageArray) > 1): ?>
            <div class="mt-2 text-sm text-gray-600">
              +<?= count($imageArray) - 1 ?> weitere Bilder
            </div>
            <?php endif ?>
          </div>
        <?php else: ?>
          <div class="border border-gray-200 p-4 bg-gray-50 flex items-center justify-center" style="min-height: 300px;">
            <span class="text-gray-400">Kein Bild verfügbar</span>
          </div>
        <?php endif ?>

        <!-- Quick Info Card -->
        <div class="border border-black mt-6">
          <div class="bg-leihlokal-500 text-white p-4 font-bold">
            Leihdetails
          </div>
          <div class="p-4 space-y-4">
            <!-- Prominent Deposit -->
            <div class="bg-leihlokal-50 border-2 border-leihlokal-500 p-3 sm:p-4 text-center">
              <div class="text-xs sm:text-sm text-gray-600 mb-1">Pfand</div>
              <div class="text-3xl sm:text-4xl font-bold text-leihlokal-600">€<?= $page->deposit() ?></div>
            </div>

            <?php if ($page->category()->isNotEmpty()): ?>
            <div class="flex justify-between items-center pt-2">
              <span class="text-gray-600">Kategorie:</span>
              <span class="font-medium"><?= $page->category() ?></span>
            </div>
            <?php endif ?>

            <div class="flex justify-between items-center">
              <span class="text-gray-600">Exemplare:</span>
              <span class="font-medium"><?= $page->copies() ?>x</span>
            </div>

            <?php if ($page->parts()->toInt() > 1): ?>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Teile:</span>
              <span class="font-medium"><?= $page->parts() ?></span>
            </div>
            <?php endif ?>
          </div>
        </div>
      </div>

      <!-- Right: Details -->
      <div class="lg:col-span-2">

        <!-- Description -->
        <?php if ($page->description()->isNotEmpty()): ?>
        <div class="mb-8">
          <h2 class="text-2xl font-bold mb-4">Beschreibung</h2>
          <div class="prose max-w-none text-gray-700">
            <?= $page->description()->kt() ?>
          </div>
        </div>
        <?php endif ?>

        <!-- Manual -->
        <?php if ($page->manual()->isNotEmpty()): ?>
        <div class="mb-8">
          <h2 class="text-2xl font-bold mb-4">Anleitung</h2>
          <div class="bg-leihlokal-50 border-l-4 border-leihlokal-500 p-4">
            <?= nl2br($page->manual()->html()) ?>
          </div>
        </div>
        <?php endif ?>

        <!-- Additional Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

          <?php if ($page->packaging()->isNotEmpty()): ?>
          <div>
            <h3 class="font-bold text-lg mb-2">Verpackung</h3>
            <p class="text-gray-700"><?= $page->packaging() ?></p>
          </div>
          <?php endif ?>

          <?php if ($page->synonyms()->isNotEmpty()): ?>
          <div>
            <h3 class="font-bold text-lg mb-2">Auch bekannt als</h3>
            <p class="text-gray-700"><?= $page->synonyms() ?></p>
          </div>
          <?php endif ?>
        </div>

        <!-- Action Buttons -->
        <div class="border-t pt-6">
          <a
            href="<?= $page->parent()->url() ?>"
            class="inline-block bg-leihlokal-500 hover:bg-leihlokal-600 text-white px-6 py-3 transition-colors"
          >
            ← Zurück zur Übersicht
          </a>

          <?php
          // Note: Reservation functionality via basket/cookie is implemented on the main leihlokal page
          // Users can add items to basket there. Direct reservation from detail page to be added later.
          ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php snippet("footer") ?>
