<?php snippet("header"); ?>

<main class="min-h-[80vh] flex items-center justify-center px-4">
  <div class="max-w-3xl mx-auto text-center">
    <h1 class="text-[12rem] leading-none font-bold mb-0 animate-fade-in bg-gradient-to-br from-leihlokal-400 via-leihlokal-500 to-leihlokal-600 text-transparent bg-clip-text">404</h1>
    <h2 class="text-4xl font-semibold mt-4 mb-8">Seite nicht gefunden</h2>
    <p class="text-lg text-gray-600 mb-12">Diese Seite existiert möglicherweise nicht oder wurde verschoben.</p>
    
    <div class="flex justify-center items-center gap-4">
      <button onclick="window.history.back()" class="px-6 py-3 text-gray-700 hover:text-leihlokal-500 hover:underline font-medium transition-colors duration-200">
        ← Zurück
      </button>
      <a href="<?= $site->url() ?>" class="px-6 py-3 bg-leihlokal-500 hover:bg-leihlokal-600 text-white font-medium transition-colors duration-200 shadow-lg hover:shadow-xl">
        Zur Startseite
      </a>
    </div>
  </div>
</main>

<?php snippet("footer"); ?>