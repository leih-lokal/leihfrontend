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
      <div class="ll-sidebar-header">Korb <span class="ll-sidebar-count" id="sidebarCartCount">0</span></div>
      <div id="sidebarCartItems">
        <div class="ll-sidebar-cart-empty">Noch nichts drin.</div>
      </div>
      <div class="ll-sidebar-actions" id="sidebarCartActions" style="display:none;">
        <button id="sidebarReserve" class="ll-btn ll-btn-primary" style="width:100%;padding:0.5rem;">Vorbestellen →</button>
        <button id="sidebarClearCart" class="ll-btn ll-btn-close" style="width:100%;padding:0.5rem;">Alles zurücklegen</button>
      </div>
    </div>

    <!-- Categories -->
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header">Rubriken <span class="ll-sidebar-count">7</span></div>
      <div class="ll-sidebar-item category-filter" data-category="" data-active="true">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Alle Sachen</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Freizeit" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>Freizeit</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Garten" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>Garten</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Haushalt" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Haushalt</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Heimwerken" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>Heimwerken</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Kinder" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5"/><path d="M19.5 10c.3 0 .5.1.7.3.2.2.3.4.3.7 0 3.1-1.1 5.8-2.8 7.7C16 20.6 13.8 22 12 22s-4-1.4-5.7-3.3C4.6 16.8 3.5 14.1 3.5 11c0-.3.1-.5.3-.7.2-.2.4-.3.7-.3"/><path d="M8 2c1 .5 2 2 2 5"/><path d="M16 2c-1 .5-2 2-2 5"/></svg>Kinder</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
      <div class="ll-sidebar-item category-filter" data-category="Küche" data-active="false">
        <span class="ll-sidebar-item-label"><svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>Küche</span>
        <svg class="ll-sidebar-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
    </div>

    <!-- Filters -->
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header">Filter</div>
      <label class="ll-sidebar-item ll-sidebar-toggle-item">
        <span class="ll-sidebar-item-label">
          <svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Nur ausleihbar
        </span>
        <span class="ll-toggle-box active" id="availableToggleBox"></span>
        <input type="checkbox" id="availableToggle" checked style="display:none;">
      </label>
      <label class="ll-sidebar-item ll-sidebar-toggle-item">
        <span class="ll-sidebar-item-label">
          <svg class="ll-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="m21 8-4-4-4 4"/><path d="M17 4v16"/></svg>
          Sortieren
        </span>
        <span class="ll-toggle-box" id="sortToggleBox"></span>
        <input type="checkbox" id="sortToggle" style="display:none;">
      </label>
    </div>

    <!-- Subpages and Files -->
    <?php
    $realSubpages = $page->children()->filterBy("intendedTemplate", "!=", "item");
    $pageFiles = $page->files();
    if ($realSubpages->isNotEmpty() || $pageFiles->isNotEmpty()): ?>
    <div class="ll-sidebar-section">
      <div class="ll-sidebar-header">Mehr <span class="ll-sidebar-count"><?= $realSubpages->count() + $pageFiles->count() ?></span></div>
      <?php foreach ($realSubpages as $subpage): ?>
        <a href="<?= $subpage->url() ?>" class="ll-sidebar-link">
          <svg class="ll-sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <?= $subpage->title() ?>
        </a>
      <?php endforeach; ?>
      <?php foreach ($pageFiles as $file): ?>
        <a href="<?= $file->url() ?>" download class="ll-sidebar-link">
          <svg class="ll-sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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

    <!-- Filter toggles (mobile only, desktop version is in sidebar) -->
    <div class="ll-toggles ll-toggles-mobile">
      <label class="ll-toggle">
        <span class="ll-toggle-box active" id="availableToggleBoxMobile"></span>
        <input type="checkbox" id="availableToggleMobile" checked style="display:none;">
        Nur ausleihbar
      </label>
      <label class="ll-toggle">
        <span class="ll-toggle-box" id="sortToggleBoxMobile"></span>
        <input type="checkbox" id="sortToggleMobile" style="display:none;">
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
  <div style="padding:0;flex:1;display:flex;flex-direction:column;">
    <!-- Cart summary -->
    <div style="padding:0.75rem;border-bottom:var(--ll-border);">
      <div id="panelCartItems"></div>
      <div id="panelTotalDeposit" style="font-family:'Univers',sans-serif;font-weight:700;font-size:0.85rem;padding-top:0.5rem;margin-top:0.5rem;border-top:var(--ll-border);"></div>
    </div>

    <!-- Reservation form -->
    <form id="reservationForm" style="flex:1;display:flex;flex-direction:column;">
      <div style="padding:0.75rem;border-bottom:var(--ll-border);">
        <label style="display:flex;align-items:center;gap:0.4rem;margin-bottom:0.4rem;">
          <svg style="width:14px;height:14px;color:var(--ll-color);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <span class="ll-section-header">Email-Adresse</span>
        </label>
        <input type="email" name="email" required placeholder="deine@email.de"
          style="width:100%;padding:0.6rem;border:2px solid var(--ll-color);font-family:'Univers',sans-serif;font-size:0.85rem;box-sizing:border-box;">
      </div>

      <div style="padding:0.75rem;flex:1;">
        <label style="display:flex;align-items:center;gap:0.4rem;margin-bottom:0.5rem;">
          <svg style="width:14px;height:14px;color:var(--ll-color);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span class="ll-section-header">Abholtermin</span>
        </label>
        <div id="daySelector" style="display:flex;flex-direction:column;gap:0.35rem;">
          <?php foreach ($daySchedule as $day): ?>
            <?php if ($day["isOpen"] && !$day["isYesterday"] && !$day["isPastClosing"]): ?>
            <button type="button" class="day-option" style="width:100%;text-align:left;padding:0.6rem 0.75rem;border:2px solid oklch(60.62% 0.245 28.83 / 0.15);background:white;color:var(--ll-black);display:flex;justify-content:space-between;align-items:center;min-height:44px;cursor:pointer;font-family:'Univers',sans-serif;font-size:0.8rem;transition:border-color 0.15s,background 0.15s;"
              data-date-iso="<?= $day["dateISO"] ?>"
              data-middle-time="<?= $day["middleTime"] ?>">
              <span style="display:flex;align-items:center;gap:0.4rem;">
                <svg style="width:14px;height:14px;color:oklch(50% 0 0);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <strong><?= $day["dateFormatted"] ?></strong>
                <span style="color:oklch(45% 0 0);"><?= $day["fullDayName"] ?></span>
                <?php if ($day["isToday"]): ?>
                  <span style="background:var(--ll-color);color:white;font-size:0.5rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;padding:0.1rem 0.3rem;">Heute</span>
                <?php endif; ?>
              </span>
              <span style="font-family:monospace;font-size:0.7rem;color:oklch(45% 0 0);"><?= $day["openTime"] ?>–<?= $day["closeTime"] ?></span>
            </button>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="padding:0.75rem;border-top:var(--ll-border);">
        <button type="submit" id="submitReservationBtn" class="ll-detail-cart-btn" style="width:100%;flex-direction:row;padding:0.75rem;font-size:0.8rem;gap:0.5rem;">
          <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          <span>Jetzt reservieren</span>
        </button>
      </div>
    </form>

    <!-- Success state (hidden initially) -->
    <div id="successDisplay" style="display:none;">

      <!-- Success header -->
      <div style="text-align:center;padding:1.5rem 1rem 1rem;">
        <div class="ll-success-icon" style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:var(--ll-color);margin-bottom:0.75rem;">
          <svg style="width:32px;height:32px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3 style="font-family:'Univers',sans-serif;font-size:1.25rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 0.2rem;">Geschafft!</h3>
        <p style="color:oklch(50% 0 0);margin:0;font-size:0.8rem;">Deine Reservierung wurde bestätigt</p>
      </div>

      <!-- OTP Code -->
      <div style="margin:0 0.75rem;border:2px solid var(--ll-color);">
        <div style="background:var(--ll-color);padding:0.4rem 0.6rem;">
          <span style="color:white;font-family:'Univers',sans-serif;font-size:0.55rem;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;">Dein Abholcode</span>
        </div>
        <div style="padding:0.75rem;text-align:center;background:white;">
          <p id="otpNumber" style="font-family:monospace;font-size:2rem;font-weight:700;letter-spacing:0.2em;color:var(--ll-color);margin:0;">----</p>
        </div>
      </div>

      <!-- Summary -->
      <div id="reservationSummary" style="margin:0.75rem;font-size:0.8rem;"></div>

      <!-- Action buttons -->
      <div style="padding:0 0.75rem 0.75rem;display:flex;flex-direction:column;gap:0.4rem;">
        <div style="display:flex;gap:0.4rem;">
          <button id="addToCalendarBtn" class="ll-success-action-btn" style="flex:1;">
            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Zum Kalender</span>
          </button>
          <button id="shareReservationBtn" class="ll-success-action-btn" style="flex:1;">
            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            <span>Teilen</span>
          </button>
        </div>
        <button id="closeSuccessBtn" class="ll-success-done-btn">
          <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Fertig</span>
        </button>
      </div>
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
