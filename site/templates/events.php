<?php snippet("header"); ?>

<main class="container mx-auto px-4 py-12">
  <!-- Page Header -->
  <div class="mb-12 text-center">
    <h1 class="text-4xl font-bold text-leihlokal-500 mb-4"><?= $page->headline()->or('Veranstaltungen') ?></h1>
    <?php if($page->intro()->isNotEmpty()): ?>
      <div class="max-w-2xl mx-auto text-lg text-gray-600">
        <?= $page->intro()->kt() ?>
      </div>
    <?php endif ?>
  </div>

  <?php if($page->coverimage()->isNotEmpty()): ?>
    <div class="mb-12">
      <?php if($image = $page->coverimage()->toFile()): ?>
        <img src="<?= $image->url() ?>" alt="<?= $page->headline() ?>" class="w-full h-64 object-cover rounded-lg">
      <?php endif ?>
    </div>
  <?php endif ?>

  <!-- Events Section -->
  <div class="flex flex-col lg:flex-row lg:gap-8" id="events-container">
    <!-- Events List (Left Side) -->
    <div class="w-full lg:w-full transition-all duration-300" id="events-list-container">
      <?php
      // Function to unescape iCal text
      function unescapeIcalText($text) {
        // Unescape iCal special characters
        $text = str_replace('\\n', "\n", $text);
        $text = str_replace('\\N', "\n", $text);
        $text = str_replace('\\,', ',', $text);
        $text = str_replace('\\;', ';', $text);
        $text = str_replace('\\\\', '\\', $text);
        return $text;
      }

      // Function to extract iCal field (handles multi-line)
      function extractIcalField($fieldName, $content) {
        // Match field and capture everything until the next field starts
        // Pattern: field name, colon, value, and continuation lines (start with space/tab)
        if (preg_match('/' . $fieldName . ':(.*)$/m', $content, $match)) {
          $lines = explode("\n", $content);
          $value = '';
          $capturing = false;

          foreach ($lines as $line) {
            // Start capturing when we find the field
            if (preg_match('/^' . $fieldName . ':(.*)$/', $line, $m)) {
              $value = $m[1];
              $capturing = true;
              continue;
            }

            // If capturing and line starts with space/tab, it's a continuation
            if ($capturing && preg_match('/^[ \t](.*)$/', $line, $m)) {
              $value .= $m[1];
            }
            // If capturing and line doesn't start with space/tab, we're done
            elseif ($capturing) {
              break;
            }
          }

          return $value ? unescapeIcalText(trim($value)) : null;
        }
        return null;
      }

      // Function to parse iCal file and extract events
      function parseIcal($url) {
        $events = [];

        try {
          // Fetch the iCal file
          $icalContent = file_get_contents($url);

          if ($icalContent === false) {
            return [];
          }

          // Normalize line endings
          $icalContent = str_replace(["\r\n", "\r"], "\n", $icalContent);

          // Split into events
          preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $icalContent, $matches);

          foreach ($matches[1] as $eventBlock) {
            $event = [];

            // Extract UID
            $event['uid'] = extractIcalField('UID', $eventBlock);

            // Extract SUMMARY (title)
            $event['title'] = extractIcalField('SUMMARY', $eventBlock);

            // Extract DESCRIPTION
            $event['description'] = extractIcalField('DESCRIPTION', $eventBlock);

            // Extract LOCATION
            $event['location'] = extractIcalField('LOCATION', $eventBlock);

            // Extract DTSTART
            if (preg_match('/DTSTART[;:]([^\r\n]+)/m', $eventBlock, $dtstart)) {
              $dateStr = trim($dtstart[1]);
              // Remove VALUE=DATE: prefix if present
              $dateStr = preg_replace('/^[^:]*:/', '', $dateStr);
              $event['date_start'] = parseIcalDate($dateStr);
            }

            // Extract DTEND
            if (preg_match('/DTEND[;:]([^\r\n]+)/m', $eventBlock, $dtend)) {
              $dateStr = trim($dtend[1]);
              // Remove VALUE=DATE: prefix if present
              $dateStr = preg_replace('/^[^:]*:/', '', $dateStr);
              $event['date_end'] = parseIcalDate($dateStr);
            }

            // Extract URL for registration
            $url = extractIcalField('URL', $eventBlock);
            if ($url) {
              $event['registration_link'] = $url;
              $event['registration_required'] = true;
            } else {
              $event['registration_required'] = false;
            }

            $event['featured'] = false;
            $event['source'] = 'ical';

            if (!empty($event['title']) && !empty($event['date_start'])) {
              $events[] = $event;
            }
          }
        } catch (Exception $e) {
          // Silently fail and return empty array
          error_log('iCal parsing error: ' . $e->getMessage());
        }

        return $events;
      }

      // Function to parse iCal date format
      function parseIcalDate($dateStr) {
        // Handle different iCal date formats
        // Format: 20240101T120000Z or 20240101
        $dateStr = str_replace(['T', 'Z'], ['', ''], $dateStr);

        if (strlen($dateStr) == 8) {
          // Date only: YYYYMMDD
          return strtotime(substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2));
        } elseif (strlen($dateStr) >= 14) {
          // Date and time: YYYYMMDDHHMMSS
          return strtotime(
            substr($dateStr, 0, 4) . '-' .
            substr($dateStr, 4, 2) . '-' .
            substr($dateStr, 6, 2) . ' ' .
            substr($dateStr, 8, 2) . ':' .
            substr($dateStr, 10, 2) . ':' .
            substr($dateStr, 12, 2)
          );
        }

        return strtotime($dateStr);
      }

      // Get events from Kirby structure
      $kirbyEvents = $site->events()->toStructure();

      // Get events from iCal if URL is provided
      $icalEvents = [];
      if ($page->ical_url()->isNotEmpty()) {
        $icalEvents = parseIcal($page->ical_url()->value());
      }

      // Merge events: convert Kirby events to array format
      $allEventsArray = [];

      foreach ($kirbyEvents as $event) {
        $allEventsArray[] = [
          'title' => $event->title()->value(),
          'date_start' => $event->date_start()->toDate(),
          'date_end' => $event->date_end()->isNotEmpty() ? $event->date_end()->toDate() : null,
          'location' => $event->location()->value(),
          'address' => $event->address()->value(),
          'description' => $event->description()->kt()->value(),
          'registration_required' => $event->registration_required()->toBool(),
          'registration_link' => $event->registration_link()->value(),
          'featured' => $event->featured()->toBool(),
          'source' => 'kirby'
        ];
      }

      // Add iCal events
      foreach ($icalEvents as $icalEvent) {
        $allEventsArray[] = [
          'title' => $icalEvent['title'] ?? '',
          'date_start' => $icalEvent['date_start'] ?? time(),
          'date_end' => $icalEvent['date_end'] ?? null,
          'location' => $icalEvent['location'] ?? '',
          'address' => '',
          'description' => $icalEvent['description'] ?? '',
          'registration_required' => $icalEvent['registration_required'] ?? false,
          'registration_link' => $icalEvent['registration_link'] ?? '',
          'featured' => $icalEvent['featured'] ?? false,
          'source' => 'ical'
        ];
      }

      // Sort all events by date
      usort($allEventsArray, function($a, $b) {
        return $a['date_start'] - $b['date_start'];
      });

      $featuredEvents = array_filter($allEventsArray, function($event) {
        return $event['featured'] === true;
      });
      $regularEvents = array_filter($allEventsArray, function($event) {
        return $event['featured'] !== true;
      });

      // Get current timestamp for comparison
      $now = time();

      // Separate upcoming and past events
      $upcomingEvents = array_filter($allEventsArray, function($event) use($now) {
          return $event['date_start'] >= $now;
      });
      // Sort upcoming events ascending
      usort($upcomingEvents, function($a, $b) {
        return $a['date_start'] - $b['date_start'];
      });

      $pastEvents = array_filter($allEventsArray, function($event) use($now) {
          return $event['date_start'] < $now;
      });
      // Sort past events descending (most recent first)
      usort($pastEvents, function($a, $b) {
        return $b['date_start'] - $a['date_start'];
      });

      // Featured events that are upcoming
      $upcomingFeaturedEvents = array_filter($upcomingEvents, function($event) {
          return $event['featured'] === true;
      });

      if(count($allEventsArray) > 0): 
      ?>
        <?php if(count($upcomingFeaturedEvents) > 0): ?>
          <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Hervorgehobene Veranstaltungen</h2>
            <div class="space-y-4">
              <?php foreach($upcomingFeaturedEvents as $event): ?>
              <?php
                $date = $event['date_start'];
                $day = date('d', $date);
                $month = date('M', $date);
                $time = date('H:i', $date);
              ?>
              <div class="event-item bg-white p-4 cursor-pointer transition-all border border-leihlokal-500 selected:border-2"
                   data-event='<?= json_encode([
                     'title' => $event['title'],
                     'date_start' => date('d.m.Y H:i', $event['date_start']),
                     'date_end' => $event['date_end'] ? date('d.m.Y H:i', $event['date_end']) : null,
                     'location' => $event['location'],
                     'address' => $event['address'],
                     'description' => $event['description'],
                     'registration_required' => $event['registration_required'],
                     'registration_link' => $event['registration_link']
                   ]) ?>'>
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0 text-center min-w-16 bg-gray-50 rounded-lg p-2 shadow-sm border border-gray-100">
                    <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                    <div class="text-3xl font-bold text-leihlokal-500 leading-none my-1"><?= $day ?></div>
                    <div class="text-xs font-medium text-gray-500 bg-white rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
                  </div>
                  <div class="flex-grow">
                    <div class="flex justify-between items-center">
                      <h3 class="text-xl font-semibold"><?= $event['title'] ?></h3>
                      <span class="bg-leihlokal-500 text-white px-2 py-1 text-xs rounded-full">Featured</span>
                    </div>
                    <?php if(!empty($event['location'])): ?>
                      <p class="text-sm text-gray-600 mt-1"><?= $event['location'] ?></p>
                    <?php endif ?>
                  </div>
                </div>
              </div>
              <?php endforeach ?>
            </div>
          </div>
        <?php endif ?>
        
        <?php if(count($upcomingEvents) > 0): ?>
        <h2 class="text-2xl font-bold mb-4">Kommende Veranstaltungen</h2>
        <div class="space-y-4" id="upcoming-events">
          <?php foreach($upcomingEvents as $event): ?>
          <?php
            $date = $event['date_start'];
            $day = date('d', $date);
            $month = date('M', $date);
            $time = date('H:i', $date);
          ?>
          <div class="event-item bg-white p-4 cursor-pointer transition-all border border-leihlokal-500 selected:border-2"
               data-event='<?= json_encode([
                 'title' => $event['title'],
                 'date_start' => date('d.m.Y H:i', $event['date_start']),
                 'date_end' => $event['date_end'] ? date('d.m.Y H:i', $event['date_end']) : null,
                 'location' => $event['location'],
                 'address' => $event['address'],
                 'description' => $event['description'],
                 'registration_required' => $event['registration_required'],
                 'registration_link' => $event['registration_link']
               ]) ?>'>
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 text-center min-w-16 bg-gray-50 rounded-lg p-2 shadow-sm border border-gray-100">
                <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                <div class="text-3xl font-bold text-leihlokal-500 leading-none my-1"><?= $day ?></div>
                <div class="text-xs font-medium text-gray-500 bg-white rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
              </div>
              <div class="flex-grow">
                <h3 class="text-xl font-semibold"><?= $event['title'] ?></h3>
                <?php if(!empty($event['location'])): ?>
                  <p class="text-sm text-gray-600 mt-1"><?= $event['location'] ?></p>
                <?php endif ?>
              </div>
            </div>
          </div>
          <?php endforeach ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12 bg-gray-100 rounded-lg mb-8">
          <p class="text-gray-600">Aktuell sind keine kommenden Veranstaltungen geplant.</p>
        </div>
        <?php endif ?>
        
        <?php if(count($pastEvents) > 0): ?>
        <h2 class="text-2xl font-bold mb-4 mt-12">Vergangene Veranstaltungen</h2>
        <div class="space-y-4" id="past-events">
          <?php foreach($pastEvents as $event): ?>
          <?php
            $date = $event['date_start'];
            $day = date('d', $date);
            $month = date('M', $date);
            $time = date('H:i', $date);
          ?>
          <div class="event-item bg-gray-50 p-4 cursor-pointer transition-all border border-gray-300 selected:border-2 opacity-75"
               data-event='<?= json_encode([
                 'title' => $event['title'],
                 'date_start' => date('d.m.Y H:i', $event['date_start']),
                 'date_end' => $event['date_end'] ? date('d.m.Y H:i', $event['date_end']) : null,
                 'location' => $event['location'],
                 'address' => $event['address'],
                 'description' => $event['description'],
                 'registration_required' => $event['registration_required'],
                 'registration_link' => $event['registration_link']
               ]) ?>'>
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 text-center min-w-16 bg-gray-100 rounded-lg p-2 shadow-sm border border-gray-200">
                <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                <div class="text-3xl font-bold text-gray-500 leading-none my-1"><?= $day ?></div>
                <div class="text-xs font-medium text-gray-500 bg-gray-50 rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between items-center">
                  <h3 class="text-xl font-semibold text-gray-600"><?= $event['title'] ?></h3>
                  <span class="bg-gray-400 text-white px-2 py-1 text-xs rounded-full">Vergangen</span>
                </div>
                <?php if(!empty($event['location'])): ?>
                  <p class="text-sm text-gray-500 mt-1"><?= $event['location'] ?></p>
                <?php endif ?>
              </div>
            </div>
          </div>
          <?php endforeach ?>
        </div>
        <?php endif ?>
      <?php else: ?>
        <div class="text-center py-12 bg-gray-100 rounded-lg">
          <p class="text-gray-600">Aktuell sind keine Veranstaltungen geplant.</p>
        </div>
      <?php endif ?>
    </div>
    
    <!-- Event Details (Right Side) -->
    <div class="w-full lg:w-2/3 hidden transition-all duration-300" id="event-details-container">
      <div class="sticky top-8 bg-white border border-leihlokal-500 p-6" id="event-details">
        <button id="close-details" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <h2 class="text-3xl font-bold text-leihlokal-500 mb-4" id="detail-title"></h2>
        
        <div class="flex flex-wrap gap-4 mb-6">
          <div class="flex items-center text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span id="detail-date"></span>
          </div>
          
          <div class="flex items-center text-gray-600" id="detail-location-container">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span id="detail-location"></span>
          </div>
          
          <button id="download-ical" class="flex items-center text-leihlokal-500 hover:text-leihlokal-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>In den Kalender speichern</span>
          </button>
        </div>
        
        <div class="prose max-w-none mb-8" id="detail-description"></div>
        
        <div id="detail-registration" class="hidden">
          <a href="#" id="detail-registration-link" class="inline-block px-6 py-3 bg-leihlokal-500 hover:bg-leihlokal-600 text-white rounded-lg font-medium transition-colors duration-200">
            Zur Anmeldung
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Modal Overlay (only appears on mobile) -->
  <div id="mobile-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white border border-leihlokal-500 p-6 rounded-lg relative max-h-[90vh] overflow-y-auto">
      <button id="close-mobile-details" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      
      <h2 class="text-3xl font-bold text-leihlokal-500 mb-4" id="mobile-detail-title"></h2>
      
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex items-center text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span id="mobile-detail-date"></span>
        </div>
        
        <div class="flex items-center text-gray-600" id="mobile-detail-location-container">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span id="mobile-detail-location"></span>
        </div>
        
        <button id="mobile-download-ical" class="flex items-center text-leihlokal-500 hover:text-leihlokal-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span>Als iCal speichern</span>
        </button>
      </div>
      
      <div class="prose max-w-none mb-8" id="mobile-detail-description"></div>
      
      <div id="mobile-detail-registration" class="hidden">
        <a href="#" id="mobile-detail-registration-link" class="inline-block px-6 py-3 bg-leihlokal-500 hover:bg-leihlokal-600 text-white rounded-lg font-medium transition-colors duration-200">
          Zur Anmeldung
        </a>
      </div>
    </div>
  </div>

  <!-- Hidden form for iCal generation -->
  <form id="ical-form" method="post" action="<?= $page->url() ?>" class="hidden">
    <input type="hidden" name="ical" value="true">
    <input type="hidden" name="title" id="ical-title">
    <input type="hidden" name="description" id="ical-description">
    <input type="hidden" name="location" id="ical-location">
    <input type="hidden" name="start_date" id="ical-start-date">
    <input type="hidden" name="end_date" id="ical-end-date">
  </form>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const eventItems = document.querySelectorAll('.event-item');
    const eventsListContainer = document.getElementById('events-list-container');
    const eventDetailsContainer = document.getElementById('event-details-container');
    const closeDetailsBtn = document.getElementById('close-details');
    const mobileModalOverlay = document.getElementById('mobile-modal-overlay');
    const closeMobileDetailsBtn = document.getElementById('close-mobile-details');
    
    // Event listener for each event item
    eventItems.forEach(item => {
      item.addEventListener('click', function() {
        const eventData = JSON.parse(this.getAttribute('data-event'));
        
        // Store current event data for iCal download
        currentEventData = eventData;
        
        // Remove selected class from all events
        eventItems.forEach(el => el.classList.remove('selected'));
        
        // Add selected class to clicked event
        this.classList.add('selected');
        
        // Display event details in both desktop and mobile views
        displayEventDetails(eventData);
        displayMobileEventDetails(eventData);
        
        if (window.innerWidth >= 1024) {
          // Desktop: Show sidebar layout
          eventsListContainer.classList.remove('lg:w-full');
          eventsListContainer.classList.add('lg:w-1/3');
          eventDetailsContainer.classList.remove('hidden');
        } else {
          // Mobile: Show modal overlay
          mobileModalOverlay.classList.remove('hidden');
          document.body.classList.add('overflow-hidden'); // Prevent scrolling
        }
      });
    });
    
    // Close button functionality (desktop)
    closeDetailsBtn.addEventListener('click', function() {
      eventsListContainer.classList.remove('lg:w-1/3');
      eventsListContainer.classList.add('lg:w-full');
      eventDetailsContainer.classList.add('hidden');
      
      // Remove selected highlight from all events
      eventItems.forEach(el => el.classList.remove('selected'));
    });
    
    // Close button functionality (mobile)
    closeMobileDetailsBtn.addEventListener('click', function() {
      mobileModalOverlay.classList.add('hidden');
      document.body.classList.remove('overflow-hidden'); // Re-enable scrolling
      
      // Remove selected highlight from all events
      eventItems.forEach(el => el.classList.remove('selected'));
    });
    
    // Function to display event details (desktop)
    function displayEventDetails(event) {
      document.getElementById('detail-title').textContent = event.title;
      
      // Format date display
      let dateText = event.date_start;
      if (event.date_end) {
        dateText += ' - ' + event.date_end;
      }
      document.getElementById('detail-date').textContent = dateText;
      
      // Location and address
      const locationContainer = document.getElementById('detail-location-container');
      const locationElement = document.getElementById('detail-location');
      
      if (event.location || event.address) {
        locationContainer.classList.remove('hidden');
        let locationText = '';
        if (event.location) locationText += event.location;
        if (event.address) {
          if (event.location) locationText += ', ';
          locationText += event.address;
        }
        locationElement.textContent = locationText;
      } else {
        locationContainer.classList.add('hidden');
      }
      
      // Description
      document.getElementById('detail-description').innerHTML = event.description;
      
      // Registration
      const registrationSection = document.getElementById('detail-registration');
      const registrationLink = document.getElementById('detail-registration-link');
      
      if (event.registration_required && event.registration_link) {
        registrationSection.classList.remove('hidden');
        registrationLink.href = event.registration_link;
      } else {
        registrationSection.classList.add('hidden');
      }
    }
    
    // Function to display event details (mobile)
    function displayMobileEventDetails(event) {
      document.getElementById('mobile-detail-title').textContent = event.title;
      
      // Format date display
      let dateText = event.date_start;
      if (event.date_end) {
        dateText += ' - ' + event.date_end;
      }
      document.getElementById('mobile-detail-date').textContent = dateText;
      
      // Location and address
      const locationContainer = document.getElementById('mobile-detail-location-container');
      const locationElement = document.getElementById('mobile-detail-location');
      
      if (event.location || event.address) {
        locationContainer.classList.remove('hidden');
        let locationText = '';
        if (event.location) locationText += event.location;
        if (event.address) {
          if (event.location) locationText += ', ';
          locationText += event.address;
        }
        locationElement.textContent = locationText;
      } else {
        locationContainer.classList.add('hidden');
      }
      
      // Description
      document.getElementById('mobile-detail-description').innerHTML = event.description;
      
      // Registration
      const registrationSection = document.getElementById('mobile-detail-registration');
      const registrationLink = document.getElementById('mobile-detail-registration-link');
      
      if (event.registration_required && event.registration_link) {
        registrationSection.classList.remove('hidden');
        registrationLink.href = event.registration_link;
      } else {
        registrationSection.classList.add('hidden');
      }
    }
    
    // Close modal when clicking outside of content (mobile)
    mobileModalOverlay.addEventListener('click', function(e) {
      if (e.target === mobileModalOverlay) {
        mobileModalOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        
        // Remove selected highlight from all events
        eventItems.forEach(el => el.classList.remove('selected'));
      }
    });
    
    // iCal download buttons
    const downloadIcalBtn = document.getElementById('download-ical');
    const mobileDownloadIcalBtn = document.getElementById('mobile-download-ical');
    const icalForm = document.getElementById('ical-form');
    
    // Current event data reference
    let currentEventData = null;
    
    // iCal download function
    function prepareIcalDownload(event) {
      document.getElementById('ical-title').value = event.title;
      document.getElementById('ical-description').value = event.description.replace(/<[^>]*>/g, ''); // Strip HTML
      document.getElementById('ical-location').value = event.location + (event.address ? ', ' + event.address : '');
      document.getElementById('ical-start-date').value = event.date_start;
      document.getElementById('ical-end-date').value = event.date_end || '';
      
      icalForm.submit();
    }
    
    // Event listeners for download buttons
    downloadIcalBtn.addEventListener('click', function() {
      if (currentEventData) {
        prepareIcalDownload(currentEventData);
      }
    });
    
    mobileDownloadIcalBtn.addEventListener('click', function() {
      if (currentEventData) {
        prepareIcalDownload(currentEventData);
      }
    });
  });
</script>

<?php snippet("footer"); ?>