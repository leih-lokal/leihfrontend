<?php snippet('header') ?>

<link rel="stylesheet" href="<?= url('assets/css/layout.css') ?>">

<main class="container mx-auto px-4 py-8">
  <!-- Page Title -->
  
  <?php foreach ($page->layout()->toLayouts() as $layout): ?>
    <?php 
      // Calculate spacing classes based on layout settings
      $spacingClass = '';
      switch ($layout->spacing()) {
        case 'small': 
          $spacingClass = 'py-4'; 
          break;
        case 'medium': 
          $spacingClass = 'py-8'; 
          break;
        case 'large': 
          $spacingClass = 'py-16'; 
          break;
        default: 
          $spacingClass = ''; 
          break;
      }
    ?>
    
    <section class="grid grid-cols-12 gap-4 <?= $layout->class() ?> <?= $spacingClass ?>" 
             id="<?= $layout->id() ?>"
             <?php if($layout->background()): ?>style="background-color: <?= $layout->background() ?>;"<?php endif ?>>
      
      <?php foreach ($layout->columns() as $column): ?>
        <div style="grid-column: span <?= $column->span() ?> / span <?= $column->span() ?>;" data-width="<?= $column->width() ?>">
          <div class="text-leihlokal">
            <?php foreach ($column->blocks() as $block): ?>
              <div class="block block-type-<?= $block->type() ?>">
                <?= $block ?>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      <?php endforeach ?>
      
    </section>
  <?php endforeach ?>
</main>

<?php snippet('footer') ?>
