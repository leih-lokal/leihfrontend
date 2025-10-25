<?php snippet("header") ?>

<?php
// Days translation for opening hours
$daysTranslation = [
    'mon' => 'Montag',
    'tue' => 'Dienstag',
    'wed' => 'Mittwoch',
    'thu' => 'Donnerstag',
    'fri' => 'Freitag',
    'sat' => 'Samstag',
    'sun' => 'Sonntag'
];

// Day codes for PHP date function (used to get today)
$dayCodesMap = [
    0 => 'sun', // Sunday
    1 => 'mon', // Monday
    2 => 'tue', // Tuesday
    3 => 'wed', // Wednesday
    4 => 'thu', // Thursday
    5 => 'fri', // Friday
    6 => 'sat'  // Saturday
];

// Helper function to format time as HH:MM
function formatTime($timeString) {
    if (empty($timeString)) return "";
    // Parse the time string and reformat it to HH:MM
    $time = strtotime($timeString);
    return date('H:i', $time);
}

// Get today's day code
$todayDayCode = $dayCodesMap[date('w')];
$todayName = $daysTranslation[$todayDayCode];

// Get today's opening hours
$todayHours = null;
foreach ($page->hours()->toStructure() as $day) {
    if ($day->day()->value() === $todayDayCode) {
        $todayHours = $day;
        break;
    }
}
?>

<!-- Today's Opening Hours with Toggle -->
<div class="relative bg-white border-b border-leihlokal-500">
  <div class="container mx-auto px-4 py-3">
    <div class="flex justify-between items-center cursor-pointer hover:bg-gray-50 transition-colors px-2 -mx-2 rounded" id="toggleHours">
      <div class="flex items-center gap-3">
        <svg class="h-6 w-6 text-leihlokal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex items-center gap-2">
          <span class="font-bold text-base">Heute (<?= $todayName ?>)</span>
          <?php if ($todayHours && $todayHours->opened()->bool()): ?>
            <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-800 px-2 py-1 text-xs font-semibold">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span>
              GEÖFFNET
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-800 px-2 py-1 text-xs font-semibold">
              <span class="w-2 h-2 bg-red-500 rounded-full"></span>
              GESCHLOSSEN
            </span>
          <?php endif ?>
        </div>
        <span class="ml-2 text-lg font-mono font-semibold text-leihlokal-600">
          <?php if ($todayHours && $todayHours->opened()->bool()): ?>
            <?= formatTime($todayHours->open_time()) ?> - <?= formatTime($todayHours->close_time()) ?>
          <?php else: ?>
            —
          <?php endif ?>
        </span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 hidden sm:inline">Alle Zeiten</span>
        <svg id="hoursChevron" class="h-5 w-5 text-gray-600 transform transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
  </div>

  <!-- Full Hours Dropdown -->
  <div id="fullHours" class="hidden border-t border-leihlokal-500 bg-white overflow-hidden transition-all duration-300">
    <div class="container mx-auto px-4 py-6">
      <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
        <svg class="h-5 w-5 text-leihlokal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        Wochenübersicht
      </h3>
      <div class="border border-leihlokal-500">
        <table class="w-full">
          <thead>
            <tr class="bg-leihlokal-500 text-white">
              <th class="text-left py-3 px-4 font-bold">Tag</th>
              <th class="text-left py-3 px-4 font-bold">Öffnungszeiten</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($page->hours()->toStructure() as $day): ?>
              <tr class="border-t border-gray-300 <?= $day->day()->value() === $todayDayCode ? 'bg-leihlokal-50' : 'bg-white' ?> hover:bg-gray-50 transition-colors">
                <td class="py-3 px-4 font-medium <?= $day->day()->value() === $todayDayCode ? 'text-leihlokal-600 font-bold' : '' ?>">
                  <div class="flex items-center gap-2">
                    <?= $daysTranslation[$day->day()->value()] ?>
                    <?php if ($day->day()->value() === $todayDayCode): ?>
                      <span class="inline-block w-2 h-2 bg-leihlokal-500 rounded-full"></span>
                    <?php endif ?>
                  </div>
                </td>
                <td class="py-3 px-4 font-mono <?= $day->day()->value() === $todayDayCode ? 'font-bold text-leihlokal-600' : '' ?>">
                  <?php if ($day->opened()->bool()): ?>
                    <?= formatTime($day->open_time()) ?> - <?= formatTime($day->close_time()) ?>
                  <?php else: ?>
                    <span class="text-gray-400">Geschlossen</span>
                  <?php endif ?>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Important Notice -->
