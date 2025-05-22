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
      $events = $site->events()->toStructure();
      $featuredEvents = $events->filterBy('featured', true);
      $regularEvents = $events->not($featuredEvents);
      
      // Get current timestamp for comparison
      $now = time();
      
      // Separate upcoming and past events
      $upcomingEvents = $events->filter(function($event) use($now) {
          return $event->date_start()->toDate() >= $now;
      })->sortBy('date_start', 'asc');
      
      $pastEvents = $events->filter(function($event) use($now) {
          return $event->date_start()->toDate() < $now;
      })->sortBy('date_start', 'desc'); // Past events in reverse chronological order
      
      // Featured events that are upcoming
      $upcomingFeaturedEvents = $featuredEvents->filter(function($event) use($now) {
          return $event->date_start()->toDate() >= $now;
      });
      
      if($events->count()): 
      ?>
        <?php if($upcomingFeaturedEvents->count()): ?>
          <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Hervorgehobene Veranstaltungen</h2>
            <div class="space-y-4">
              <?php foreach($upcomingFeaturedEvents as $event): ?>
              <?php 
                $date = $event->date_start()->toDate();
                $day = date('d', $date);
                $month = date('M', $date);
                $time = date('H:i', $date);
              ?>
              <div class="event-item bg-white p-4 cursor-pointer transition-all border border-leihlokal-500 selected:border-2" 
                   data-event='<?= json_encode([
                     'title' => $event->title()->value(),
                     'date_start' => $event->date_start()->toDate('d.m.Y H:i'),
                     'date_end' => $event->date_end()->isNotEmpty() ? $event->date_end()->toDate('d.m.Y H:i') : null,
                     'location' => $event->location()->value(),
                     'address' => $event->address()->value(),
                     'description' => $event->description()->kt()->value(),
                     'registration_required' => $event->registration_required()->toBool(),
                     'registration_link' => $event->registration_link()->value()
                   ]) ?>'>
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0 text-center min-w-16 bg-gray-50 rounded-lg p-2 shadow-sm border border-gray-100">
                    <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                    <div class="text-3xl font-bold text-leihlokal-500 leading-none my-1"><?= $day ?></div>
                    <div class="text-xs font-medium text-gray-500 bg-white rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
                  </div>
                  <div class="flex-grow">
                    <div class="flex justify-between items-center">
                      <h3 class="text-xl font-semibold"><?= $event->title() ?></h3>
                      <span class="bg-leihlokal-500 text-white px-2 py-1 text-xs rounded-full">Featured</span>
                    </div>
                    <?php if($event->location()->isNotEmpty()): ?>
                      <p class="text-sm text-gray-600 mt-1"><?= $event->location() ?></p>
                    <?php endif ?>
                  </div>
                </div>
              </div>
              <?php endforeach ?>
            </div>
          </div>
        <?php endif ?>
        
        <?php if($upcomingEvents->count()): ?>
        <h2 class="text-2xl font-bold mb-4">Kommende Veranstaltungen</h2>
        <div class="space-y-4" id="upcoming-events">
          <?php foreach($upcomingEvents as $event): ?>
          <?php 
            $date = $event->date_start()->toDate();
            $day = date('d', $date);
            $month = date('M', $date);
            $time = date('H:i', $date);
          ?>
          <div class="event-item bg-white p-4 cursor-pointer transition-all border border-leihlokal-500 selected:border-2" 
               data-event='<?= json_encode([
                 'title' => $event->title()->value(),
                 'date_start' => $event->date_start()->toDate('d.m.Y H:i'),
                 'date_end' => $event->date_end()->isNotEmpty() ? $event->date_end()->toDate('d.m.Y H:i') : null,
                 'location' => $event->location()->value(),
                 'address' => $event->address()->value(),
                 'description' => $event->description()->kt()->value(),
                 'registration_required' => $event->registration_required()->toBool(),
                 'registration_link' => $event->registration_link()->value()
               ]) ?>'>
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 text-center min-w-16 bg-gray-50 rounded-lg p-2 shadow-sm border border-gray-100">
                <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                <div class="text-3xl font-bold text-leihlokal-500 leading-none my-1"><?= $day ?></div>
                <div class="text-xs font-medium text-gray-500 bg-white rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
              </div>
              <div class="flex-grow">
                <h3 class="text-xl font-semibold"><?= $event->title() ?></h3>
                <?php if($event->location()->isNotEmpty()): ?>
                  <p class="text-sm text-gray-600 mt-1"><?= $event->location() ?></p>
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
        
        <?php if($pastEvents->count()): ?>
        <h2 class="text-2xl font-bold mb-4 mt-12">Vergangene Veranstaltungen</h2>
        <div class="space-y-4" id="past-events">
          <?php foreach($pastEvents as $event): ?>
          <?php 
            $date = $event->date_start()->toDate();
            $day = date('d', $date);
            $month = date('M', $date);
            $time = date('H:i', $date);
          ?>
          <div class="event-item bg-gray-50 p-4 cursor-pointer transition-all border border-gray-300 selected:border-2 opacity-75" 
               data-event='<?= json_encode([
                 'title' => $event->title()->value(),
                 'date_start' => $event->date_start()->toDate('d.m.Y H:i'),
                 'date_end' => $event->date_end()->isNotEmpty() ? $event->date_end()->toDate('d.m.Y H:i') : null,
                 'location' => $event->location()->value(),
                 'address' => $event->address()->value(),
                 'description' => $event->description()->kt()->value(),
                 'registration_required' => $event->registration_required()->toBool(),
                 'registration_link' => $event->registration_link()->value()
               ]) ?>'>
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 text-center min-w-16 bg-gray-100 rounded-lg p-2 shadow-sm border border-gray-200">
                <div class="text-xs uppercase font-medium text-gray-500"><?= $month ?></div>
                <div class="text-3xl font-bold text-gray-500 leading-none my-1"><?= $day ?></div>
                <div class="text-xs font-medium text-gray-500 bg-gray-50 rounded-full px-2 py-0.5 inline-block"><?= $time ?></div>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between items-center">
                  <h3 class="text-xl font-semibold text-gray-600"><?= $event->title() ?></h3>
                  <span class="bg-gray-400 text-white px-2 py-1 text-xs rounded-full">Vergangen</span>
                </div>
                <?php if($event->location()->isNotEmpty()): ?>
                  <p class="text-sm text-gray-500 mt-1"><?= $event->location() ?></p>
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