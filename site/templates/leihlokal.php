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

<!-- Hours Band -->
<div class="ll-band">
  <div class="ll-hours" id="toggleHours">
    <span class="ll-hours-label">Heute — <?= $todayName ?></span>
    <?php if ($isHolidayToday): ?>
      <span class="ll-status-badge" style="background:#dc2626;color:white;">Geschlossen</span>
      <span class="ll-status-badge" style="background:#d97706;color:white;">Feiertag</span>
      <span class="ll-hours-time" style="color:#d97706;"><?= $holidayDisplayMessage ?></span>
    <?php elseif ($todayHours && $todayHours->opened()->bool()): ?>
      <span class="ll-status-badge" style="background:#16a34a;color:white;">Geöffnet</span>
      <span class="ll-hours-time"><?= formatTime($todayHours->open_time()) ?> — <?= formatTime($todayHours->close_time()) ?></span>
    <?php else: ?>
      <span class="ll-status-badge" style="background:#dc2626;color:white;">Geschlossen</span>
      <span class="ll-hours-time">—</span>
    <?php endif; ?>
    <svg id="hoursChevron" style="width:16px;height:16px;margin-left:auto;transition:transform 0.2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </div>

  <!-- Full Hours Dropdown -->
  <div id="fullHours" class="ll-band" style="display:none;">
    <table class="ll-hours-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Öffnungszeiten</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($page->hours()->toStructure() as $day): ?>
          <tr class="<?= $day->day()->value() === $todayDayCode ? 'll-hours-today' : '' ?>">
            <td>
              <?= $daysTranslation[$day->day()->value()] ?>
              <?php if ($day->day()->value() === $todayDayCode): ?>
                <span style="display:inline-block;width:6px;height:6px;background:var(--ll-color);margin-left:4px;vertical-align:middle;"></span>
              <?php endif; ?>
            </td>
            <td style="font-family:monospace;">
              <?php if ($day->opened()->bool()): ?>
                <?= formatTime($day->open_time()) ?> — <?= formatTime($day->close_time()) ?>
              <?php else: ?>
                <span style="color:oklch(60% 0 0);">Geschlossen</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Notice Band -->
<?php if ($site->importanttoggle()->bool()):
  $intensity = $site->notice_intensity()->value() ?: "info";
  $intensityClass = "ll-notice--" . $intensity;
?>
<div class="ll-band ll-notice <?= $intensityClass ?>">
  <div class="ll-section-header" style="color:var(--ll-color);"><?= $site->importantnotice()->kt() ?></div>
</div>
<?php endif; ?>

