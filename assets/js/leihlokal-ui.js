import api from "./leihlokal-core.js";

// DOM references — new markup
const productGrid = document.getElementById("productGrid");
const searchInput = document.getElementById("searchInput");
const cartBar = document.getElementById("cartBar");
const cartBarCount = document.getElementById("cartBarCount");
const cartPanel = document.getElementById("cartPanel");

// State
let currentPage = 1;
let currentCategory = "";
let currentSearchQuery = "";
let cart = api.cart;
let showOnlyAvailable = true;
let sortByNumber = false;
let expandedItemId = null; // Track which item detail is open

// Preserved helpers
function getCurrentSort() {
  return sortByNumber ? "iid" : "@random";
}

function formatIID(iid) {
  const num = String(iid).padStart(4, "0");
  return num.slice(0, 2) + "." + num.slice(2);
}

function escapeHTML(str) {
  if (!str) return "";
  return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

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

// ============================================================================
// STEP 2: Product card renderer (blueprint style)
// ============================================================================

function createProductCard(item) {
  const isAvailable = item.status === "instock" && !item.is_protected;

  return `
    <div class="ll-card ${item.is_protected ? 'protected' : ''} ${expandedItemId === item.id ? 'active' : ''}"
         data-item-id="${item.id}">
      <div class="ll-card-img">
        ${item.images?.[0]
          ? `<img src="${item.images[0].thumb}" alt="${escapeHTML(item.name)}" loading="lazy">`
          : ""}
      </div>
      <div class="ll-card-iid"><span class="ll-card-iid-a">${String(item.iid).padStart(4, "0").slice(0, 2)}</span><span class="ll-card-iid-b">${String(item.iid).padStart(4, "0").slice(2)}</span></div>
      <div class="ll-card-body">
        <div class="ll-card-title">${escapeHTML(item.name)}</div>
        <div class="ll-card-cat">${escapeHTML(item.category)}</div>
      </div>
      <div class="ll-card-footer" style="display:none;padding:0.4rem 0.6rem;border-top:var(--ll-border);justify-content:space-between;align-items:center;">
        <span style="font-weight:700;font-size:0.65rem;">${item.deposit ? item.deposit + " € Pfand" : ""}</span>
        ${isAvailable
          ? `<button class="ll-btn" style="background:var(--ll-black);color:white;padding:0.2rem 0.5rem;font-size:0.58rem;"
               data-action="add-to-cart" data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'>+ Korb</button>`
          : `<span class="ll-meta-label" style="color:oklch(60% 0 0);">${item.is_protected ? "Geschützt" : "Vergeben"}</span>`
        }
      </div>
    </div>
  `;
}

// ============================================================================
// STEP 3: Inline item detail — mobile strip and desktop 2×2
// ============================================================================

function createDetailElement(item, isMobile) {
  const isAvailable = item.status === "instock" && !item.is_protected;
  const paddedIid = String(item.iid).padStart(4, "0");
  const detailUrl = `/ll/${paddedIid}`;

  // Only show brand/model if they have real values (filter out "/", empty, whitespace-only)
  const cleanStr = (s) => s && s.trim() && s.trim() !== "/" ? s.trim() : "";
  const brandModel = [cleanStr(item.brand), cleanStr(item.model)].filter(Boolean).join(" — ");

  const statusLabel = isAvailable ? "VERFÜGBAR" : (item.is_protected ? "GESCHÜTZT" : "VERGEBEN");
  const statusColor = isAvailable ? "var(--ll-color)" : "#dc2626";

  const imgHTML = item.images?.[0]
    ? `<img src="${item.images[0].full}" alt="${escapeHTML(item.name)}" style="width:100%;height:100%;object-fit:cover;display:block;">`
    : '<div style="display:flex;align-items:center;justify-content:center;height:100%;background:oklch(95% 0 0);color:oklch(70% 0 0);font-size:0.7rem;text-transform:uppercase;">Kein Bild</div>';

  // L-shaped layout from pencil mockup:
  // CSS Grid with 2 cols, 2 rows. Image = top-left cell (78% x 78%).
  // Right strip = top-right cell. Bottom strip = spans full width.
  // Grid: [image 78%] [right-strip 22%]
  //        [bottom-strip full width     ]
  // L-shaped layout: image top-left, right strip, bottom strip.
  // No outer border — the product grid cell provides border-right and border-bottom.
  // Use fr units so right strip and bottom-right buttons align.
  const content = `
    <div style="display:grid;grid-template-columns:1fr auto;grid-template-rows:1fr auto;width:100%;height:100%;background:white;">

      <!-- Top-left: Image (forced square) -->
      <div style="aspect-ratio:1;overflow:hidden;background:oklch(95% 0 0);position:relative;">
        ${imgHTML}
        <button data-action="close-detail" style="position:absolute;top:0.4rem;left:0.4rem;z-index:2;background:rgba(255,255,255,0.9);border:2px solid var(--ll-color);width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;cursor:pointer;color:var(--ll-color);line-height:1;">×</button>
      </div>

      <!-- Top-right: IID stacked + status rotated -->
      <div style="display:flex;flex-direction:column;align-items:center;padding:0.5rem 0.4rem;border-left:2px solid var(--ll-color);gap:0.3rem;min-width:3.5rem;">
        <div style="font-family:monospace;font-weight:700;font-size:clamp(1.2rem, 2.5vw, 2rem);line-height:1;text-align:center;">
          <div style="background:var(--ll-color);color:white;padding:0.2rem 0.35rem;">${paddedIid.slice(0, 2)}</div>
          <div style="color:var(--ll-color);padding:0.2rem 0.35rem;border:2px solid var(--ll-color);border-top:none;">${paddedIid.slice(2)}</div>
        </div>
        <div style="flex:1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
          <div style="writing-mode:vertical-rl;transform:rotate(180deg);font-family:'Univers',sans-serif;font-size:clamp(0.9rem, 2vw, 1.6rem);font-weight:700;color:${statusColor};letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;">${statusLabel}</div>
        </div>
      </div>

      <!-- Bottom strip: spans both columns -->
      <div style="grid-column:1/-1;display:flex;align-items:stretch;border-top:2px solid var(--ll-color);">
        <div style="flex:1;padding:0.5rem 0.6rem;display:flex;flex-direction:column;justify-content:center;">
          <div style="font-family:'Univers',sans-serif;font-size:clamp(0.75rem, 1.5vw, 1rem);font-weight:700;">${escapeHTML(item.name)}</div>
          ${brandModel ? `<div style="font-size:0.7rem;color:oklch(50% 0 0);">${escapeHTML(brandModel)}</div>` : ""}
          ${item.deposit ? `<div style="font-size:0.65rem;color:oklch(40% 0 0);margin-top:0.1rem;">${item.deposit} € Pfand</div>` : ""}
        </div>
        ${isAvailable
          ? `<button class="ll-btn" data-action="add-to-cart" data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'
               style="background:var(--ll-color);color:white;padding:0.6rem 0.75rem;font-size:clamp(0.6rem, 1.2vw, 0.8rem);line-height:1.3;text-align:center;flex-shrink:0;border-left:2px solid var(--ll-color);">IN DEN<br>KORB</button>`
          : `<div style="background:oklch(50% 0 0);color:white;padding:0.6rem 0.75rem;font-size:0.6rem;font-weight:700;text-transform:uppercase;line-height:1.3;text-align:center;flex-shrink:0;border-left:2px solid var(--ll-color);display:flex;align-items:center;">${item.is_protected ? "GESCHÜTZT" : "VERGEBEN"}</div>`
        }
        <a href="${detailUrl}" class="ll-btn ll-btn-close" style="text-decoration:none;text-align:center;display:flex;align-items:center;padding:0.5rem;border-left:2px solid var(--ll-color);font-size:0.6rem;">DETAILS</a>
      </div>
    </div>
  `;

  const el = document.createElement("div");

  if (isMobile) {
    el.className = "ll-detail-strip";
    el.innerHTML = content;
  } else {
    el.className = "ll-detail-2x2";
    el.innerHTML = content;
  }

  el.dataset.detailFor = item.id;
  return el;
}

// ============================================================================
// STEP 4: Expand/collapse logic for item detail
// ============================================================================

let detailElement = null;
let originalCardHTML = null;
let originalCardIndex = null;

function isMobileView() {
  return window.innerWidth < 1024;
}

// FLIP animation: snapshot positions, mutate DOM, animate from old to new
function flipAnimate(container, mutationFn) {
  // First: snapshot current positions of all cards
  const children = Array.from(container.querySelectorAll(".ll-card"));
  const firstRects = new Map();
  children.forEach(el => {
    firstRects.set(el, el.getBoundingClientRect());
  });

  // Mutate the DOM
  mutationFn();

  // Last: get new positions and Invert+Play
  const remaining = Array.from(container.querySelectorAll(".ll-card"));
  remaining.forEach(el => {
    const first = firstRects.get(el);
    if (!first) return; // new element, skip
    const last = el.getBoundingClientRect();
    const dx = first.left - last.left;
    const dy = first.top - last.top;
    if (dx === 0 && dy === 0) return; // didn't move

    el.style.transform = `translate(${dx}px, ${dy}px)`;
    el.style.transition = "none";

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        el.style.transition = "transform 0.2s ease-out";
        el.style.transform = "";
        el.addEventListener("transitionend", function cleanup() {
          el.style.transition = "";
          el.removeEventListener("transitionend", cleanup);
        }, { once: true });
      });
    });
  });
}

async function expandItemDetail(itemId) {
  // Close existing detail first (no animation for the close, keeps it snappy)
  collapseItemDetail();

  const item = await api.getItem(itemId);
  expandedItemId = item.id;
  const mobile = isMobileView();

  if (mobile) {
    flipAnimate(productGrid, () => {
      const cards = Array.from(productGrid.children);
      const clickedCard = cards.find(c => c.dataset.itemId === itemId);
      if (!clickedCard) return;

      clickedCard.classList.add("active");
      const cardIndex = cards.indexOf(clickedCard);
      const columnsPerRow = 3;
      const lastCardInRow = Math.min(cardIndex + (columnsPerRow - (cardIndex % columnsPerRow)), cards.length) - 1;
      const insertAfter = cards[lastCardInRow];

      detailElement = createDetailElement(item, true);
      insertAfter.after(detailElement);
    });
    detailElement?.scrollIntoView({ behavior: "smooth", block: "nearest" });
  } else {
    flipAnimate(productGrid, () => {
      const cards = Array.from(productGrid.querySelectorAll(".ll-card, .ll-detail-2x2"));
      const clickedCard = cards.find(c => c.dataset?.itemId === itemId);
      if (!clickedCard) return;

      originalCardHTML = clickedCard.outerHTML;
      originalCardIndex = Array.from(productGrid.children).indexOf(clickedCard);

      detailElement = createDetailElement(item, false);
      clickedCard.replaceWith(detailElement);
    });
  }
}

function collapseItemDetail() {
  if (!detailElement) return;

  if (isMobileView()) {
    flipAnimate(productGrid, () => {
      detailElement.remove();
      productGrid.querySelectorAll(".ll-card.active").forEach(c => c.classList.remove("active"));
    });
  } else {
    flipAnimate(productGrid, () => {
      if (originalCardHTML) {
        const temp = document.createElement("div");
        temp.innerHTML = originalCardHTML;
        detailElement.replaceWith(temp.firstElementChild);
      } else {
        detailElement.remove();
      }
    });
  }

  detailElement = null;
  originalCardHTML = null;
  originalCardIndex = null;
  expandedItemId = null;
}

// ============================================================================
// STEP 5: Cart UI — sticky bar (mobile) + sidebar (desktop)
// ============================================================================

function updateCartUI() {
  const count = cart.items.length;
  const totalDeposit = cart.items.reduce((sum, item) => sum + (item.deposit || 0), 0);

  // Mobile: sticky cart bar
  if (cartBar) {
    cartBar.classList.toggle("visible", count > 0);
    cartBarCount.textContent = `Korb (${count})`;
  }

  // Desktop: sidebar cart
  const sidebarCart = document.getElementById("sidebarCartItems");
  const sidebarReserve = document.getElementById("sidebarReserve");
  const sidebarClear = document.getElementById("sidebarClearCart");

  if (sidebarCart) {
    if (count === 0) {
      sidebarCart.innerHTML = '<span style="color:oklch(60% 0 0);font-size:0.75rem;">Noch nichts drin.</span>';
      if (sidebarReserve) sidebarReserve.style.display = "none";
      if (sidebarClear) sidebarClear.style.display = "none";
    } else {
      sidebarCart.innerHTML = cart.items.map(item => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.35rem 0;border-bottom:1px solid oklch(60.62% 0.245 28.83 / 0.08);font-size:0.75rem;">
          <span>${formatIID(item.iid)} ${item.name}</span>
          <button onclick="removeFromCart('${item.id}')" style="background:none;border:none;color:var(--ll-color);cursor:pointer;font-weight:700;">×</button>
        </div>
      `).join("");
      if (sidebarReserve) sidebarReserve.style.display = "block";
      if (sidebarClear) sidebarClear.style.display = "block";
    }
  }

  // Cart panel contents (for reservation flow)
  const panelItems = document.getElementById("panelCartItems");
  const panelTotal = document.getElementById("panelTotalDeposit");
  if (panelItems) {
    panelItems.innerHTML = cart.items.map(item => `
      <div style="display:flex;justify-content:space-between;font-size:0.85rem;padding:0.3rem 0;border-bottom:var(--ll-border);">
        <span>${formatIID(item.iid)} ${item.name}</span>
        <span style="font-weight:700;">${(item.deposit || 0).toFixed(2)} €</span>
      </div>
    `).join("");
  }
  if (panelTotal) {
    panelTotal.textContent = `Gesamtpfand: ${totalDeposit.toFixed(2).replace(".", ",")} €`;
  }
}

