<?php snippet("header"); ?>

<?php
// Days translation for opening hours
$daysTranslation = [
  "mon" => "Montag",
  "tue" => "Dienstag",
  "wed" => "Mittwoch",
  "thu" => "Donnerstag",
  "fri" => "Freitag",
  "sat" => "Samstag",
  "sun" => "Sonntag",
];

/**
 * Check if a given date is a public holiday in Baden-Württemberg, Germany
 * Returns the holiday name if it's a holiday, false otherwise
 *
 * @param int|null $timestamp Unix timestamp to check (defaults to today)
 * @return string|false Holiday name or false if not a holiday
 */
function isBadenWuerttembergHoliday($timestamp = null)
{
  if ($timestamp === null) {
    $timestamp = time();
  }

  $year = (int) date("Y", $timestamp);
  $month = (int) date("n", $timestamp);
  $day = (int) date("j", $timestamp);

  // Fixed holidays
  $fixedHolidays = [
    "1-1" => "Neujahrstag",
    "1-6" => "Heilige Drei Könige",
    "5-1" => "Tag der Arbeit",
    "10-3" => "Tag der Deutschen Einheit",
    "11-1" => "Allerheiligen",
    "12-25" => "1. Weihnachtstag",
    "12-26" => "2. Weihnachtstag",
  ];

  $dateKey = "{$month}-{$day}";
  if (isset($fixedHolidays[$dateKey])) {
    return $fixedHolidays[$dateKey];
  }

  // Calculate Easter Sunday for the year
  // easter_date() returns timestamp for Easter Sunday
  $easterTimestamp = easter_date($year);

  // Movable holidays based on Easter
  $movableHolidays = [
    -2 => "Karfreitag",           // Good Friday: 2 days before Easter
    1 => "Ostermontag",           // Easter Monday: 1 day after Easter
    39 => "Christi Himmelfahrt",  // Ascension Day: 39 days after Easter
    50 => "Pfingstmontag",        // Whit Monday: 50 days after Easter
    60 => "Fronleichnam",         // Corpus Christi: 60 days after Easter
  ];

  foreach ($movableHolidays as $daysOffset => $holidayName) {
    $holidayTimestamp = strtotime("{$daysOffset} days", $easterTimestamp);
    if (
      date("Y-m-d", $holidayTimestamp) === date("Y-m-d", $timestamp)
    ) {
      return $holidayName;
    }
  }

  return false;
}

// Check if today is a holiday
$todayHoliday = isBadenWuerttembergHoliday();
$isHolidayToday = $todayHoliday !== false;

// Get custom holiday message from page field, fallback to holiday name
$holidayDisplayMessage = $page->holiday_message()->isNotEmpty()
  ? $page->holiday_message()->value()
  : $todayHoliday;

// Day codes for PHP date function (used to get today)
$dayCodesMap = [
  0 => "sun", // Sunday
  1 => "mon", // Monday
  2 => "tue", // Tuesday
  3 => "wed", // Wednesday
  4 => "thu", // Thursday
  5 => "fri", // Friday
  6 => "sat", // Saturday
];

// Helper function to format time as HH:MM
function formatTime($timeString)
{
  if (empty($timeString)) {
    return "";
  }
  // Parse the time string and reformat it to HH:MM
  $time = strtotime($timeString);
  return date("H:i", $time);
}

// Helper function to calculate middle time of opening hours
function calculateMiddleTime($openTime, $closeTime)
{
  if (empty($openTime) || empty($closeTime)) {
    return "";
  }

  // Calculate pickup time as 1 minute before closing
  // This ensures the pickup time is always in the future (as long as current time < closing time)
  $closeTimestamp = strtotime($closeTime);
  $pickupTimestamp = strtotime("-1 minute", $closeTimestamp);

  return date("H:i", $pickupTimestamp);
}

// German day abbreviations
$dayAbbreviations = [
  "mon" => "Mo",
  "tue" => "Di",
  "wed" => "Mi",
  "thu" => "Do",
  "fri" => "Fr",
  "sat" => "Sa",
  "sun" => "So",
];