<?php if($site->importanttoggle()->bool()): ?>
<?php
// Get notice settings
$intensity = $site->notice_intensity()->value() ?: 'info';
$iconType = $site->notice_icon()->value() ?: 'info';

// Define styling based on intensity
$styles = [
  'info' => [
    'container' => 'bg-blue-50 border-l-4 border-blue-400',
    'icon_color' => 'text-blue-500',
    'text_color' => 'text-blue-800',
    'text_size' => 'text-sm',
    'icon_size' => 'h-5 w-5',
    'animation' => ''
  ],
  'warning' => [
    'container' => 'bg-yellow-50 border-l-4 border-yellow-500',
    'icon_color' => 'text-yellow-600',
    'text_color' => 'text-yellow-900',
    'text_size' => 'text-base',
    'icon_size' => 'h-6 w-6',
    'animation' => ''
  ],
  'urgent' => [
    'container' => 'bg-red-100 border-l-4 border-leihlokal-500',
    'icon_color' => 'text-leihlokal-500',
    'text_color' => 'text-leihlokal-600',
    'text_size' => 'text-base font-bold',
    'icon_size' => 'h-7 w-7',
    'animation' => 'notice-pulse'
  ]
];

$style = $styles[$intensity];

// Define icons
$icons = [
  'info' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />',
  'warning' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />',
  'alert' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />',
  'clock' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />'
];

$icon = $icons[$iconType];
?>
<div class="<?= $style['container'] ?> p-4 my-2 <?= $style['animation'] ?>">
  <div class="container mx-auto">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 <?= $intensity === 'urgent' ? 'notice-bounce' : '' ?>">
        <svg class="<?= $style['icon_size'] ?> <?= $style['icon_color'] ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <?= $icon ?>
        </svg>
      </div>
      <div class="flex-1">
        <div class="<?= $style['text_color'] ?> <?= $style['text_size'] ?>"><?= $site->importantnotice()->kt() ?></div>
      </div>
    </div>
  </div>
</div>
<?php endif ?>