<!-- Catalog Body -->
<div class="ll-catalog">

  <!-- Sidebar (desktop only, rendered in HTML, hidden via CSS on mobile) -->
  <div class="ll-sidebar">
    <!-- Cart -->
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header ll-section-header">Korb</div>
      <div id="sidebarCartItems" style="padding:0.5rem 0.8rem;">
        <span style="color:oklch(60% 0 0);font-size:0.75rem;">Noch nichts drin.</span>
      </div>
      <div style="padding:0.5rem 0.8rem;">
        <button id="sidebarReserve" class="ll-btn ll-btn-primary" style="width:100%;display:none;">Vorbestellen →</button>
        <button id="sidebarClearCart" class="ll-btn ll-btn-close" style="width:100%;margin-top:0.4rem;display:none;">Alles zurücklegen</button>
      </div>
    </div>

    <!-- Categories -->
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header ll-section-header">Rubriken</div>
      <div class="ll-sidebar-item category-filter" data-category="" data-active="true">Alle Sachen</div>
      <div class="ll-sidebar-item category-filter" data-category="Freizeit" data-active="false">Freizeit</div>
      <div class="ll-sidebar-item category-filter" data-category="Garten" data-active="false">Garten</div>
      <div class="ll-sidebar-item category-filter" data-category="Haushalt" data-active="false">Haushalt</div>
      <div class="ll-sidebar-item category-filter" data-category="Heimwerken" data-active="false">Heimwerken</div>
      <div class="ll-sidebar-item category-filter" data-category="Kinder" data-active="false">Kinder</div>
      <div class="ll-sidebar-item category-filter" data-category="Küche" data-active="false">Küche</div>
    </div>

    <!-- Subpages and Files -->
    <?php
    $realSubpages = $page->children()->filterBy("intendedTemplate", "!=", "item");
    $pageFiles = $page->files();
    if ($realSubpages->isNotEmpty() || $pageFiles->isNotEmpty()): ?>
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header ll-section-header">Mehr zum leih.lokal</div>
      <?php foreach ($realSubpages as $subpage): ?>
        <a href="<?= $subpage->url() ?>" class="ll-sidebar-item" style="display:block;text-decoration:none;color:inherit;"><?= $subpage->title() ?></a>
      <?php endforeach; ?>
      <?php foreach ($pageFiles as $file): ?>
        <a href="<?= $file->url() ?>" download class="ll-sidebar-item" style="display:block;text-decoration:none;color:inherit;">
          <?= $file->title()->isNotEmpty() ? $file->title() : $file->filename() ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Main content area -->
  <div class="ll-main">
    <!-- Search -->
    <div class="ll-search">
      <input type="text" id="searchInput" placeholder="Durchsuchen...">
    </div>

    <!-- Category chips (mobile only, hidden on desktop via CSS) -->
    <div class="ll-chips">
      <div class="ll-chip category-filter" data-category="" data-active="true">Alle</div>
      <div class="ll-chip category-filter" data-category="Freizeit" data-active="false">Freizeit</div>
      <div class="ll-chip category-filter" data-category="Garten" data-active="false">Garten</div>
      <div class="ll-chip category-filter" data-category="Haushalt" data-active="false">Haushalt</div>
      <div class="ll-chip category-filter" data-category="Heimwerken" data-active="false">Heimwerken</div>
      <div class="ll-chip category-filter" data-category="Kinder" data-active="false">Kinder</div>
      <div class="ll-chip category-filter" data-category="Küche" data-active="false">Küche</div>
    </div>

    <!-- Filter toggles -->
    <div class="ll-toggles">
      <label class="ll-toggle">
        <span class="ll-toggle-box active" id="availableToggleBox"></span>
        <input type="checkbox" id="availableToggle" checked style="display:none;">
        Nur ausleihbar
      </label>
      <label class="ll-toggle">
        <span class="ll-toggle-box" id="sortToggleBox"></span>
        <input type="checkbox" id="sortToggle" style="display:none;">
        Sortieren
      </label>
    </div>

    <!-- Product Grid -->
    <div id="productGrid" class="ll-product-grid">
      <!-- Products rendered by JS -->
    </div>

    <!-- Pagination -->
    <div class="ll-pagination">
      <button id="loadMore" class="ll-btn ll-load-more">Mehr laden</button>
      <div id="pageNumbers" class="ll-page-numbers"></div>
    </div>
  </div>
</div>

<!-- Sticky Cart Bar (mobile) -->
<div class="ll-cart-bar" id="cartBar">
  <span class="ll-cart-bar-count" id="cartBarCount">Korb (0)</span>
  <button class="ll-cart-bar-action" id="cartBarAction">Vorbestellen →</button>
</div>

