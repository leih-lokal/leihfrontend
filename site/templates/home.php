<?php snippet("header"); ?>

<main>
  
  <?php 
      $pics = $page->immies()->toFiles();
      // You can uncomment this to debug
      // var_dump($pics->count());
  ?>

  <!-- Top image row -->
  <section class="grid grid-cols-3 md:grid-cols-6 gap-4 p-8 pb-4">
      <?php for ($i = 0; $i < 6; $i++): ?>
      <div class="aspect-square overflow-hidden border-2 border-leihlokal-500 transition-all duration-200 image-frame" data-index="<?= $i ?>">
          <?php 
          if ($pics->count() > 0): 
              $randomImage = $pics->shuffle()->first();
          ?>
              <img src="<?= $randomImage->url() ?>" 
                   alt="<?= $randomImage->alt() ?>" 
                   class="w-full h-full object-cover transition-opacity duration-500"
                   loading="lazy">
          <?php endif ?>
      </div>
      <?php endfor ?>
  </section>
  
      <section class="flex justify-between items-center p-8 pb-0 pt-4 md:text-[6vw] text-left relative z-10">
          <h1 class="font-bold uni-cd text-[8vw] p-0 leading-[0.8] inline-block md:text-[6vw] lg:text-8xl sm:text-[10vw] text-leihlokal-500">
              <?= $page->firstword()->esc() ?>
          </h1>
      </section>
      <section class="p-8 pt-0 pb-0 text-center relative z-10">
          <h1 class="font-bold uni-cd text-[8vw] p-0 leading-[0.8] inline-block md:text-[6vw] lg:text-8xl sm:text-[10vw] text-leihlokal-500">
              <?= $page->secondword()->esc() ?>
          </h1>
      </section>
      <section class="p-8 pt-0 pb-4 border-b border-leihlokal text-right relative z-10">
          <h1 class="font-bold uni-cd text-[8vw] p-0 leading-[0.8] inline-block md:text-[6vw] lg:text-8xl sm:text-[10vw] text-leihlokal-500">
              <?= $page->thirdword()->esc() ?>
          </h1>
      </section>
      
      <section class="grid grid-cols-3 md:grid-cols-6 gap-4 p-8 pt-4">
          <?php for ($i = 6; $i < 12; $i++): ?>
          <div class="aspect-square overflow-hidden border-2 border-leihlokal-500 transition-all duration-500 image-frame" data-index="<?= $i ?>">
              <?php if ($pic = $page->immies()->toFiles()->shuffle()->first()): ?>
              <img src="<?= $pic->url() ?>" 
                   alt="<?= $pic->alt() ?>" 
                   class="w-full h-full object-cover transition-opacity duration-500"
                   loading="lazy">
              <?php endif ?>
          </div>
          <?php endfor ?>
      </section>
  

    <section class="pretext">
        <p><?= $page->pretext()->kt() ?></p>
    </section>

<section class="relative overflow-hidden p-8 border-b border-leihlokal">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="relative bg-gradient-to-br from-leihlokal-50 to-white border-2 border-leihlokal-500 p-6 duration-300">
            <div class="text-[4vw] font-bold leading-[1.2] md:text-[3vw] lg:text-4xl sm:text-[5vw] mb-4" id="statDescription"></div>
            <div class="text-[12vw] text-right font-bold text-leihlokal-500 mt-auto md:text-[9vw] lg:text-12xl sm:text-[15vw]" id="statValue">0</div>
        </div>
        <div class="prose prose-lg max-w-none md:pl-8 relative">
            <div class="absolute top-0 left-0 w-1 h-full bg-leihlokal-500 opacity-20"></div>
            <?= $page->blurb()->html() ?>
        </div>
    </div>
</section>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script>
        // Convert Kirby structure to JavaScript array
        const stats = <?= json_encode(
            $page->stats()->toStructure()->toArray()
        ) ?>;

        let currentStatIndex = 0;

        function updateStat() {
            const stat = stats[currentStatIndex];
            const statDescription = document.getElementById('statDescription');
            const statValue = document.getElementById('statValue');

            // Animate stat description
            anime({
                targets: statDescription,
                opacity: [1, 0],
                translateY: [0, -20],
                easing: 'easeInOutQuad',
                duration: 500,
                complete: function (anim) {
                    statDescription.innerHTML = stat.stat;
                    anime({
                        targets: statDescription,
                        opacity: [0, 1],
                        translateY: [20, 0],
                        easing: 'easeInOutQuad',
                        duration: 500
                    });
                }
            });

            // Animate stat value
            anime({
                targets: statValue,
                innerHTML: [{
                    value: statValue.innerHTML
                }, {
                    value: stat.value
                }],
                round: 1,
                easing: 'easeInOutExpo',
                duration: 800
            });

            currentStatIndex = (currentStatIndex + 1) % stats.length;
        }

        updateStat();
        setInterval(updateStat, 5000);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const frames = document.querySelectorAll('.image-frame');
            const images = <?= json_encode($page->immies()->toFiles()->map(function($file) {
                return [
                    'url' => $file->url(),
                    'alt' => $file->alt()
                ];
            })->values()) ?>;

            function updateRandomFrame() {
                // Select random frame
                const frameIndex = Math.floor(Math.random() * frames.length);
                const frame = frames[frameIndex];
                
                // Select random image
                const imageIndex = Math.floor(Math.random() * images.length);
                const newImage = images[imageIndex];

                // Create new image element
                const img = frame.querySelector('img');
                const newImg = document.createElement('img');
                newImg.src = newImage.url;
                newImg.alt = newImage.alt;
                newImg.className = 'w-full h-full object-cover transition-opacity duration-200 opacity-0';
                
                // Add new image and fade out old one
                frame.appendChild(newImg);
                img.style.opacity = '0';

                // After transition, remove old image and fade in new one
                setTimeout(() => {
                    frame.removeChild(img);
                    newImg.style.opacity = '1';
                }, 200);

                // Schedule next update
                setTimeout(updateRandomFrame, Math.random() * 5000 + 1000); // Random interval between 1-3 seconds
            }

            // Start the updates after a short delay
            setTimeout(updateRandomFrame, 4000);
        });
    </script>

<?php snippet("footer"); ?>
