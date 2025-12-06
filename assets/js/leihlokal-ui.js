import api from "./leihlokal-core.js";

const productGrid = document.getElementById("productGrid");
const searchInput = document.getElementById("searchInput");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageNumbers = document.getElementById("pageNumbers");

let currentPage = 1;
let currentCategory = "";
let cart = api.cart;
let showOnlyAvailable = true;

// Helper function to format date
function formatDate(date) {
  return date.toLocaleDateString("de-DE", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

// Helper function to get next occurrence of a weekday
function getNextWeekdayDate(dayKey) {
  const days = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];
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
function generateICS(pickup, items, otp = null) {
  const event = {
    start: new Date(pickup),
    end: new Date(new Date(pickup).getTime() + 30 * 60000), // 30 minutes duration
    title: "leih.lokal Abholung",
    location: "leih.lokal Karlsruhe, Gerwigstraße 41, 76131 Karlsruhe",
  };

  // Build description with proper ICS formatting
  let descriptionLines = ["Abholung deiner reservierten Artikel:"];

  if (otp) {
    descriptionLines.push("");
    descriptionLines.push(`ABHOLCODE: ${otp}`);
    descriptionLines.push("");
  }

  descriptionLines.push("");
  descriptionLines.push("Reservierte Artikel:");
  items.forEach((item) => {
    descriptionLines.push(`- ${formatIID(item.iid)} ${item.name}`);
  });

  // Escape special characters for ICS format and join with \n
  const description = descriptionLines
    .join("\\n")
    .replace(/\\/g, "\\\\")
    .replace(/;/g, "\\;")
    .replace(/,/g, "\\,");

  const icsContent = [
    "BEGIN:VCALENDAR",
    "VERSION:2.0",
    "BEGIN:VEVENT",
    `DTSTART:${event.start
      .toISOString()
      .replace(/[-:]/g, "")
      .replace(/\.\d{3}/, "")}`,
    `DTEND:${event.end
      .toISOString()
      .replace(/[-:]/g, "")
      .replace(/\.\d{3}/, "")}`,
    `SUMMARY:${event.title}`,
    `DESCRIPTION:${description}`,
    `LOCATION:${event.location}`,
    "END:VEVENT",
    "END:VCALENDAR",
  ].join("\r\n");

  const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "leihlokal-abholung.ics";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// ============================================================================
// RESERVATION FLOW
// ============================================================================

let selectedPickupDay = null;

// Show success screen
function showReservationSuccess(record, reservationData, reservedItems) {
  // Hide form, show success
  document.getElementById("reservationForm").classList.add("hidden");
  document.getElementById("successDisplay").classList.remove("hidden");

  // Display OTP
  if (record.otp) {
    document.getElementById("otpNumber").textContent = record.otp;
  }

  // Build summary
  const pickupDate = new Date(reservationData.pickup);
  const formattedDate = pickupDate.toLocaleDateString("de-DE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const formattedTime = pickupDate.toLocaleTimeString("de-DE", {
    hour: "2-digit",
    minute: "2-digit",
  });

  const totalDeposit = reservedItems.reduce((sum, item) => sum + (item.deposit || 0), 0);

  const summary = `
    <div class="flex justify-between border-b pb-2">
      <span class="font-medium">Abholtermin:</span>
      <span>${formattedDate}, ${formattedTime} Uhr</span>
    </div>
    <div class="flex justify-between border-b pb-2">
      <span class="font-medium">Email:</span>
      <span>${reservationData.customer_email}</span>
    </div>
    <div class="flex justify-between border-b pb-2">
      <span class="font-medium">Artikel:</span>
      <span>${reservationData.items.length} Stück</span>
    </div>
    <div class="flex justify-between font-bold text-lg">
      <span>Gesamtpfand:</span>
      <span class="text-leihlokal-600">${totalDeposit.toFixed(2).replace(".", ",")} €</span>
    </div>
  `;

  document.getElementById("reservationSummary").innerHTML = summary;

  // Confetti!
  if (typeof confetti !== "undefined") {
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: 0.6 },
    });
  }

  // Setup calendar button
  const calendarBtn = document.getElementById("addToCalendarBtn");
  const newCalendarBtn = calendarBtn.cloneNode(true);
  calendarBtn.parentNode.replaceChild(newCalendarBtn, calendarBtn);
  newCalendarBtn.addEventListener("click", () => {
    generateICS(reservationData.pickup, reservedItems, record.otp);
  });

  // Setup share button
  const shareBtn = document.getElementById("shareReservationBtn");
  const newShareBtn = shareBtn.cloneNode(true);
  shareBtn.parentNode.replaceChild(newShareBtn, shareBtn);
  newShareBtn.addEventListener("click", () => {
    const otp = record.otp;
    const text = `Meine Reservierung bei leih.lokal Karlsruhe\nAbholcode: ${otp}\nAbholung: ${formattedDate}, ${formattedTime} Uhr`;

    if (navigator.share) {
      navigator.share({
        title: "leih.lokal Reservierung",
        text: text,
      });
    } else {
      navigator.clipboard.writeText(text);
      alert("In Zwischenablage kopiert!");
    }
  });
}