<!-- Cart / Reservation Panel (mobile) -->
<div class="ll-cart-panel" id="cartPanel">
  <div class="ll-cart-panel-header">
    <span class="ll-section-header">Vorbestellung</span>
    <button id="closeCartPanel" class="ll-btn" style="background:none;color:white;font-size:1.5rem;line-height:1;">×</button>
  </div>
  <div style="padding:0.75rem;flex:1;">
    <!-- Cart summary -->
    <div id="panelCartItems" style="margin-bottom:1rem;"></div>
    <div id="panelTotalDeposit" style="font-weight:700;font-size:1rem;padding-top:0.5rem;border-top:var(--ll-border);margin-bottom:1rem;"></div>

    <!-- Reservation form -->
    <form id="reservationForm">
      <label class="ll-section-header" style="display:block;margin-bottom:0.5rem;">Email-Adresse</label>
      <input type="email" name="email" required placeholder="deine@email.de"
        style="width:100%;padding:0.75rem;border:1.5px solid var(--ll-black);font-size:1rem;margin-bottom:1rem;">

      <label class="ll-section-header" style="display:block;margin-bottom:0.5rem;">Abholtermin</label>
      <div id="daySelector" style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1rem;">
        <?php foreach ($daySchedule as $day): ?>
          <?php if ($day["isOpen"] && !$day["isYesterday"] && !$day["isPastClosing"]): ?>
          <button type="button" class="day-option ll-btn" style="width:100%;text-align:left;padding:0.75rem;border:1.5px solid var(--ll-color-20);background:white;color:var(--ll-black);display:flex;justify-content:space-between;align-items:center;min-height:48px;"
            data-date-iso="<?= $day["dateISO"] ?>"
            data-middle-time="<?= $day["middleTime"] ?>">
            <span>
              <strong><?= $day["dateFormatted"] ?></strong>
              <span style="margin-left:0.5rem;color:oklch(40% 0 0);"><?= $day["fullDayName"] ?></span>
              <?php if ($day["isToday"]): ?>
                <span class="ll-status-badge" style="background:var(--ll-color);color:white;margin-left:0.5rem;">Heute</span>
              <?php endif; ?>
            </span>
            <span style="font-family:monospace;font-size:0.75rem;color:oklch(40% 0 0);"><?= $day["openTime"] ?> — <?= $day["closeTime"] ?></span>
          </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <button type="submit" id="submitReservationBtn" class="ll-btn ll-btn-primary" style="width:100%;padding:0.75rem;font-size:0.85rem;">
        Jetzt reservieren →
      </button>
    </form>

    <!-- Success state (hidden initially) -->
    <div id="successDisplay" style="display:none;text-align:center;padding:1rem 0;">
      <div class="ll-success-icon" style="display:inline-block;background:var(--ll-color);padding:0.75rem;margin-bottom:1rem;">
        <svg style="width:40px;height:40px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h3 style="font-family:'Univers',sans-serif;font-size:1.5rem;font-weight:700;text-transform:uppercase;margin:0 0 0.25rem;">Geschafft!</h3>
      <p style="color:oklch(40% 0 0);margin:0 0 1rem;">Deine Reservierung wurde bestätigt</p>
      <div style="background:var(--ll-color);padding:1rem;margin-bottom:1rem;">
        <p style="color:white;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin:0 0 0.5rem;">Dein Abholcode:</p>
        <div style="background:white;padding:0.75rem;">
          <p id="otpNumber" class="ll-success-code">----</p>
        </div>
      </div>
      <div id="reservationSummary" style="text-align:left;border:var(--ll-border);padding:0.75rem;margin-bottom:1rem;font-size:0.85rem;"></div>
      <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
        <button id="addToCalendarBtn" class="ll-btn ll-btn-close" style="flex:1;padding:0.6rem;">Zum Kalender</button>
        <button id="shareReservationBtn" class="ll-btn ll-btn-close" style="flex:1;padding:0.6rem;">Teilen</button>
      </div>
      <button id="closeSuccessBtn" class="ll-btn" style="width:100%;padding:0.6rem;background:oklch(90% 0 0);color:var(--ll-black);">Fertig</button>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('toggleHours').addEventListener('click', function() {
    var fullHours = document.getElementById('fullHours');
    var chevron = document.getElementById('hoursChevron');
    fullHours.style.display = fullHours.style.display === 'none' ? '' : 'none';
    chevron.style.transform = fullHours.style.display === 'none' ? '' : 'rotate(180deg)';
  });
});

window.daySchedule = <?= json_encode($daySchedule) ?>;
</script>

<script type="module" src="<?= url("assets/js/leihlokal-ui.js") ?>"></script>

<?php snippet("footer"); ?>