window.addToCart = function(item) {
  cart.addItem(item);
  updateCartUI();
  // Micro-feedback: bump the cart count
  if (cartBarCount) {
    cartBarCount.classList.add("bump");
    setTimeout(() => cartBarCount.classList.remove("bump"), 150);
  }
};

window.removeFromCart = function(itemId) {
  cart.removeItem(itemId);
  updateCartUI();
};

// ============================================================================
// STEP 6: Category filter, search, toggle, and pagination logic
// ============================================================================

// Category filtering — works for both chips (mobile) and sidebar items (desktop)
async function filterByCategory(category) {
  currentCategory = category;
  currentSearchQuery = "";
  searchInput.value = "";

  document.querySelectorAll(".category-filter").forEach(el => {
    el.dataset.active = (el.dataset.category === category).toString();
  });

  try {
    let filter = [];
    if (category) filter.push(`category ~ "${category}"`);
    if (showOnlyAvailable) filter.push('status = "instock"');
    const response = await api.getItems(1, filter.join(" && "), getCurrentSort());
    renderItems(response.items);
    currentPage = 1;
    updatePagination(response.totalPages);
  } catch (error) {
    console.error("Failed to filter:", error);
  }
}

// Render items to grid (replace)
function renderItems(items) {
  collapseItemDetail();
  productGrid.innerHTML = items.map(item => createProductCard(item)).join("");
}