// German month abbreviations
$monthAbbreviations = [
  1 => "Jan",
  2 => "Feb",
  3 => "Mär",
  4 => "Apr",
  5 => "Mai",
  6 => "Jun",
  7 => "Jul",
  8 => "Aug",
  9 => "Sep",
  10 => "Okt",
  11 => "Nov",
  12 => "Dez",
];

// Helper function to format date as "Do, 5. Dez"
function formatGermanDate($timestamp, $dayAbbreviations, $monthAbbreviations)
{
  $dayOfWeek = date("w", $timestamp); // 0 = Sunday, 6 = Saturday
  $dayCodesMap = [
    0 => "sun",
    1 => "mon",
    2 => "tue",
    3 => "wed",
    4 => "thu",
    5 => "fri",
    6 => "sat",
  ];
  $dayKey = $dayCodesMap[$dayOfWeek];

  $dayAbbr = $dayAbbreviations[$dayKey];
  $dayNum = date("j", $timestamp);
  $monthNum = (int) date("n", $timestamp);
  $monthAbbr = $monthAbbreviations[$monthNum];

  return "{$dayAbbr}, {$dayNum}. {$monthAbbr}";
}

// Get today's day code
$todayDayCode = $dayCodesMap[date("w")];
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
          <?php if ($isHolidayToday): ?>
            <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-800 px-2 py-1 text-xs font-semibold">
              <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
              FEIERTAG
            </span>
          <?php elseif ($todayHours && $todayHours->opened()->bool()): ?>
            <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-800 px-2 py-1 text-xs font-semibold">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span>
              GEÖFFNET
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-800 px-2 py-1 text-xs font-semibold">
              <span class="w-2 h-2 bg-red-500 rounded-full"></span>
              GESCHLOSSEN
            </span>
          <?php endif; ?>
        </div>
        <span class="ml-2 text-lg font-mono font-semibold text-leihlokal-600">
          <?php if ($isHolidayToday): ?>
            <span class="text-amber-700"><?= $holidayDisplayMessage ?></span>
          <?php elseif ($todayHours && $todayHours->opened()->bool()): ?>
            <?= formatTime($todayHours->open_time()) ?> - <?= formatTime(
   $todayHours->close_time(),
 ) ?>
          <?php else: ?>
            —
          <?php endif; ?>
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
              <tr class="border-t border-gray-300 <?= $day->day()->value() ===
              $todayDayCode
                ? "bg-leihlokal-50"
                : "bg-white" ?> hover:bg-gray-50 transition-colors">
                <td class="py-3 px-4 font-medium <?= $day->day()->value() ===
                $todayDayCode
                  ? "text-leihlokal-600 font-bold"
                  : "" ?>">
                  <div class="flex items-center gap-2">
                    <?= $daysTranslation[$day->day()->value()] ?>
                    <?php if ($day->day()->value() === $todayDayCode): ?>
                      <span class="inline-block w-2 h-2 bg-leihlokal-500 rounded-full"></span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="py-3 px-4 font-mono <?= $day->day()->value() ===
                $todayDayCode
                  ? "font-bold text-leihlokal-600"
                  : "" ?>">
                  <?php if ($day->opened()->bool()): ?>
                    <?= formatTime($day->open_time()) ?> - <?= formatTime(
   $day->close_time(),
 ) ?>
                  <?php else: ?>
                    <span class="text-gray-400">Geschlossen</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Important Notice -->
<?php if ($site->importanttoggle()->bool()): ?>
<?php // Get notice settings
  // Get notice settings
  // Get notice settings
  // Get notice settings
  // Get notice settings
  // Get notice settings
  // Get notice settings
  // Get notice settings

