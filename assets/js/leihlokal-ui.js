import api from "./leihlokal-core.js";

const productGrid = document.getElementById("productGrid");
const searchInput = document.getElementById("searchInput");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageNumbers = document.getElementById("pageNumbers");

let currentPage = 1;
let currentCategory = "";
let cart = api.cart;
let selectedTimeSlot = null;
let currentReservationStep = 1;
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
function generateICS(pickup, items) {
  const event = {
    start: new Date(pickup),
    end: new Date(new Date(pickup).getTime() + 30 * 60000), // 30 minutes duration
    title: "Leihlokal Abholung",
    description: `Abholung deiner reservierten Artikel:\n${items.map((item) => `- ${formatIID(item.iid)} ${item.name}`).join("\n")}`,
    location: "Leihlokal Karlsruhe, Gerwigstraße 41, 76131 Karlsruhe",
  };

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
    `DESCRIPTION:${event.description}`,
    `LOCATION:${event.location}`,
    "END:VEVENT",
    "END:VCALENDAR",
  ].join("\n");

  const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "leihlokal-abholung.ics";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Function to show confirmation step
function showConfirmationStep(reservationData, record) {
  // Store cart items for sharing
  const reservedItems = [...cart.items]; // Make a copy of cart items

  // Hide time slot selection and show confirmation
  document.getElementById("timeSlotSelection").classList.add("hidden");
  document.getElementById("confirmationStep").classList.remove("hidden");

  // Hide ONLY the top cart summary and user type switch
  document.querySelector(".mb-6:first-child").classList.add("hidden");
  document
    .querySelector(".flex.items-center.justify-center.space-x-4.mb-6")
    .classList.add("hidden");

  // Update confirmation details
  const pickupDate = getNextWeekdayDate(selectedTimeSlot.day);
  const details = document.getElementById("confirmationDetails");
  details.innerHTML = `
        <div class="flex justify-between border-b pb-2">
            <span class="font-medium">Abholtermin:</span>
            <span>${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(pickupDate)}, ${selectedTimeSlot.time} Uhr</span>
        </div>
        <div class="border-b pb-2">
            <div class="font-medium mb-2">Reservierte Artikel:</div>
            ${reservedItems
              .map(
                (item) => `
                <div class="flex justify-between pl-4">
                    <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
                    <span class="text-gray-600">€${item.deposit || 0}</span>
                </div>
            `,
              )
              .join("")}
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
    colors: ["#ff0000", "#ffffff"],
  });

  // Add event listeners for calendar and share buttons
  document.getElementById("addToCalendar").addEventListener("click", () => {
    generateICS(reservationData.pickup, reservedItems);
  });

  document
    .getElementById("shareReservation")
    .addEventListener("click", async () => {
      if (navigator.share) {
        try {
          const pickupDate = getNextWeekdayDate(selectedTimeSlot.day);
          await navigator.share({
            title: "Meine Leihlokal Reservierung",
            text: `Ich habe folgende Artikel im Leihlokal reserviert:\n${reservedItems.map((item) => `- ${formatIID(item.iid)} ${item.name}`).join("\n")}\nAbholung: ${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(pickupDate)}, ${selectedTimeSlot.time} Uhr`,
          });
        } catch (err) {
          console.error("Share failed:", err);
        }
      } else {
        alert("Teilen wird von deinem Browser nicht unterstützt");
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

function formatPickupDate(dayKey, timeSlot) {
  // Get current date and find next occurrence of the selected day
  const days = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"]; // Changed order to match PHP
  const today = new Date();
  const currentDay = today.getDay();
  const targetDay = days.indexOf(dayKey);

  let daysToAdd = targetDay - (currentDay - 1); // Adjusted for Monday-based week
  if (daysToAdd <= 0) daysToAdd += 7;

  const pickupDate = new Date(today);
  pickupDate.setDate(today.getDate() + daysToAdd);

  // Set the time
  const [hours, minutes] = timeSlot.split(":");
  pickupDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);

  return pickupDate.toISOString();
}

// Update the submit button handler
document
  .getElementById("submitReservation")
  .addEventListener("click", async function (e) {
    e.preventDefault();

    if (currentReservationStep === 1) {
      const isExistingUser = document.getElementById("userTypeSwitch").checked;
      const activeForm = document.getElementById(
        isExistingUser ? "existingUserForm" : "newUserForm",
      );

      if (activeForm.checkValidity()) {
        // Move to time slot selection
        document.getElementById("formContainer").classList.add("hidden");
        document.getElementById("timeSlotSelection").classList.remove("hidden");
        this.textContent = "Reservierung abschließen →";
        currentReservationStep = 2;
      } else {
        activeForm.reportValidity();
      }
    } else {
      if (!selectedTimeSlot) {
        alert("Bitte wähle einen Abholtermin aus");
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
        const isExistingUser =
          document.getElementById("userTypeSwitch").checked;
        const formData = isExistingUser
          ? {
              customer_iid: document.querySelector("#existingUserForm input")
                .value,
              customer_name: "exists",
              customer_email: "ex@is.ts",
              customer_phone: "0123456789",
              is_new_customer: false,
            }
          : {
              customer_name: document.querySelector(
                '#newUserForm input[type="text"]',
              ).value,
              customer_email: document.querySelector(
                '#newUserForm input[type="email"]',
              ).value,
              customer_phone: document.querySelector(
                '#newUserForm input[type="tel"]',
              ).value,
              is_new_customer: true,
            };

        const reservationData = {
          ...formData,
          items: cart.items.map((item) => item.id),
          pickup: formatPickupDate(selectedTimeSlot.day, selectedTimeSlot.time),
          comments: "",
          done: false,
        };

        // Submit to API
        const response = await fetch(
          "https://stage.leihlokal-ka.de/api/collections/reservation/records",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(reservationData),
          },
        );

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const record = await response.json();

        // Show success message and switch to confirmation step
        submitButton.className = "hidden"; // Hide the submit button
        showConfirmationStep(reservationData, record);

        // Clear cart (but don't close modal)
        cart.clearCart();
        updateCartUI();
      } catch (error) {
        console.error("Reservation failed:", error);

        // Show error state
        submitButton.className =
          "w-full bg-red-500 text-white p-3 transition-colors";
        submitButton.innerHTML = `
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Fehler beim Reservieren
            `;

        // Reset button after delay
        setTimeout(() => {
          submitButton.className =
            "w-full bg-leihlokal-500 text-white p-3 hover:bg-leihlokal-600 transition-colors";
          submitButton.innerHTML = "Vorbestellung abschicken! →";
          submitButton.disabled = false;
        }, 3000);
      }
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
      filter.push(`cat ~ "${category}"`);
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

    modalContent.innerHTML = `
            <div class="flex flex-col md:flex-row gap-6">
                <div class="md:w-1/2">
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200 mb-4">
                        ${
                          item.images?.[0]
                            ? `
                            <img src="${item.images[0].full}" alt="${item.name}" class="w-full h-full object-cover">
                        `
                            : ""
                        }
                    </div>
                    ${
                      item.images?.length > 1
                        ? `
                        <div class="grid grid-cols-4 gap-2">
                            ${item.images
                              .slice(1)
                              .map(
                                (img) => `
                                <img src="${img.thumb}" alt="" class="w-full h-20 object-cover">
                            `,
                              )
                              .join("")}
                        </div>
                    `
                        : ""
                    }
                </div>
                <div class="md:w-1/2">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-4xl mt-4 font-bold">${item.name}</h2>
                        <span class="font-mono text-xl font-bold border p-4 border-leihlokal-500 text-leihlokal-600">${formatIID(item.iid)}</span>
                    </div>
                    <span class="${status.bg} ${status.text} px-2 py-1 text-sm inline-flex items-center gap-1 mb-4">
                        ${status.label}
                    </span>
                    ${
                      item.brand || item.model
                        ? `
                        <div class="mb-4">
                            ${item.brand ? `<p class="font-medium">${item.brand}</p>` : ""}
                            ${item.model ? `<p class="font-mono">${item.model}</p>` : ""}
                        </div>
                    `
                        : ""
                    }
                    <div class="prose prose-sm max-w-none mb-6">
                        ${item.description || ""}
                    </div>
                    ${
                      item.parts > 1
                        ? `
                        <p class="text-sm mb-4">Dieser Gegenstand hat ${item.parts} Teile.</p>
                    `
                        : ""
                    }
                    ${
                      item.deposit
                        ? `
                        <p class="text-xl mb-4">Pfand: €${item.deposit}</p>
                    `
                        : ""
                    }
                    <button class="w-full ${item.status === "instock" ? "bg-leihlokal-500 text-white" : "bg-gray-300 text-gray-600 cursor-not-allowed"} p-2 mt-auto"
                                    ${item.status !== "instock" ? "disabled" : ""}
                                    data-item='${JSON.stringify(item)}'
                                    ${item.status === "instock" ? 'data-action="add-to-cart"' : ""}
                            >
                                ${item.status === "instock" ? "In den Ausleihkorb" : "Bald wieder da!"}
                            </button>
                </div>
            </div>
        `;

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden"; // Prevent scrolling
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
function showReservationModal() {
  const modal = document.getElementById("reservationModal");
  const cartItemsContainer = document.getElementById("reservationCartItems");
  const totalDepositSpan = document.getElementById("totalDeposit");

  // Calculate total deposit and create items summary
  let totalDeposit = 0;
  const itemsHTML = cart.items
    .map((item) => {
      const deposit = item.deposit || 0;
      totalDeposit += deposit;
      return `
            <div class="flex justify-between items-center py-2">
                <span class="font-mono">${formatIID(item.iid)} - ${item.name}</span>
                <span class="text-gray-600">€${deposit}</span>
            </div>
        `;
    })
    .join("");

  cartItemsContainer.innerHTML = itemsHTML;
  totalDepositSpan.textContent = `€${totalDeposit}`;

  modal.classList.remove("hidden");
  modal.classList.add("flex");
  document.body.style.overflow = "hidden";

  // Reset state
  currentReservationStep = 1;
  selectedTimeSlot = null;
  document.getElementById("timeSlotSelection").classList.add("hidden");
  document.getElementById("formContainer").classList.remove("hidden");
  document.getElementById("selectedTimeDisplay").textContent = "None";

  // Update button text
  document.getElementById("submitReservation").textContent =
    "Weiter zur Terminauswahl →";
}

function closeReservationModal() {
  const modal = document.getElementById("reservationModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  document.body.style.overflow = "";

  // Reset forms
  document.getElementById("newUserForm").reset();
  document.getElementById("existingUserForm").reset();

  // Reset state
  currentReservationStep = 1;
  selectedTimeSlot = null;

  // Reset UI
  document.getElementById("timeSlotSelection").classList.add("hidden");
  document.getElementById("formContainer").classList.remove("hidden");
  document.getElementById("selectedTimeDisplay").textContent = "None";

  // Reset button
  const submitButton = document.getElementById("submitReservation");
  submitButton.className =
    "w-full bg-leihlokal-500 text-white p-3 hover:bg-leihlokal-600 transition-colors";
  submitButton.innerHTML = "Vorbestellung abschicken! →";
  submitButton.disabled = false;

  // Clear selected time slot styling
  document.querySelectorAll(".time-slot-button").forEach((btn) => {
    btn.classList.remove("bg-leihlokal-500", "text-white");
  });

  // Also hide confirmation step
  document.getElementById("confirmationStep").classList.add("hidden");
}

// Update Complete Reservation button click handler
document
  .getElementById("completeReservation")
  .addEventListener("click", showReservationModal);

// Close button handler
document
  .getElementById("closeReservationModal")
  .addEventListener("click", closeReservationModal);

// Close on background click
document
  .getElementById("reservationModal")
  .addEventListener("click", function (e) {
    if (e.target === this) {
      closeReservationModal();
    }
  });

// Handle user type switch
document
  .getElementById("userTypeSwitch")
  .addEventListener("change", function (e) {
    const newUserForm = document.getElementById("newUserForm");
    const existingUserForm = document.getElementById("existingUserForm");

    if (this.checked) {
      newUserForm.classList.add("hidden");
      existingUserForm.classList.remove("hidden");
    } else {
      newUserForm.classList.remove("hidden");
      existingUserForm.classList.add("hidden");
    }
  });

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
      filter.push(`cat ~ "${currentCategory}"`);
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

  // Handle add to cart clicks
  if (e.target.matches('[data-action="add-to-cart"]')) {
    const itemData = JSON.parse(e.target.dataset.item);
    addToCart(itemData);
    playAddToCartAnimation(e.target);
  }

  // Handle time slot selection
  if (e.target.matches(".time-slot-button")) {
    // Remove previous selection
    document.querySelectorAll(".time-slot-button").forEach((btn) => {
      btn.classList.remove("bg-leihlokal-500", "text-white");
    });

    // Add selection to clicked button
    e.target.classList.add("bg-leihlokal-500", "text-white");

    // Store selection
    selectedTimeSlot = {
      day: e.target.dataset.day,
      time: e.target.dataset.time,
    };

    // Update display with date
    const targetDate = getNextWeekdayDate(selectedTimeSlot.day);
    document.getElementById("selectedTimeDisplay").textContent =
      `${weekSchedule[selectedTimeSlot.day].name}, ${formatDate(targetDate)}, ${selectedTimeSlot.time} Uhr`;
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
  const cart = document.querySelector(".border.border-black:nth-child(2)"); // Cart container
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