// Append items to grid (for mobile "load more")
function appendItems(items) {
  collapseItemDetail();
  productGrid.insertAdjacentHTML("beforeend", items.map(item => createProductCard(item)).join(""));
}

// Search
async function performSearch(query, page = 1) {
  const results = await api.searchItems(query, page, getCurrentSort());
  const filtered = showOnlyAvailable
    ? { items: results.items.filter(i => i.status === "instock"), totalPages: results.totalPages }
    : results;
  renderItems(filtered.items);
  currentPage = page;
  updatePagination(results.totalPages);
}

// Load items (browse mode)
window.loadItems = async function(page = 1) {
  currentSearchQuery = "";
  try {
    let filter = [];
    if (currentCategory) filter.push(`category ~ "${currentCategory}"`);
    if (showOnlyAvailable) filter.push('status = "instock"');
    const response = await api.getItems(page, filter.join(" && "), getCurrentSort());
    renderItems(response.items);
    currentPage = page;
    updatePagination(response.totalPages);
  } catch (error) {
    console.error("Failed to load items:", error);
  }
};

window.goToPage = async function(page) {
  if (currentSearchQuery) {
    await performSearch(currentSearchQuery, page);
  } else {
    await loadItems(page);
  }
};

// Pagination UI
function updatePagination(totalPages) {
  // Mobile: load more button
  const loadMoreBtn = document.getElementById("loadMore");
  if (loadMoreBtn) {
    loadMoreBtn.style.display = currentPage < totalPages ? "" : "none";
  }

  // Desktop: page numbers (truncated)
  const pageNumbers = document.getElementById("pageNumbers");
  if (pageNumbers) {
    const buttons = [];
    const pages = [];

    // Always show: first, last, current, and 1 neighbor on each side
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
        pages.push(i);
      }
    }

    let prev = 0;
    for (const p of pages) {
      if (p - prev > 1) {
        buttons.push(`<span class="ll-btn ll-page-btn" style="cursor:default;border:none;">…</span>`);
      }
      buttons.push(`<button class="ll-btn ll-page-btn ${p === currentPage ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`);
      prev = p;
    }

    pageNumbers.innerHTML = buttons.join("");
  }
}