$intensity = $site->notice_intensity()->value() ?: "info";
$iconType = $site->notice_icon()->value() ?: "info"; // Define styling based on intensity
$styles = [
  "info" => [
    "container" => "bg-blue-50 border-l-4 border-blue-400",
    "icon_color" => "text-blue-500",
    "text_color" => "text-blue-800",
    "text_size" => "text-sm",
    "icon_size" => "h-5 w-5",
    "animation" => "",
  ],
  "warning" => [
    "container" => "bg-yellow-50 border-l-4 border-yellow-500",
    "icon_color" => "text-yellow-600",
    "text_color" => "text-yellow-900",
    "text_size" => "text-base",
    "icon_size" => "h-6 w-6",
    "animation" => "",
  ],
  "urgent" => [
    "container" => "bg-red-100 border-l-4 border-leihlokal-500",
    "icon_color" => "text-leihlokal-500",
    "text_color" => "text-leihlokal-600",
    "text_size" => "text-base font-bold",
    "icon_size" => "h-7 w-7",
    "animation" => "notice-pulse",
  ],
];
$style = $styles[$intensity]; // Define icons
$icons = [
  "info" =>
    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />',
  "warning" =>
    '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />',
  "alert" =>
    '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />',
  "clock" =>
    '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />',
];
$icon = $icons[$iconType];
?>
<div class="<?= $style["container"] ?> p-4 my-2 <?= $style["animation"] ?>">
  <div class="container mx-auto">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 <?= $intensity === "urgent"
        ? "notice-bounce"
        : "" ?>">
        <svg class="<?= $style["icon_size"] ?> <?= $style[
   "icon_color"
 ] ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <?= $icon ?>
        </svg>
      </div>
      <div class="flex-1">
        <div class="<?= $style["text_color"] ?> <?= $style[
   "text_size"
 ] ?>"><?= $site->importantnotice()->kt() ?></div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

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

<?php // Helper function to generate 30-minute time slots