function updateCartUI() {
  const cartItems = document.getElementById("cartItems");
  const clearCartBtn = document.getElementById("clearCart");
  const completeReservationBtn = document.getElementById("completeReservation");
  const emptyMessage = cartItems.querySelector(".empty-cart-message");

  if (cart.items.length === 0) {
    cartItems.innerHTML =
      '<div class="text-gray-500 text-sm">Hier ist noch nichts. Such\' dir was aus!</div>';
    clearCartBtn.classList.add("hidden");
    completeReservationBtn.classList.add("hidden");
    return;
  }

  cartItems.innerHTML = cart.items
    .map(
      (item) => `
        <div class="flex justify-between items-center border-b border-gray-200 pb-2">
            <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
            <button
                class="text-red-500 hover:text-red-700"
                onclick="removeFromCart('${item.id}')"
            >
                ×
            </button>
        </div>
    `,
    )
    .join("");

  clearCartBtn.classList.remove("hidden");
  completeReservationBtn.classList.remove("hidden");
}

function formatPickupDate(dateISO, timeSlot) {
  // Parse ISO date (YYYY-MM-DD)
  const [year, month, day] = dateISO.split('-').map(Number);
  const [hours, minutes] = timeSlot.split(':').map(Number);

  const pickupDate = new Date(year, month - 1, day, hours, minutes, 0, 0);
  return pickupDate.toISOString();
}

// Open reservation modal
document.getElementById("completeReservation").addEventListener("click", () => {
  if (cart.items.length === 0) {
    alert("Dein Ausleihkorb ist leer");
    return;
  }

  // Populate modal cart summary
  const modalCartItems = document.getElementById("modalCartItems");
  const totalDeposit = cart.items.reduce((sum, item) => sum + item.deposit, 0);

  modalCartItems.innerHTML = cart.items
    .map(
      (item) => `
      <div class="flex justify-between text-sm">
        <span class="font-mono">${formatIID(item.iid)}</span>
        <span class="flex-1 px-3">${item.name}</span>
        <span class="font-bold">${item.deposit.toFixed(2)} €</span>
      </div>
    `
    )
    .join("");

  document.getElementById("modalTotalDeposit").textContent =
    totalDeposit.toFixed(2).replace(".", ",") + " €";

  // Reset form state
  selectedPickupDay = null;
  document.querySelectorAll(".day-option").forEach((btn) => {
    btn.classList.remove("day-selected");
  });
  document.getElementById("reservationForm").classList.remove("hidden");
  document.getElementById("successDisplay").classList.add("hidden");

  // Show modal
  document.getElementById("reservationModal").classList.remove("hidden");
});

// Close modal handlers
document.getElementById("closeReservationModal").addEventListener("click", () => {
  document.getElementById("reservationModal").classList.add("hidden");
  // If success screen was showing, refresh page to clear state
  if (!document.getElementById("successDisplay").classList.contains("hidden")) {
    window.location.reload();
  }
});

document.getElementById("closeSuccessBtn")?.addEventListener("click", () => {
  document.getElementById("reservationModal").classList.add("hidden");
  // Refresh page to clear state
  window.location.reload();
});

// Day selection
document.querySelectorAll(".day-option").forEach((btn) => {
  btn.addEventListener("click", (e) => {
    const clickedBtn = e.currentTarget;

    // Deselect all
    document.querySelectorAll(".day-option").forEach((b) => {
      b.classList.remove("day-selected");
    });

    // Select clicked
    clickedBtn.classList.add("day-selected");

    // Store selection
    selectedPickupDay = {
      dateISO: clickedBtn.dataset.dateIso,
      time: clickedBtn.dataset.middleTime,
    };
  });
});