// ============================================================================
// STEP 7: Event listeners and initialization
// ============================================================================

// Category filters (both mobile chips and desktop sidebar)
document.querySelectorAll(".category-filter").forEach(el => {
  el.addEventListener("click", () => filterByCategory(el.dataset.category));
});

// Search
searchInput?.addEventListener("input", debounce(async (e) => {
  const query = e.target.value.trim();
  currentSearchQuery = query;
  if (query) {
    await performSearch(query);
  } else {
    loadItems(1);
  }
}, 300));

// Square toggles
document.getElementById("availableToggle")?.addEventListener("change", async function(e) {
  showOnlyAvailable = e.target.checked;
  document.getElementById("availableToggleBox")?.classList.toggle("active", e.target.checked);
  if (currentSearchQuery) await performSearch(currentSearchQuery);
  else loadItems(1);
});

document.getElementById("sortToggle")?.addEventListener("change", async function(e) {
  sortByNumber = e.target.checked;
  document.getElementById("sortToggleBox")?.classList.toggle("active", e.target.checked);
  if (currentSearchQuery) await performSearch(currentSearchQuery);
  else loadItems(1);
});

// Load more (mobile pagination — appends instead of replacing)
document.getElementById("loadMore")?.addEventListener("click", async () => {
  const nextPage = currentPage + 1;
  try {
    let filter = [];
    if (currentCategory) filter.push(`category ~ "${currentCategory}"`);
    if (showOnlyAvailable) filter.push('status = "instock"');

    let response;
    if (currentSearchQuery) {
      response = await api.searchItems(currentSearchQuery, nextPage, getCurrentSort());
      if (showOnlyAvailable) {
        response = { items: response.items.filter(i => i.status === "instock"), totalPages: response.totalPages };
      }
    } else {
      response = await api.getItems(nextPage, filter.join(" && "), getCurrentSort());
    }

    appendItems(response.items);
    currentPage = nextPage;
    updatePagination(response.totalPages);
  } catch (error) {
    console.error("Failed to load more items:", error);
  }
});

