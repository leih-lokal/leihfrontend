<?php snippet("header") ?>
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
$daysTranslation = [
    'mon' => 'Montag',
    'tue' => 'Dienstag',
    'wed' => 'Mittwoch',
    'thu' => 'Donnerstag',
    'fri' => 'Freitag',
    'sat' => 'Samstag',
    'sun' => 'Sonntag'
];

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
      <button class="lg:hidden w-full mb-4 p-4 bg-leihlokal-500 text-white text-left flex justify-between items-center">
        Menu & Cart
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
      </button>

      <!-- Sidebar Content (toggleable on mobile) -->
      <div class="hidden lg:block space-y-8 lg:sticky lg:top-8">
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
<div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white max-w-4xl w-full mx-4 relative">
        <!-- Close button -->
        <button id="closeModal" class="absolute -top-3 -right-3 bg-white border border-black p-2 hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Modal Content -->
        <div id="itemModalContent" class="p-6">
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
    document.querySelector('button.lg\\:hidden').addEventListener('click', function() {
  const sidebar = document.querySelector('.hidden.lg\\:block');
  sidebar.classList.toggle('hidden');
    });

    window.weekSchedule = <?= json_encode($weekSchedule) ?>;

</script>

<!-- Module Script -->
<script type="module">
    import api from '<?= url("assets/js/leihlokal-core.js") ?>';
    
    const productGrid = document.getElementById('productGrid');
    const searchInput = document.getElementById('searchInput');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageNumbers = document.getElementById('pageNumbers');

    let currentPage = 1;
    let currentCategory = '';
    let cart = api.cart;
    let selectedTimeSlot = null;
    let currentReservationStep = 1;
    let showOnlyAvailable = true;
    
    // Helper function to format date
    function formatDate(date) {
        return date.toLocaleDateString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Helper function to get next occurrence of a weekday
    function getNextWeekdayDate(dayKey) {
        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        const today = new Date();
        const currentDay = today.getDay();
        const targetDay = days.indexOf(dayKey);
        
        let daysToAdd = targetDay - (currentDay - 1); // Adjusted for Monday-based week
        if (daysToAdd <= 0) daysToAdd += 7;
        
        const targetDate = new Date(today);
        targetDate.setDate(today.getDate() + daysToAdd);
        return targetDate;
    }
    
    // Function to generate ICS file
    function generateICS(pickup, items) {
        const event = {
            start: new Date(pickup),
            end: new Date(new Date(pickup).getTime() + 30 * 60000), // 30 minutes duration
            title: 'Leihlokal Abholung',
            description: `Abholung deiner reservierten Artikel:\n${items.map(item => `- ${formatIID(item.iid)} ${item.name}`).join('\n')}`,
            location: 'Leihlokal Karlsruhe, Gerwigstraße 41, 76131 Karlsruhe',
        };
        
        const icsContent = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            `DTSTART:${event.start.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')}`,
            `DTEND:${event.end.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')}`,
            `SUMMARY:${event.title}`,
            `DESCRIPTION:${event.description}`,
            `LOCATION:${event.location}`,
            'END:VEVENT',
            'END:VCALENDAR'
        ].join('\n');
        
        const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'leihlokal-abholung.ics';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Function to show confirmation step
    function showConfirmationStep(reservationData, record) {
        // Store cart items for sharing
        const reservedItems = [...cart.items]; // Make a copy of cart items
    
        // Hide time slot selection and show confirmation
        document.getElementById('timeSlotSelection').classList.add('hidden');
        document.getElementById('confirmationStep').classList.remove('hidden');
        
        // Hide ONLY the top cart summary and user type switch
        document.querySelector('.mb-6:first-child').classList.add('hidden');
        document.querySelector('.flex.items-center.justify-center.space-x-4.mb-6').classList.add('hidden');
        
        // Update confirmation details
        const pickupDate = getNextWeekdayDate(selectedTimeSlot.day);
        const details = document.getElementById('confirmationDetails');
        details.innerHTML = `
            <div class="flex justify-between border-b pb-2">
                <span class="font-medium">Abholtermin:</span>
                <span>${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(pickupDate)}, ${selectedTimeSlot.time} Uhr</span>
            </div>
            <div class="border-b pb-2">
                <div class="font-medium mb-2">Reservierte Artikel:</div>
                ${reservedItems.map(item => `
                    <div class="flex justify-between pl-4">
                        <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
                        <span class="text-gray-600">€${item.deposit || 0}</span>
                    </div>
                `).join('')}
            </div>
            <div class="flex justify-between pt-2">
                <span class="font-medium">Gesamtpfand:</span>
                <span>€${reservedItems.reduce((sum, item) => sum + (item.deposit || 0), 0)}</span>
            </div>
        `;
        
        // Trigger confetti
        confetti({
            particleCount: 200,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#ff0000', '#ffffff']
        });
        
        // Add event listeners for calendar and share buttons
        document.getElementById('addToCalendar').addEventListener('click', () => {
            generateICS(reservationData.pickup, reservedItems);
        });
        
        document.getElementById('shareReservation').addEventListener('click', async () => {
            if (navigator.share) {
                try {
                    const pickupDate = getNextWeekdayDate(selectedTimeSlot.day);
                    await navigator.share({
                        title: 'Meine Leihlokal Reservierung',
                        text: `Ich habe folgende Artikel im Leihlokal reserviert:\n${reservedItems.map(item => `- ${formatIID(item.iid)} ${item.name}`).join('\n')}\nAbholung: ${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(pickupDate)}, ${selectedTimeSlot.time} Uhr`,
                    });
                } catch (err) {
                    console.error('Share failed:', err);
                }
            } else {
                alert('Teilen wird von deinem Browser nicht unterstützt');
            }
        });
    }
    
    function updateCartUI() {
        const cartItems = document.getElementById('cartItems');
        const clearCartBtn = document.getElementById('clearCart');
        const completeReservationBtn = document.getElementById('completeReservation');
        const emptyMessage = cartItems.querySelector('.empty-cart-message');
    
        if (cart.items.length === 0) {
            cartItems.innerHTML = '<div class="text-gray-500 text-sm">Hier ist noch nichts. Such\' dir was aus!</div>';
            clearCartBtn.classList.add('hidden');
            completeReservationBtn.classList.add('hidden');
            return;
        }
    
        cartItems.innerHTML = cart.items.map(item => `
            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
                <button 
                    class="text-red-500 hover:text-red-700"
                    onclick="removeFromCart('${item.id}')"
                >
                    ×
                </button>
            </div>
        `).join('');
    
        clearCartBtn.classList.remove('hidden');
        completeReservationBtn.classList.remove('hidden');
    }
    
    function formatPickupDate(dayKey, timeSlot) {
        // Get current date and find next occurrence of the selected day
        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']; // Changed order to match PHP
        const today = new Date();
        const currentDay = today.getDay();
        const targetDay = days.indexOf(dayKey);
        
        let daysToAdd = targetDay - (currentDay - 1); // Adjusted for Monday-based week
        if (daysToAdd <= 0) daysToAdd += 7;
        
        const pickupDate = new Date(today);
        pickupDate.setDate(today.getDate() + daysToAdd);
        
        // Set the time
        const [hours, minutes] = timeSlot.split(':');
        pickupDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        return pickupDate.toISOString();
    }
    
    // Update the submit button handler
    document.getElementById('submitReservation').addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (currentReservationStep === 1) {
            const isExistingUser = document.getElementById('userTypeSwitch').checked;
            const activeForm = document.getElementById(isExistingUser ? 'existingUserForm' : 'newUserForm');
            
            if (activeForm.checkValidity()) {
                // Move to time slot selection
                document.getElementById('formContainer').classList.add('hidden');
                document.getElementById('timeSlotSelection').classList.remove('hidden');
                this.textContent = 'Reservierung abschließen →';
                currentReservationStep = 2;
            } else {
                activeForm.reportValidity();
            }
        } else {
            if (!selectedTimeSlot) {
                alert('Bitte wähle einen Abholtermin aus');
                return;
            }
            
            // Get the button reference
            const submitButton = this;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Wird verarbeitet...
            `;
            
            try {
                const isExistingUser = document.getElementById('userTypeSwitch').checked;
                const formData = isExistingUser 
                    ? {
                        customer_iid: document.querySelector('#existingUserForm input').value,
                        is_new_customer: false
                    }
                    : {
                        customer_name: document.querySelector('#newUserForm input[type="text"]').value,
                        customer_email: document.querySelector('#newUserForm input[type="email"]').value,
                        customer_phone: document.querySelector('#newUserForm input[type="tel"]').value,
                        is_new_customer: true
                    };
                
                const reservationData = {
                    ...formData,
                    items: cart.items.map(item => item.id),
                    pickup: formatPickupDate(selectedTimeSlot.day, selectedTimeSlot.time),
                    comments: "",
                    done: true
                };
                
                // Submit to API
                const record = await api.pb.collection('reservation').create(reservationData);
                
                // Show success message and switch to confirmation step
                submitButton.className = 'hidden'; // Hide the submit button
                showConfirmationStep(reservationData, record);
                
                // Clear cart (but don't close modal)
                cart.clearCart();
                updateCartUI();
                
            } catch (error) {
                console.error('Reservation failed:', error);
                
                // Show error state
                submitButton.className = 'w-full bg-red-500 text-white p-3 transition-colors';
                submitButton.innerHTML = `
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Fehler beim Reservieren
                `;
                
                // Reset button after delay
                setTimeout(() => {
                    submitButton.className = 'w-full bg-leihlokal-500 text-white p-3 hover:bg-leihlokal-600 transition-colors';
                    submitButton.innerHTML = 'Vorbestellung abschicken! →';
                    submitButton.disabled = false;
                }, 3000);
            }
        }
    });
    
    // Function to add item to cart
    window.addToCart = function(item) {
        cart.addItem(item);
        updateCartUI();
    }
    
    // Function to remove item from cart
    window.removeFromCart = function(itemId) {
        cart.removeItem(itemId);
        updateCartUI();
    }
    
    // Filter items by category
    async function filterByCategory(category) {
        currentCategory = category;
        
        // Update active state in UI
        document.querySelectorAll('.category-filter').forEach(el => {
            el.dataset.active = (el.dataset.category === category).toString();
        });
    
        try {
            let filter = [];
            
            if (category) {
                filter.push(`cat ~ "${category}"`);
            }
            
            if (showOnlyAvailable) {
                filter.push('status = "instock"');
            }
            
            const finalFilter = filter.join(' && ');
            const response = await api.getItems(1, finalFilter);
            
            productGrid.innerHTML = response.items
                .map(item => createProductCard(item))
                .join('');
    
            currentPage = 1;
            updatePagination(response.totalPages);
        } catch (error) {
            console.error('Failed to filter items:', error);
        }
    }

    // Format item ID in SH.IT notation (as helper)
    function formatIID(iid) {
        // Convert to string and pad with zeros
        const num = String(iid).padStart(4, '0');
        // Insert the dot at position 2
        return num.slice(0, 2) + '.' + num.slice(2);
    }
    
    // Create a product card HTML
    function createProductCard(item) {
      const statusConfig = {
          instock: { bg: 'bg-green-100', text: 'text-green-800', label: 'Ausleihbar' },
          deleted: { bg: 'bg-gray-100', text: 'text-gray-800', label: 'Gelöscht' },
          outofstock: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Ausgeliehen' },
          onbackorder: { bg: 'bg-purple-100', text: 'text-purple-800', label: 'Nachbestellt' },
          reserved: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Vorbestellt' },
          lost: { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Verloren' },
          repairing: { bg: 'bg-red-100', text: 'text-red-800', label: 'In Reparatur' },
          forsale: { bg: 'bg-indigo-100', text: 'text-indigo-800', label: 'Zum Verkauf' }
      };
    
        const status = statusConfig[item.status] || statusConfig.instock;
    
        return `
            <div class="border border-black relative">
                <div class="absolute -top-3 right-4 bg-white px-2 border border-black">
                    <span class="font-mono text-lg font-bold text-leihlokal-600">${formatIID(item.iid)}</span>
                </div>
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="${status.bg} ${status.text} px-2 py-1 text-sm flex items-center gap-1">
                            ${status.label}
                        </span>
                    </div>
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200 mb-4 h-48 cursor-pointer item-detail-trigger" data-item-id="${item.id}">
                        ${item.images?.[0] ? `
                            <img src="${item.images[0].thumb}" alt="${item.name}" class="w-full h-full object-cover">
                        ` : ''}
                    </div>
                    <h3 class="font-bold mb-2 cursor-pointer hover:text-leihlokal-600 item-detail-trigger" data-item-id="${item.id}">${item.name}</h3>
                    <p class="text-sm mb-4">${item.description || ''}</p>
                    <button class="w-full ${item.status === 'instock' ? 'bg-leihlokal-500 text-white' : 'bg-gray-300 text-gray-600 cursor-not-allowed'} p-2" 
                                        ${item.status !== 'instock' ? 'disabled' : ''}
                                        data-item='${JSON.stringify(item)}'
                                        ${item.status === 'instock' ? 'data-action="add-to-cart"' : ''}
                                >
                                    ${item.status === 'instock' ? 'In den Ausleihkorb' : 'Bald wieder da!'}
                                </button>
                            </div>
            </div>
        `;
    }
    
    // Modal handling functions
    function showItemDetails(itemId) {
        const modal = document.getElementById('itemModal');
        const modalContent = document.getElementById('itemModalContent');
        
        // Fetch item details
        api.getItem(itemId).then(item => {
          const statusConfig = {
              instock: { bg: 'bg-green-100', text: 'text-green-800', label: 'Ausleihbar' },
              deleted: { bg: 'bg-gray-100', text: 'text-gray-800', label: 'Gelöscht' },
              outofstock: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Ausgeliehen' },
              onbackorder: { bg: 'bg-purple-100', text: 'text-purple-800', label: 'Nachbestellt' },
              reserved: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Vorbestellt' },
              lost: { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Verloren' },
              repairing: { bg: 'bg-red-100', text: 'text-red-800', label: 'In Reparatur' },
              forsale: { bg: 'bg-indigo-100', text: 'text-indigo-800', label: 'Zum Verkauf' }
          };
    
            const status = statusConfig[item.status] || statusConfig.instock;
            
            modalContent.innerHTML = `
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="md:w-1/2">
                        <div class="aspect-w-1 aspect-h-1 bg-gray-200 mb-4">
                            ${item.images?.[0] ? `
                                <img src="${item.images[0].full}" alt="${item.name}" class="w-full h-full object-cover">
                            ` : ''}
                        </div>
                        ${item.images?.length > 1 ? `
                            <div class="grid grid-cols-4 gap-2">
                                ${item.images.slice(1).map(img => `
                                    <img src="${img.thumb}" alt="" class="w-full h-20 object-cover">
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                    <div class="md:w-1/2">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-4xl mt-4 font-bold">${item.name}</h2>
                            <span class="font-mono text-xl font-bold border p-4 border-leihlokal-500 text-leihlokal-600">${formatIID(item.iid)}</span>
                        </div>
                        <span class="${status.bg} ${status.text} px-2 py-1 text-sm inline-flex items-center gap-1 mb-4">
                            ${status.label}
                        </span>
                        ${item.brand || item.model ? `
                            <div class="mb-4">
                                ${item.brand ? `<p class="font-medium">${item.brand}</p>` : ''}
                                ${item.model ? `<p class="font-mono">${item.model}</p>` : ''}
                            </div>
                        ` : ''}
                        <div class="prose prose-sm max-w-none mb-6">
                            ${item.description || ''}
                        </div>
                        ${item.parts > 1 ? `
                            <p class="text-sm mb-4">Dieser Gegenstand hat ${item.parts} Teile.</p>
                        ` : ''}
                        ${item.deposit ? `
                            <p class="text-xl mb-4">Pfand: €${item.deposit}</p>
                        ` : ''}
                        <button class="w-full ${item.status === 'instock' ? 'bg-leihlokal-500 text-white' : 'bg-gray-300 text-gray-600 cursor-not-allowed'} p-2 mt-auto" 
                                        ${item.status !== 'instock' ? 'disabled' : ''}
                                        data-item='${JSON.stringify(item)}'
                                        ${item.status === 'instock' ? 'data-action="add-to-cart"' : ''}
                                >
                                    ${item.status === 'instock' ? 'In den Ausleihkorb' : 'Bald wieder da!'}
                                </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
    }
    
    // Close the item modal
    function closeItemModal() {
        const modal = document.getElementById('itemModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    // Reservation Modal Functions
    function showReservationModal() {
        const modal = document.getElementById('reservationModal');
        const cartItemsContainer = document.getElementById('reservationCartItems');
        const totalDepositSpan = document.getElementById('totalDeposit');
        
        // Calculate total deposit and create items summary
        let totalDeposit = 0;
        const itemsHTML = cart.items.map(item => {
            const deposit = item.deposit || 0;
            totalDeposit += deposit;
            return `
                <div class="flex justify-between items-center py-2">
                    <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
                    <span class="text-gray-600">€${deposit}</span>
                </div>
            `;
        }).join('');
        
        cartItemsContainer.innerHTML = itemsHTML;
        totalDepositSpan.textContent = `€${totalDeposit}`;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        // Reset state
        currentReservationStep = 1;
        selectedTimeSlot = null;
        document.getElementById('timeSlotSelection').classList.add('hidden');
        document.getElementById('formContainer').classList.remove('hidden');
        document.getElementById('selectedTimeDisplay').textContent = 'None';
        
        // Update button text
        document.getElementById('submitReservation').textContent = 'Weiter zur Terminauswahl →';
    }
    
    function closeReservationModal() {
        const modal = document.getElementById('reservationModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        
        // Reset forms
        document.getElementById('newUserForm').reset();
        document.getElementById('existingUserForm').reset();
        
        // Reset state
        currentReservationStep = 1;
        selectedTimeSlot = null;
        
        // Reset UI
        document.getElementById('timeSlotSelection').classList.add('hidden');
        document.getElementById('formContainer').classList.remove('hidden');
        document.getElementById('selectedTimeDisplay').textContent = 'None';
        
        // Reset button
        const submitButton = document.getElementById('submitReservation');
        submitButton.className = 'w-full bg-leihlokal-500 text-white p-3 hover:bg-leihlokal-600 transition-colors';
        submitButton.innerHTML = 'Vorbestellung abschicken! →';
        submitButton.disabled = false;
        
        // Clear selected time slot styling
        document.querySelectorAll('.time-slot-button').forEach(btn => {
            btn.classList.remove('bg-leihlokal-500', 'text-white');
        });
        
        // Also hide confirmation step
        document.getElementById('confirmationStep').classList.add('hidden');
    }
    
    // Update Complete Reservation button click handler
    document.getElementById('completeReservation').addEventListener('click', showReservationModal);
    
    // Close button handler
    document.getElementById('closeReservationModal').addEventListener('click', closeReservationModal);
    
    // Close on background click
    document.getElementById('reservationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReservationModal();
        }
    });
    
    // Handle user type switch
    document.getElementById('userTypeSwitch').addEventListener('change', function(e) {
        const newUserForm = document.getElementById('newUserForm');
        const existingUserForm = document.getElementById('existingUserForm');
        
        if (this.checked) {
            newUserForm.classList.add('hidden');
            existingUserForm.classList.remove('hidden');
        } else {
            newUserForm.classList.remove('hidden');
            existingUserForm.classList.add('hidden');
        }
    });
    

    // Initialize API and load initial data
    async function initializeApp() {
        try {
            await api.initialize();
            await loadItems(1);
            
        } catch (error) {
            console.error('Failed to initialize app:', error);
        }
    }

    // Load and render items
    window.loadItems = async function(page = 1) {
        try {
            let filter = [];
            
            if (currentCategory) {
                filter.push(`cat ~ "${currentCategory}"`);
            }
            
            if (showOnlyAvailable) {
                filter.push('status = "instock"');
            }
            
            const finalFilter = filter.join(' && ');
            const response = await api.getItems(page, finalFilter);
            
            productGrid.innerHTML = response.items
                .map(item => createProductCard(item))
                .join('');
    
            currentPage = page;
            updatePagination(response.totalPages);
            
        } catch (error) {
            console.error('Failed to load items:', error);
        }
    }

    // Update pagination controls
    function updatePagination(totalPages) {
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    
        const pageButtons = [];
        
        // Always show first page
        if (totalPages > 0) {
            pageButtons.push(`
                <button 
                    class="px-4 py-2 border border-black ${currentPage === 1 ? 'bg-leihlokal-500 text-white' : 'hover:bg-leihlokal-800 hover:text-white'}"
                    ${currentPage === 1 ? 'disabled' : ''}
                    onclick="loadItems(1)"
                >
                    1
                </button>
            `);
        }
    
        // Add ellipsis if there's a gap
        if (currentPage > 2) {
            pageButtons.push('<span class="px-2">...</span>');
        }
    
        // Current page (if not first or last)
        if (currentPage !== 1 && currentPage !== totalPages) {
            pageButtons.push(`
                <button 
                    class="px-4 py-2 border border-black bg-leihlokal-500 text-white"
                    disabled
                >
                    ${currentPage}
                </button>
            `);
        }
    
        // Add ellipsis if there's a gap
        if (currentPage < totalPages - 1) {
            pageButtons.push('<span class="px-2">...</span>');
        }
    
        // Always show last page
        if (totalPages > 1) {
            pageButtons.push(`
                <button 
                    class="px-4 py-2 border border-black ${currentPage === totalPages ? 'bg-leihlokal-500 text-white' : 'hover:bg-leihlokal-800 hover:text-white'}"
                    ${currentPage === totalPages ? 'disabled' : ''}
                    onclick="loadItems(${totalPages})"
                >
                    ${totalPages}
                </button>
            `);
        }
    
        pageNumbers.innerHTML = pageButtons.join('');
    }

    // Event listeners
    prevPageBtn.addEventListener('click', () => loadItems(currentPage - 1));
    nextPageBtn.addEventListener('click', () => loadItems(currentPage + 1));
    
    document.querySelectorAll('.category-filter').forEach(el => {
        el.addEventListener('click', () => filterByCategory(el.dataset.category));
    });
    
    searchInput.addEventListener('input', debounce(async (e) => {
        if (e.target.value) {
            const results = await api.searchItems(e.target.value);
            const filteredResults = showOnlyAvailable 
                ? { 
                    items: results.items.filter(item => item.status === 'instock'),
                    totalPages: Math.ceil(results.items.filter(item => item.status === 'instock').length / 20) // Assuming 20 items per page
                  }
                : results;
            
            productGrid.innerHTML = filteredResults.items
                .map(item => createProductCard(item))
                .join('');
        } else {
            loadItems(1);
        }
    }, 300));
    
    
    document.getElementById('closeModal').addEventListener('click', closeItemModal);
    
    // Close on background click
    document.getElementById('itemModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeItemModal();
        }
    });
    
    document.getElementById('availableToggle').addEventListener('change', function(e) {
        showOnlyAvailable = e.target.checked;
        loadItems(1); // Reload items with new filter
    });
    
    document.addEventListener('click', function(e) {
        // Handle item detail clicks
        if (e.target.closest('.item-detail-trigger')) {
            const itemId = e.target.closest('.item-detail-trigger').dataset.itemId;
            showItemDetails(itemId);
        }
        
        // Handle add to cart clicks
        if (e.target.matches('[data-action="add-to-cart"]')) {
            const itemData = JSON.parse(e.target.dataset.item);
            addToCart(itemData);
            playAddToCartAnimation(e.target);
        }
        
        // Handle time slot selection
        if (e.target.matches('.time-slot-button')) {
            // Remove previous selection
            document.querySelectorAll('.time-slot-button').forEach(btn => {
                btn.classList.remove('bg-leihlokal-500', 'text-white');
            });
            
            // Add selection to clicked button
            e.target.classList.add('bg-leihlokal-500', 'text-white');
            
            // Store selection
            selectedTimeSlot = {
                day: e.target.dataset.day,
                time: e.target.dataset.time
            };
            
            
            // Update display with date
            const targetDate = getNextWeekdayDate(selectedTimeSlot.day);
            document.getElementById('selectedTimeDisplay').textContent = 
                `${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(targetDate)}, ${selectedTimeSlot.time} Uhr`;
        }
    });
    
    // Clear cart button
    document.getElementById('clearCart').addEventListener('click', () => {
        cart.clearCart();
        updateCartUI();
    });
    
    // Subscribe to cart updates
    cart.subscribe(updateCartUI);
    
    // Initialize cart UI
    updateCartUI();

    // Utility function for debouncing search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Function to play the add-to-cart animation
    function playAddToCartAnimation(buttonEl) {
        // Create the flying dot
        const dot = document.createElement('div');
        dot.style.cssText = `
            position: fixed;
            width: 40px;
            height: 40px;
            background: #f00;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
        `;
        document.body.appendChild(dot);
    
        // Get the cart element position
        const cart = document.querySelector('.border.border-black:nth-child(2)'); // Cart container
        const cartRect = cart.getBoundingClientRect();
        const buttonRect = buttonEl.getBoundingClientRect();
    
        // Set initial position
        dot.style.left = `${buttonRect.left + buttonRect.width / 2}px`;
        dot.style.top = `${buttonRect.top + buttonRect.height / 2}px`;
    
        // Animate the dot
        anime({
            targets: dot,
            left: cartRect.left + cartRect.width / 2,
            top: cartRect.top + cartRect.height / 2,
            scale: [1, 0.5],
            opacity: [1, 0],
            duration: 600,
            easing: 'easeOutQuart',
            complete: () => {
                dot.remove();
                
                // Add a quick pulse animation to the cart
                anime({
                    targets: cart,
                    scale: [1, 1.03, 1],
                    duration: 200,
                    easing: 'easeOutQuad'
                });
            }
        });
    
        // Also animate the button
        anime({
            targets: buttonEl,
            scale: [1, 0.95, 1],
            duration: 200,
            easing: 'easeOutQuad'
        });
    }
    
    // Start the app
    initializeApp();
</script>

<?php snippet("footer") ?>