// Form submission
document.getElementById("quickReservationForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  // Validation
  const email = e.target.querySelector('input[name="email"]').value.trim();
  if (!email) {
    alert("Bitte gib deine Email-Adresse ein");
    return;
  }

  if (!selectedPickupDay) {
    alert("Bitte wähle einen Abholtermin aus");
    return;
  }

  // Store cart items before clearing
  const reservedItems = [...cart.items];

  const submitBtn = document.getElementById("submitReservationBtn");
  submitBtn.disabled = true;
  submitBtn.innerHTML = `
    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Wird verarbeitet...
  `;

  try {
    // Build pickup datetime
    const [year, month, day] = selectedPickupDay.dateISO.split("-").map(Number);
    const [hours, minutes] = selectedPickupDay.time.split(":").map(Number);
    const pickupDate = new Date(year, month - 1, day, hours, minutes, 0, 0);

    // Build reservation payload
    const reservationData = {
      customer_email: email,
      items: cart.items.map((item) => item.id),
      pickup: pickupDate.toISOString(),
      comments: "",
      is_new_customer: true,
      done: false,
    };

    // Submit to API
    const record = await api.submitReservation(reservationData);

    // Show success
    showReservationSuccess(record, reservationData, reservedItems);

    // Clear cart
    cart.clearCart();
    updateCartUI();
  } catch (error) {
    console.error("Reservation failed:", error);

    // Show error
    submitBtn.classList.add("bg-red-500");
    submitBtn.innerHTML = `
      <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      Fehler beim Reservieren
    `;

    // Reset after delay
    setTimeout(() => {
      submitBtn.disabled = false;
      submitBtn.className = "w-full bg-leihlokal-500 text-white p-4 text-lg font-bold hover:bg-leihlokal-600 transition-colors";
      submitBtn.innerHTML = "Jetzt reservieren →";
    }, 3000);
  }
});

// Function to add item to cart
window.addToCart = function (item) {
  cart.addItem(item);
  updateCartUI();
};

// Function to remove item from cart
window.removeFromCart = function (itemId) {
  cart.removeItem(itemId);
  updateCartUI();
};

// Filter items by category
async function filterByCategory(category) {
  currentCategory = category;

  // Update active state in UI
  document.querySelectorAll(".category-filter").forEach((el) => {
    el.dataset.active = (el.dataset.category === category).toString();
  });

  try {
    let filter = [];

    if (category) {
      filter.push(`category ~ "${category}"`);
    }

    if (showOnlyAvailable) {
      filter.push('status = "instock"');
    }

    const finalFilter = filter.join(" && ");
    const response = await api.getItems(1, finalFilter);

    productGrid.innerHTML = response.items
      .map((item) => createProductCard(item))
      .join("");

    currentPage = 1;
    updatePagination(response.totalPages);
  } catch (error) {
    console.error("Failed to filter items:", error);
  }
}

// Format item ID in SH.IT notation (as helper)
function formatIID(iid) {
  // Convert to string and pad with zeros
  const num = String(iid).padStart(4, "0");
  // Insert the dot at position 2
  return num.slice(0, 2) + "." + num.slice(2);
}