// Delegated click handler for product grid
document.addEventListener("click", function(e) {
  // Card click → expand detail
  const card = e.target.closest(".ll-card");
  if (card && !e.target.closest('[data-action]')) {
    expandItemDetail(card.dataset.itemId);
    return;
  }

  // Add to cart
  if (e.target.closest('[data-action="add-to-cart"]')) {
    const btn = e.target.closest('[data-action="add-to-cart"]');
    const itemData = JSON.parse(btn.dataset.item);
    addToCart(itemData);
    return;
  }

  // Close detail
  if (e.target.closest('[data-action="close-detail"]')) {
    collapseItemDetail();
    return;
  }
});

// Cart bar → open panel (mobile)
document.getElementById("cartBarAction")?.addEventListener("click", () => {
  cartPanel?.classList.add("open");
});

// Close panel
document.getElementById("closeCartPanel")?.addEventListener("click", () => {
  cartPanel?.classList.remove("open");
});

// Click outside cart panel to close (desktop)
document.addEventListener("click", function(e) {
  if (cartPanel?.classList.contains("open") && !cartPanel.contains(e.target) &&
      !e.target.closest("#cartBarAction") && !e.target.closest("#sidebarReserve")) {
    cartPanel.classList.remove("open");
  }
});

// Sidebar reserve button → open panel (desktop)
document.getElementById("sidebarReserve")?.addEventListener("click", () => {
  cartPanel?.classList.add("open");
});