<!-- Hours Toggle Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const toggleHours = document.getElementById('toggleHours');
    const fullHours = document.getElementById('fullHours');
    const hoursChevron = document.getElementById('hoursChevron');
    
    toggleHours.addEventListener('click', function() {
      fullHours.classList.toggle('hidden');
      hoursChevron.classList.toggle('rotate-180');
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<?php

// Helper function to generate 30-minute time slots
function generateTimeSlots($start, $end) {
    $slots = [];
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    
    while ($startTime < $endTime) {
        $slots[] = date('H:i', $startTime);
        $startTime = strtotime('+30 minutes', $startTime);
    }
    
    return $slots;
}

// Get opening hours from Kirby
$openingHours = $page->hours()->toStructure();

// Process opening hours into a week schedule with time slots
$weekSchedule = [];

foreach ($openingHours as $day) {
    $dayKey = $day->day()->value();
    $weekSchedule[$dayKey] = [
        'name' => $daysTranslation[$dayKey],
        'isOpen' => $day->opened()->bool(),
        'slots' => $day->opened()->bool() 
            ? generateTimeSlots($day->open_time()->value(), $day->close_time()->value())
            : []
    ];
}
?>

<!-- Container and Skeleton -->
<div class="container mx-auto px-4 py-8">
  <div class="flex flex-col lg:flex-row gap-8">
    
    <!-- Left Sidebar (Categories & Cart) -->
    <div class="w-full lg:w-1/3">
      <!-- Mobile Toggle Button -->
      <button id="sidebarToggleBtn" class="lg:hidden w-full mb-4 p-4 bg-leihlokal-500 text-white text-left flex justify-between items-center">
        Menu & Cart
        <svg id="sidebarToggleIcon" class="w-6 h-6 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
      </button>

      <!-- Sidebar Content (toggleable on mobile) -->
      <div class="hidden lg:block space-y-8 lg:sticky lg:top-8" id="mobileSidebar">
        <!-- Subpages -->
        <?php
        $realSubpages = $page->children()->filterBy('intendedTemplate', '!=', 'item');
        if ($realSubpages->isNotEmpty()):
        ?>
        <div class="border border-black">
            <div class="bg-leihlokal-500 text-white p-4">Mehr zum leih.lokal</div>
            <div class="p-4 space-y-2">
                <?php foreach ($realSubpages as $subpage): ?>
                <a href="<?= $subpage->url() ?>" class="block p-2 hover:bg-gray-100 transition-colors">
                    <?= $subpage->title() ?>
                </a>
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>

        <!-- Categories -->
        <div class="border border-black">
            <div class="bg-leihlokal-500 text-white p-4">Rubriken</div>
            <div class="p-4 space-y-2">
                <div id="cat-all" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="" 
                     data-active="true">
                    Alle Sachen
                </div>
                <div id="cat-freizeit" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Freizeit" 
                     data-active="false">
                    Freizeit
                </div>
                <div id="cat-garten" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Garten" 
                     data-active="false">
                    Garten
                </div>
                <div id="cat-haushalt" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Haushalt" 
                     data-active="false">
                    Haushalt
                </div>
                <div id="cat-heimwerken" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Heimwerken" 
                     data-active="false">
                    Heimwerken
                </div>
                <div id="cat-kinder" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Kinder" 
                     data-active="false">
                    Kinder
                </div>
                <div id="cat-kueche" 
                     class="cursor-pointer p-2 hover:bg-gray-100 transition-colors category-filter font-normal data-[active=true]:bg-leihlokal-500 data-[active=true]:text-white" 
                     data-category="Küche" 
                     data-active="false">
                    Küche
                </div>
            </div>
        </div>

        <!-- Cart -->
        <div class="border border-black">
            <div class="bg-leihlokal-500 text-white p-4">Ausleihkorb</div>
            <div id="cartItems" class="p-4 space-y-2">
                <!-- Cart items will be inserted here -->
                <div class="empty-cart-message text-gray-500 text-sm">Hier ist noch nichts. Such' dir was aus!</div>
            </div>
            <div class="p-4">
                <button id="completeReservation" class="w-full mb-2 bg-leihlokal-500 hover:bg-leihlokal-600 text-white p-2 hidden">
                  Vorbestellung absenden
                </button>
                <button id="clearCart" class="w-full bg-white hover:bg-gray-200 text-black border p-2 hidden">
                  Alles zurücklegen
                </button>
            </div>
        </div>
      </div>
    </div>

    <!-- Right Content (Product List) -->
    <div class="w-full lg:w-2/3">
      <!-- Search Bar -->
      <div class="mb-8 flex gap-4 items-center">
          <div class="flex-1">
              <input type="text" 
                     id="searchInput"
                     placeholder="Durchsuchen..." 
                     class="w-full p-4 border border-black focus:outline-none focus:border-leihlokal-500">
          </div>
          <div class="flex items-center gap-2">
              <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="availableToggle" class="sr-only peer" checked>
                  <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-leihlokal-500"></div>
              </label>
              <span class="text-sm text-gray-600">Nur ausleihbar</span>
          </div>
      </div>

      <!-- Product Grid -->
      <div id="productGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-7">
        <!-- Products will be inserted here via JavaScript -->
      </div>

      <!-- Pagination -->
      <div id="pagination" class="mt-8 flex justify-center space-x-2">
        <button id="prevPage" class="px-4 py-2 border border-black hover:bg-leihlokal-800 hover:text-white">
          &larr; Previous
        </button>
        <div id="pageNumbers" class="flex space-x-2">
          <!-- Page numbers will be inserted here -->
        </div>
        <button id="nextPage" class="px-4 py-2 border border-black hover:bg-leihlokal-800 hover:text-white">
          Next &rarr;
        </button>
      </div>
    </div>

  </div>
</div>

<!-- Modal Container -->
<div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white max-w-lg w-full relative border-2 border-black">
        <!-- Close button -->
        <button id="closeModal" class="absolute  bg-white border-2 border-black p-2 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Modal Content -->
        <div id="itemModalContent">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>

<!-- Reservation Modal -->
<div id="reservationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white max-w-2xl w-full mx-4 relative shadow-xl border-2 border-leihlokal-600">
        <!-- Close button -->
        <button id="closeReservationModal" class="absolute -top-3 -right-3 bg-white border-2 border-leihlokal-600 p-2 hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Modal Header -->
        <div class="bg-leihlokal-500 text-white p-6">
            <h2 class="text-2xl font-bold">Deine Reservierung</h2>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <!-- Cart Summary -->
            <div class="mb-6">
                <h3 class="text-lg font-bold mb-4">Ausleihkorb</h3>
                <div id="reservationCartItems" class="space-y-2 mb-4">
                    <!-- Cart items will be inserted here -->
                </div>
                <div class="text-right border-t pt-4">
                    <p class="text-lg font-bold">Gesamtpfand: <span id="totalDeposit">€0</span></p>
                </div>
            </div>

            <!-- User Type Switch -->
            <div class="flex items-center justify-center space-x-4 mb-6">
                <span class="text-gray-500">Neue:r Nutzer:in</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="userTypeSwitch" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-200 rounded-full peer-focus:outline-none peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-leihlokal-500"></div>
                </label>
                <span class="text-gray-500">Bestandsnutzer:in</span>
            </div>

            <!-- Forms Container -->
            <div id="formContainer">
                <!-- New User Form -->
                <form id="newUserForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nachname</label>
                        <input type="text" required 
                               class="w-full p-2 border border-gray-300  focus:outline-none focus:border-leihlokal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" required 
                               class="w-full p-2 border border-gray-300  focus:outline-none focus:border-leihlokal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefonnummer für Rückfragen</label>
                        <input type="tel" required 
                               class="w-full p-2 border border-gray-300  focus:outline-none focus:border-leihlokal-500">
                    </div>
                </form>

                <!-- Existing User Form -->
                <form id="existingUserForm" class="space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nutzernummer</label>
                        <input type="text" required pattern="\d{4}" maxlength="4" 
                               placeholder="Gib deine 4-stellige Nutzernummer ein (beispielsweise 0123)"
                               class="w-full p-2 border border-gray-300  focus:outline-none focus:border-leihlokal-500">
                    </div>
                </form>
            </div>
            
            <!-- Time Slot Selection (Initially Hidden) -->
            <div id="timeSlotSelection" class="hidden">
                <h3 class="text-lg font-bold mb-4">Wähle einen Abholtermin</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead>
                            <tr>
                                <?php foreach ($weekSchedule as $day): ?>
                                <th class="border-b px-4 py-2 bg-gray-50 text-sm">
                                    <?= $day['name'] ?>
                                </th>
                                <?php endforeach ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Find the maximum number of slots
                            $maxSlots = 0;
                            foreach ($weekSchedule as $day) {
                                $maxSlots = max($maxSlots, count($day['slots']));
                            }
                            
                            // Generate rows for each time slot
                            for ($i = 0; $i < $maxSlots; $i++):
                            ?>
                            <tr>
                                <?php foreach ($weekSchedule as $dayKey => $day): ?>
                                <td class="border px-4 py-2 text-sm">
                                    <?php if ($day['isOpen'] && isset($day['slots'][$i])): ?>
                                    <button 
                                        class="w-full text-center py-1 transition-colors hover:bg-leihlokal-100 time-slot-button"
                                        data-day="<?= $dayKey ?>"
                                        data-time="<?= $day['slots'][$i] ?>"
                                    >
                                        <?= $day['slots'][$i] ?>
                                    </button>
                                    <?php endif ?>
                                </td>
                                <?php endforeach ?>
                            </tr>
                            <?php endfor ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-sm text-gray-600">
                    Selected time: <span id="selectedTimeDisplay">None</span>
                </div>
            </div>
            
            <!-- Confirmation Step (Initially Hidden) -->
            <div id="confirmationStep" class="hidden text-center">
                <div class="mb-8">
                    <div class="inline-block p-4 rounded-full bg-red-100 mb-4">
                        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Deine Reservierung ist bestätigt!</h3>
                    <p class="text-gray-600">Wir freuen uns auf deinen Besuch.</p>
                </div>
                
                <div class="bg-gray-50 p-6 mb-8">
                    <h4 class="font-bold mb-4">Reservierungsdetails</h4>
                    <div id="confirmationDetails" class="space-y-2 text-left">
                        <!-- Details will be inserted here -->
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button id="addToCalendar" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300  shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Zum Kalender hinzufügen
                    </button>
                    <button id="shareReservation" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300  shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Reservierung teilen
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <button id="submitReservation" 
                        class="w-full bg-leihlokal-500 text-white p-3 hover:bg-leihlokal-600 transition-colors">
                          Vorbestellung abschicken! &rarr;
                </button>
            </div>
        </div>
    </div>
</div>




<!-- Scripts -->
<script type="text/javascript">

// Mobile sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    
    toggleBtn.addEventListener('click', function() {
        const sidebar = document.getElementById('mobileSidebar');
        if (sidebar) {
            sidebar.classList.toggle('hidden');
            // Rotate icon when toggled
            toggleIcon.classList.toggle('rotate-180');
        }
    });
});

    window.weekSchedule = <?= json_encode($weekSchedule) ?>;

</script>

<!-- Module Script -->
<script type="module" src="<?= url('assets/js/leihlokal-ui.js') ?>"></script>

<?php snippet("footer") ?>