function generateTimeSlots($start, $end)
{
  $slots = [];
  $startTime = strtotime($start);
  $endTime = strtotime($end);
  while ($startTime < $endTime) {
    $slots[] = date("H:i", $startTime);
    $startTime = strtotime("+30 minutes", $startTime);
  }
  return $slots;
} // Get opening hours from Kirby and index by day code for lookup
$openingHours = $page->hours()->toStructure();
$hoursLookup = [];
foreach ($openingHours as $day) {
  $dayKey = $day->day()->value();
  $hoursLookup[$dayKey] = [
    "isOpen" => $day->opened()->bool(),
    "openTime" => $day->opened()->bool() ? formatTime($day->open_time()) : "",
    "closeTime" => $day->opened()->bool() ? formatTime($day->close_time()) : "",
    "middleTime" => $day->opened()->bool()
      ? calculateMiddleTime(
        $day->open_time()->value(),
        $day->close_time()->value(),
      )
      : "",
  ];
} // Set timezone to Berlin for accurate time comparisons
date_default_timezone_set("Europe/Berlin"); // Generate 8-day schedule: yesterday (-1) to +6 days
$daySchedule = [];
$today = time();
for ($i = -1; $i <= 6; $i++) {
  $timestamp = strtotime("+{$i} days", $today);
  $dayOfWeek = date("w", $timestamp); // 0 = Sunday, 6 = Saturday
  $dayCodesMap = [
    0 => "sun",
    1 => "mon",
    2 => "tue",
    3 => "wed",
    4 => "thu",
    5 => "fri",
    6 => "sat",
  ];
  $dayKey = $dayCodesMap[$dayOfWeek]; // Look up opening hours for this day
  $hours = $hoursLookup[$dayKey] ?? [
    "isOpen" => false,
    "openTime" => "",
    "closeTime" => "",
    "middleTime" => "",
  ];

  // Check if this day is a Baden-Württemberg holiday
  $holidayName = isBadenWuerttembergHoliday($timestamp);
  $isHoliday = $holidayName !== false;

  // If it's a holiday, override isOpen to false
  $effectivelyOpen = $hours["isOpen"] && !$isHoliday;

  // Check if today's closing time has passed
  $isPastClosing = false;
  if ($i === 0 && $effectivelyOpen && !empty($hours["closeTime"])) {
    // Parse closing time (format: "HH:MM")
    [$closeHour, $closeMinute] = explode(":", $hours["closeTime"]);
    $closingTimestamp = strtotime(
      date("Y-m-d") . " {$closeHour}:{$closeMinute}:00",
    );
    $isPastClosing = time() >= $closingTimestamp;
  }
  $daySchedule[] = [
    "date" => $timestamp,
    "dateFormatted" => formatGermanDate(
      $timestamp,
      $dayAbbreviations,
      $monthAbbreviations,
    ),
    "fullDayName" => $daysTranslation[$dayKey],
    "dayKey" => $dayKey,
    "isToday" => $i === 0,
    "isYesterday" => $i === -1,
    "isOpen" => $effectivelyOpen,
    "isHoliday" => $isHoliday,
    "holidayName" => $holidayName ?: "",
    "openTime" => $hours["openTime"],
    "closeTime" => $hours["closeTime"],
    "middleTime" => $hours["middleTime"],
    "dateISO" => date("Y-m-d", $timestamp),
    "isPastClosing" => $isPastClosing,
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

        <!-- Subpages and Files -->
        <?php
        $realSubpages = $page
          ->children()
          ->filterBy("intendedTemplate", "!=", "item");
        $pageFiles = $page->files();
        if ($realSubpages->isNotEmpty() || $pageFiles->isNotEmpty()): ?>
        <div class="border border-black">
            <div class="bg-leihlokal-500 text-white p-4">Mehr zum leih.lokal</div>
            <div class="p-4 space-y-2">
                <?php foreach ($realSubpages as $subpage): ?>
                <a href="<?= $subpage->url() ?>" class="block p-2 hover:bg-gray-100 transition-colors">
                    <?= $subpage->title() ?>
                </a>
                <?php endforeach; ?>
                <?php foreach ($pageFiles as $file): ?>
                <a href="<?= $file->url() ?>" download class="block p-2 hover:bg-gray-100 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-leihlokal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span><?= $file->title()->isNotEmpty()
                      ? $file->title()
                      : $file->filename() ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif;
        ?>
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
<div id="reservationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">

        <!-- Header -->
        <div class="sticky top-0 bg-leihlokal-500 text-white p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold">Vorbestellung abschließen</h2>
            <button id="closeReservationModal" class="text-white hover:text-gray-200 text-3xl font-bold leading-none">×</button>
        </div>

        <!-- Reservation Form (initial state) -->
        <div id="reservationForm" class="p-6">

            <!-- Cart Summary -->
            <div class="mb-6 p-4 bg-gray-50 border-2 border-gray-200">
                <h3 class="font-bold text-lg mb-3">Deine Auswahl:</h3>
                <div id="modalCartItems" class="space-y-2 mb-4">
                    <!-- Cart items rendered here -->
                </div>
                <div class="border-t-2 border-gray-300 pt-3 flex justify-between items-center">
                    <span class="font-bold">Gesamtpfand:</span>
                    <span id="modalTotalDeposit" class="text-xl font-bold text-leihlokal-600">0,00 €</span>
                </div>
            </div>

            <form id="quickReservationForm">
                <!-- Email Input -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Email-Adresse
                    </label>
                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="deine@email.de"
                        class="w-full p-4 text-lg border-2 border-leihlokal-500 focus:outline-none focus:border-leihlokal-600 transition-all"
                    >
                </div>

                <!-- Day Selector -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Wann möchtest du abholen?
                    </label>
                    <div class="space-y-2" id="daySelector">
                        <?php foreach ($daySchedule as $day): ?>
                            <?php if (
                              $day["isOpen"] &&
                              !$day["isYesterday"] &&
                              !$day["isPastClosing"]
                            ): ?>
                            <button
                                type="button"
                                class="day-option w-full group p-4 border-2 transition-all text-left
                                       border-leihlokal-300 hover:border-leihlokal-500 hover:bg-leihlokal-50"
                                data-date-iso="<?= $day["dateISO"] ?>"
                                data-middle-time="<?= $day["middleTime"] ?>"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-lg font-bold text-leihlokal-600 group-hover:text-leihlokal-700">
                                                <?= $day["dateFormatted"] ?>
                                            </span>
                                            <span class="text-base text-gray-700 group-hover:text-leihlokal-600">
                                                <?= $day["fullDayName"] ?>
                                            </span>
                                            <?php if ($day["isToday"]): ?>
                                            <span class="inline-flex items-center gap-1 bg-leihlokal-500 text-white px-2 py-1 text-xs font-bold">
                                                <span class="w-2 h-2 bg-white rounded-full"></span>
                                                HEUTE
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm font-mono text-gray-600 group-hover:text-leihlokal-600">
                                            <?= $day["openTime"] ?> - <?= $day[
   "closeTime"
 ] ?>
                                        </div>
                                    </div>
                                    <div class="day-selected-indicator hidden">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitReservationBtn"
                    class="w-full bg-leihlokal-500 text-white p-4 text-lg font-bold hover:bg-leihlokal-600 transition-colors"
                >
                    Jetzt reservieren →
                </button>
            </form>
        </div>

        <!-- Success Display (hidden initially) -->
        <div id="successDisplay" class="hidden p-4">

            <!-- Success Icon -->
            <div class="text-center mb-4">
                <div class="inline-block p-3 bg-leihlokal-500 mb-2 shadow-xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-leihlokal-600 mb-1">Geschafft!</h3>
                <p class="text-lg text-gray-600">Deine Reservierung wurde bestätigt</p>
            </div>

            <!-- OTP Display -->
            <div class="mb-4">
                <div class="bg-leihlokal-500 border-4 border-black p-4 mx-auto max-w-md shadow-xl">
                    <p class="text-white text-sm font-bold mb-2">🎫 Dein Abholcode:</p>
                    <div class="bg-white p-4 mb-2 shadow-inner">
                        <p id="otpNumber" class="text-4xl font-bold font-mono text-leihlokal-600 tracking-wider text-center">
                            ----
                        </p>
                    </div>
                    <p class="text-white text-sm">
                        Zeige diesen Code beim Abholen vor
                    </p>
                </div>
            </div>

            <!-- Reservation Summary -->
            <div class="bg-gray-50 border-2 border-gray-200 p-4 mb-4">
                <h4 class="font-bold text-base mb-3">Zusammenfassung</h4>
                <div id="reservationSummary" class="space-y-2">
                    <!-- Summary details rendered here -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button id="addToCalendarBtn" class="flex-1 bg-white border-2 border-leihlokal-500 text-leihlokal-600 p-3 font-bold hover:bg-leihlokal-50 transition-colors">
                    📅 Zum Kalender
                </button>
                <button id="shareReservationBtn" class="flex-1 bg-white border-2 border-leihlokal-500 text-leihlokal-600 p-3 font-bold hover:bg-leihlokal-50 transition-colors">
                    🔗 Teilen
                </button>
            </div>

            <!-- Close Button -->
            <button id="closeSuccessBtn" class="w-full mt-3 bg-gray-200 text-gray-700 p-3 font-bold hover:bg-gray-300 transition-colors">
                Fertig
            </button>
        </div>

    </div>
</div>
<!-- Custom Styles for Reservation Flow -->
<style>
/* Smooth transitions for modal and forms */
.transition-all {
    transition: all 0.3s ease;
}

/* Success icon bounce animation */
@keyframes bounce-once {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.animate-bounce-once {
    animation: bounce-once 0.6s ease-out;
}

/* Time slot button selected state */
.time-slot-button.bg-leihlokal-500 {
    background-color: #ff0000;
    color: white;
    border-color: #cc0000;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Day selector button selected state */
.day-selector-button[data-selected="true"] {
    background-color: #ff0000;
    border-color: #cc0000;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.day-selector-button[data-selected="true"] .text-gray-800,
.day-selector-button[data-selected="true"] .text-gray-600 {
    color: white !important;
}

.day-selector-button[data-selected="true"] .selected-indicator {
    display: block;
}

.day-selector-button:disabled {
    transform: none !important;
}
</style>

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

    window.daySchedule = <?= json_encode($daySchedule) ?>;

</script>

<!-- Module Script -->
<script type="module" src="<?= url("assets/js/leihlokal-ui.js") ?>"></script>

<?php snippet("footer"); ?>