// Clear cart
document.getElementById("sidebarClearCart")?.addEventListener("click", () => {
  cart.clearCart();
  updateCartUI();
});

// Subscribe to cart updates
cart.subscribe(updateCartUI);
updateCartUI();

// ============================================================================
// STEP 8: Reservation form submission
// ============================================================================

// Day selection
let selectedPickupDay = null;

document.querySelectorAll(".day-option").forEach(btn => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".day-option").forEach(b => {
      b.style.background = "white";
      b.style.color = "var(--ll-black)";
      b.style.borderColor = "var(--ll-color-20)";
    });
    btn.style.background = "var(--ll-color)";
    btn.style.color = "white";
    btn.style.borderColor = "var(--ll-color)";
    selectedPickupDay = {
      dateISO: btn.dataset.dateIso,
      time: btn.dataset.middleTime,
    };
  });
});

// Form submission — preserved logic
document.getElementById("reservationForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = e.target.querySelector('input[name="email"]').value.trim();
  if (!email) { alert("Bitte gib deine Email-Adresse ein"); return; }
  if (!selectedPickupDay) { alert("Bitte wähle einen Abholtermin aus"); return; }

  const reservedItems = [...cart.items];
  const submitBtn = document.getElementById("submitReservationBtn");
  submitBtn.disabled = true;
  submitBtn.textContent = "Wird verarbeitet...";

  try {
    const [year, month, day] = selectedPickupDay.dateISO.split("-").map(Number);
    const [hours, minutes] = selectedPickupDay.time.split(":").map(Number);
    const pickupDate = new Date(year, month - 1, day, hours, minutes, 0, 0);

    const reservationData = {
      customer_email: email,
      items: cart.items.map(item => item.id),
      pickup: pickupDate.toISOString(),
      comments: "",
      is_new_customer: true,
      done: false,
    };

    const record = await api.submitReservation(reservationData);
    showReservationSuccess(record, reservationData, reservedItems);
    cart.clearCart();
    updateCartUI();
  } catch (error) {
    console.error("Reservation failed:", error);
    submitBtn.style.background = "#dc2626";
    submitBtn.textContent = "Fehler beim Reservieren";
    setTimeout(() => {
      submitBtn.disabled = false;
      submitBtn.style.background = "";
      submitBtn.textContent = "Jetzt reservieren →";
    }, 3000);
  }
});

// ============================================================================
// STEP 9: Success display + ICS/share
// ============================================================================