// Create a product card HTML
function createProductCard(item) {
  const statusConfig = {
    instock: {
      bg: "bg-green-100",
      text: "text-green-800",
      label: "Ausleihbar",
    },
    deleted: { bg: "bg-gray-100", text: "text-gray-800", label: "Gelöscht" },
    outofstock: {
      bg: "bg-yellow-100",
      text: "text-yellow-800",
      label: "Ausgeliehen",
    },
    onbackorder: {
      bg: "bg-purple-100",
      text: "text-purple-800",
      label: "Nachbestellt",
    },
    reserved: {
      bg: "bg-blue-100",
      text: "text-blue-800",
      label: "Vorbestellt",
    },
    lost: { bg: "bg-orange-100", text: "text-orange-800", label: "Verloren" },
    repairing: {
      bg: "bg-red-100",
      text: "text-red-800",
      label: "In Reparatur",
    },
    forsale: {
      bg: "bg-indigo-100",
      text: "text-indigo-800",
      label: "Zum Verkauf",
    },
  };

  const status = statusConfig[item.status] || statusConfig.instock;

  const paddedIid = String(item.iid).padStart(4, "0");

  return `
        <div class="border border-black">
            <!-- ID Header -->
            <div class="bg-white px-4 pt-4 flex items-center justify-between ">
                <span class="text-2xl font-bold font-mono">
                    <span class="bg-leihlokal-500 text-white px-2 py-1">${paddedIid.slice(0, 2)}</span><span class="text-leihlokal-500 px-2 py-1">${paddedIid.slice(2)}</span>
                </span>
                <span class="${status.bg} ${status.text} px-2 py-1 text-sm font-medium">
                    ${status.label}
                </span>
            </div>

            <div class="p-4">
                <div class="aspect-w-1 aspect-h-1 bg-gray-200 mb-4 h-48 cursor-pointer item-detail-trigger" data-item-id="${item.id}">
                    ${
                      item.images?.[0]
                        ? `
                        <img src="${item.images[0].thumb}" alt="${item.name}" class="w-full h-full object-cover">
                    `
                        : ""
                    }
                </div>
                <h3 class="font-bold mb-2 cursor-pointer hover:text-leihlokal-600 item-detail-trigger" data-item-id="${item.id}">${item.name}</h3>
                <p class="text-sm mb-4">${item.description || ""}</p>
                <button class="w-full ${item.status === "instock" ? "bg-leihlokal-500 text-white" : "bg-gray-300 text-gray-600 cursor-not-allowed"} p-2"
                                    ${item.status !== "instock" ? "disabled" : ""}
                                    data-item='${JSON.stringify(item)}'
                                    ${item.status === "instock" ? 'data-action="add-to-cart"' : ""}
                            >
                                ${item.status === "instock" ? "In den Ausleihkorb" : "Bald wieder da!"}
                            </button>
                        </div>
        </div>
    `;
}

// Modal handling functions
function showItemDetails(itemId) {
  const modal = document.getElementById("itemModal");
  const modalContent = document.getElementById("itemModalContent");

  // Fetch item details
  api.getItem(itemId).then((item) => {
    const paddedIid = String(item.iid).padStart(4, "0");
    const detailUrl = `/ll/${paddedIid}`;

    modalContent.innerHTML = `
      <!-- Image -->
      <div class="relative">
        ${
          item.images?.[0]
            ? `<img src="${item.images[0].full}" alt="${item.name}" class="w-full h-64 object-cover">`
            : '<div class="w-full h-64 bg-gray-200 flex items-center justify-center"><span class="text-gray-400">Kein Bild</span></div>'
        }
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Item ID -->
        <div class="text-center mb-4">
          <div class="text-5xl py-4 font-bold font-mono leading-none inline-block">
            <span class="border-2 border-leihlokal-500 bg-leihlokal-500 text-white p-2">${paddedIid.slice(0, 2)}</span><span class="border-2 border-leihlokal-500 p-2 text-leihlokal-500">${paddedIid.slice(2)}</span>
          </div>
        </div>

        <!-- Name -->
        <h2 class="text-2xl font-bold mb-2 text-center">${item.name}</h2>

        ${
          item.brand || item.model
            ? `<p class="text-center text-gray-600 mb-4">${item.brand || ""}${item.brand && item.model ? " - " : ""}${item.model || ""}</p>`
            : ""
        }

        <!-- Deposit -->
        ${
          item.deposit
            ? `
          <div class="bg-leihlokal-50 border-2 border-leihlokal-500 p-3 text-center mb-6">
            <div class="text-xs text-gray-600 mb-1">Pfand</div>
            <div class="text-3xl font-bold text-leihlokal-600">€${item.deposit}</div>
          </div>
        `
            : ""
        }

        <!-- Buttons -->
        <div class="space-y-3">
          ${
            item.status === "instock"
              ? `
            <button class="w-full bg-leihlokal-500 hover:bg-leihlokal-600 text-white p-3 font-bold transition-colors"
                    data-item='${JSON.stringify(item)}'
                    data-action="add-to-cart-modal">
              In den Ausleihkorb
            </button>
          `
              : `
            <div class="w-full bg-gray-200 text-gray-500 p-3 font-bold text-center">
              Zurzeit nicht verfügbar
            </div>
          `
          }

          <a href="${detailUrl}" class="block w-full border-2 border-black hover:bg-gray-100 text-center p-3 font-bold transition-colors">
            Details →
          </a>
        </div>
      </div>
    `;

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden";
  });
}