function showReservationSuccess(record, reservationData, reservedItems) {
  document.getElementById("reservationForm").style.display = "none";
  const successEl = document.getElementById("successDisplay");
  successEl.style.display = "";

  if (record.otp) {
    document.getElementById("otpNumber").textContent = record.otp;
  }

  const pickupDate = new Date(reservationData.pickup);
  const formattedDate = pickupDate.toLocaleDateString("de-DE", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
  const formattedTime = pickupDate.toLocaleTimeString("de-DE", { hour: "2-digit", minute: "2-digit" });
  const totalDeposit = reservedItems.reduce((sum, item) => sum + (item.deposit || 0), 0);

  document.getElementById("reservationSummary").innerHTML = `
    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:var(--ll-border);">
      <span style="font-weight:700;">Abholtermin:</span>
      <span>${formattedDate}, ${formattedTime} Uhr</span>
    </div>
    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:var(--ll-border);">
      <span style="font-weight:700;">Email:</span>
      <span>${reservationData.customer_email}</span>
    </div>
    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:var(--ll-border);">
      <span style="font-weight:700;">Artikel:</span>
      <span>${reservationData.items.length} Stück</span>
    </div>
    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;font-weight:700;font-size:1rem;">
      <span>Gesamtpfand:</span>
      <span style="color:var(--ll-color);">${totalDeposit.toFixed(2).replace(".", ",")} €</span>
    </div>
  `;

  // Calendar button
  const calendarBtn = document.getElementById("addToCalendarBtn");
  const newCalendarBtn = calendarBtn.cloneNode(true);
  calendarBtn.parentNode.replaceChild(newCalendarBtn, calendarBtn);
  newCalendarBtn.addEventListener("click", () => {
    generateICS(reservationData.pickup, reservedItems, record.otp);
  });

  // Share button
  const shareBtn = document.getElementById("shareReservationBtn");
  const newShareBtn = shareBtn.cloneNode(true);
  shareBtn.parentNode.replaceChild(newShareBtn, shareBtn);
  newShareBtn.addEventListener("click", () => {
    const text = `Meine Reservierung bei leih.lokal Karlsruhe\nAbholcode: ${record.otp}\nAbholung: ${formattedDate}, ${formattedTime} Uhr`;
    if (navigator.share) {
      navigator.share({ title: "leih.lokal Reservierung", text });
    } else {
      navigator.clipboard.writeText(text);
      alert("In Zwischenablage kopiert!");
    }
  });
}

// ICS generation — preserved verbatim
function generateICS(pickup, items, otp = null) {
  const event = {
    start: new Date(pickup),
    end: new Date(new Date(pickup).getTime() + 30 * 60000),
    title: "leih.lokal Abholung",
    location: "leih.lokal Karlsruhe, Gerwigstraße 41, 76131 Karlsruhe",
  };

  let descriptionLines = ["Abholung deiner reservierten Artikel:"];
  if (otp) { descriptionLines.push("", `ABHOLCODE: ${otp}`, ""); }
  descriptionLines.push("", "Reservierte Artikel:");
  items.forEach(item => descriptionLines.push(`- ${formatIID(item.iid)} ${item.name}`));

  const description = descriptionLines.join("\\n").replace(/\\/g, "\\\\").replace(/;/g, "\\;").replace(/,/g, "\\,");

  const icsContent = [
    "BEGIN:VCALENDAR", "VERSION:2.0", "BEGIN:VEVENT",
    `DTSTART:${event.start.toISOString().replace(/[-:]/g, "").replace(/\.\d{3}/, "")}`,
    `DTEND:${event.end.toISOString().replace(/[-:]/g, "").replace(/\.\d{3}/, "")}`,
    `SUMMARY:${event.title}`, `DESCRIPTION:${description}`, `LOCATION:${event.location}`,
    "END:VEVENT", "END:VCALENDAR"
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

// Close success → reload
document.getElementById("closeSuccessBtn")?.addEventListener("click", () => {
  window.location.reload();
});

// ============================================================================
// STEP 10: App initialization
// ============================================================================

async function initializeApp() {
  try {
    await api.initialize();
    await loadItems(1);
  } catch (error) {
    console.error("Failed to initialize:", error);
  }
}

initializeApp();