// Close the item modal
function closeItemModal() {
  const modal = document.getElementById("itemModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  document.body.style.overflow = ""; // Restore scrolling
}

// Reservation Modal Functions

// Initialize API and load initial data
async function initializeApp() {
  try {
    await api.initialize();
    await loadItems(1);
  } catch (error) {
    console.error("Failed to initialize app:", error);
  }
}

// Load and render items
window.loadItems = async function (page = 1) {
  try {
    let filter = [];

    if (currentCategory) {
      filter.push(`category ~ "${currentCategory}"`);
    }

    if (showOnlyAvailable) {
      filter.push('status = "instock"');
    }

    const finalFilter = filter.join(" && ");
    const response = await api.getItems(page, finalFilter);

    productGrid.innerHTML = response.items
      .map((item) => createProductCard(item))
      .join("");

    currentPage = page;
    updatePagination(response.totalPages);
  } catch (error) {
    console.error("Failed to load items:", error);
  }
};

// Update pagination controls
function updatePagination(totalPages) {
  prevPageBtn.disabled = currentPage === 1;
  nextPageBtn.disabled = currentPage === totalPages;

  const pageButtons = [];

  // Always show first page
  if (totalPages > 0) {
    pageButtons.push(`
            <button
                class="px-4 py-2 border border-black ${currentPage === 1 ? "bg-leihlokal-500 text-white" : "hover:bg-leihlokal-800 hover:text-white"}"
                ${currentPage === 1 ? "disabled" : ""}
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
                class="px-4 py-2 border border-black ${currentPage === totalPages ? "bg-leihlokal-500 text-white" : "hover:bg-leihlokal-800 hover:text-white"}"
                ${currentPage === totalPages ? "disabled" : ""}
                onclick="loadItems(${totalPages})"
            >
                ${totalPages}
            </button>
        `);
  }

  pageNumbers.innerHTML = pageButtons.join("");
}

// Event listeners
prevPageBtn.addEventListener("click", () => loadItems(currentPage - 1));
nextPageBtn.addEventListener("click", () => loadItems(currentPage + 1));

document.querySelectorAll(".category-filter").forEach((el) => {
  el.addEventListener("click", () => filterByCategory(el.dataset.category));
});

searchInput.addEventListener(
  "input",
  debounce(async (e) => {
    if (e.target.value) {
      const results = await api.searchItems(e.target.value);
      const filteredResults = showOnlyAvailable
        ? {
            items: results.items.filter((item) => item.status === "instock"),
            totalPages: Math.ceil(
              results.items.filter((item) => item.status === "instock").length /
                20,
            ), // Assuming 20 items per page
          }
        : results;

      productGrid.innerHTML = filteredResults.items
        .map((item) => createProductCard(item))
        .join("");
    } else {
      loadItems(1);
    }
  }, 300),
);

document.getElementById("closeModal").addEventListener("click", closeItemModal);

// Close on background click
document.getElementById("itemModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeItemModal();
  }
});

document
  .getElementById("availableToggle")
  .addEventListener("change", function (e) {
    showOnlyAvailable = e.target.checked;
    loadItems(1); // Reload items with new filter
  });

document.addEventListener("click", function (e) {
  // Handle item detail clicks
  if (e.target.closest(".item-detail-trigger")) {
    const itemId = e.target.closest(".item-detail-trigger").dataset.itemId;
    showItemDetails(itemId);
  }

  // Handle add to cart clicks (from product grid)
  if (e.target.matches('[data-action="add-to-cart"]')) {
    const itemData = JSON.parse(e.target.dataset.item);
    addToCart(itemData);
    playAddToCartAnimation(e.target);
  }

  // Handle add to cart clicks (from modal)
  if (e.target.matches('[data-action="add-to-cart-modal"]')) {
    const itemData = JSON.parse(e.target.dataset.item);
    addToCart(itemData);
    playAddToCartAnimation(e.target);
    // Close modal after adding
    closeItemModal();
  }
});

// Clear cart button
document.getElementById("clearCart").addEventListener("click", () => {
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
  const dot = document.createElement("div");
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
  const cart = document.querySelector(".border.border-black:nth-child(1)"); // Cart container
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
    easing: "easeOutQuart",
    complete: () => {
      dot.remove();

      // Add a quick pulse animation to the cart
      anime({
        targets: cart,
        scale: [1, 1.03, 1],
        duration: 200,
        easing: "easeOutQuad",
      });
    },
  });

  // Also animate the button
  anime({
    targets: buttonEl,
    scale: [1, 0.95, 1],
    duration: 200,
    easing: "easeOutQuad",
  });
}

// Start the app
initializeApp();
