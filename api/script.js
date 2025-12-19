const API_URL = "http://localhost:3000/api";
const PHP_API_URL = "http://localhost/trvlr";
let bookingModal = null;
let authModal = null;
let editBookingModal = null;
const booking = { hotel: "", price: 0, image: "", meta: "", hotelId: "" };

// Search
const searchForm = document.getElementById("hotelSearchForm");
const searchInput = document.getElementById("hotelSearchInput");
const resultsGrid = document.getElementById("hotelResultsGrid");
const statusText = document.getElementById("hotelResultsStatus");

// Booking Form
const bookingForm = document.getElementById("bookingForm");
const hotelInput = document.getElementById("bookingHotel");
const startDateInput = document.getElementById("bookingStartDate");
const endDateInput = document.getElementById("bookingEndDate");
const guestsInput = document.getElementById("bookingGuests");
const priceDisplay = document.getElementById("bookingPriceValue");
const metaDisplay = document.getElementById("bookingMeta");
const hotelImage = document.getElementById("bookingHotelImage");
const submitBtn = document.querySelector('#bookingForm button[type="submit"]');

// Bookings Sidebar
const bookingsToggleBtn = document.getElementById("bookingsToggle");
const bookingsSidebar = document.getElementById("bookingsSidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const closeSidebarBtn = document.getElementById("closeSidebar");
const bookingsContent = document.getElementById("bookingsContent");

// Modals
const bookingEl = document.getElementById("bookingModal");
const authEl = document.getElementById("authRequiredModal");
const editBookingEl = document.getElementById("editBookingModal");

if (bookingEl) bookingModal = new bootstrap.Modal(bookingEl);
if (authEl) authModal = new bootstrap.Modal(authEl);
if (editBookingEl) editBookingModal = new bootstrap.Modal(editBookingEl);

// Edit Booking Form
const editBookingForm = document.getElementById("editBookingForm");
const editBookingIdInput = document.getElementById("editBookingId");
const editBookingHotelInput = document.getElementById("editBookingHotel");
const editStartDateInput = document.getElementById("editStartDate");
const editEndDateInput = document.getElementById("editEndDate");
const editGuestsInput = document.getElementById("editGuests");

// Event Listeners
searchForm?.addEventListener("submit", (e) => {
  e.preventDefault();
  const query = searchInput?.value || "manila";
  loadHotels(query);
});

bookingForm?.addEventListener("submit", handleBooking);
bookingsToggleBtn?.addEventListener("click", openBookingsSidebar);
closeSidebarBtn?.addEventListener("click", closeBookingsSidebar);
sidebarOverlay?.addEventListener("click", closeBookingsSidebar);
editBookingForm?.addEventListener("submit", handleEditBooking);

// Load initial hotels
loadHotels("manila");
checkUserSession();

// Load hotels
async function loadHotels(location) {
  statusText.textContent = "Searching for hotels...";
  resultsGrid.innerHTML = "";

  try {
    const res = await fetch(`${API_URL}/hotels?location=${encodeURIComponent(location)}`);
    const data = await res.json();

    if (!data.hotels?.length) {
      statusText.textContent = `No hotels found for ${data.location}.`;
      return;
    }

    resultsGrid.innerHTML = data.hotels.map(createHotelCard).join("");
    attachCardListeners(resultsGrid);
    statusText.textContent = `Showing ${data.count} stays for ${data.location}.`;
  } catch (error) {
    statusText.textContent = "Error fetching hotels.";
    console.error(error);
  }
}

// Create hotel card
function createHotelCard(hotel) {
  const data = {
    id: hotel.id || "",
    name: encodeURIComponent(hotel.name),
    price: hotel.priceAmount || "",
    rating: hotel.rating,
    reviews: encodeURIComponent(hotel.reviewLabel),
    accommodation: encodeURIComponent(hotel.accommodation),
    image: encodeURIComponent(hotel.image),
  };

  return `
    <div class="card h-100 shadow-sm hotel-card" data-hotel='${JSON.stringify(data)}'>
      <img src="${hotel.image}" class="card-img-top" alt="${hotel.name}" style="height: 200px; object-fit: cover;">
      <div class="card-body">
        <h5 class="card-title">${hotel.name}</h5>
        <p class="card-text">
          <span class="rating-badge">
            <svg class="rating-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            ${hotel.rating}
          </span>
          <small class="text-muted">${hotel.reviewLabel}</small>
        </p>
        <p class="fw-bold text-primary mb-2">${hotel.price}</p>
        <button class="btn btn-dark w-100 book-btn">Book Now</button>
      </div>
    </div>
  `;
}

// Attach card listeners
function attachCardListeners(container) {
  container.querySelectorAll(".book-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const card = e.target.closest(".hotel-card");
      const data = JSON.parse(card.dataset.hotel);
      openBookingModal(data);
    });
  });
}

// Open booking modal
function openBookingModal(data) {
  booking.hotel = decodeURIComponent(data.name);
  booking.price = parseInt(data.price) || 0;
  booking.image = decodeURIComponent(data.image);
  booking.hotelId = data.id;
  booking.meta = `${decodeURIComponent(data.accommodation)} · ${data.rating}★`;

  hotelInput.value = booking.hotel;
  priceDisplay.textContent = `₱${booking.price.toLocaleString()}`;
  metaDisplay.textContent = booking.meta;
  hotelImage.src = booking.image;

  // Set default dates
  const today = new Date();
  const checkin = new Date(today.setDate(today.getDate() + 7));
  const checkout = new Date(today.setDate(today.getDate() + 2));
  
  startDateInput.value = checkin.toISOString().split("T")[0];
  endDateInput.value = checkout.toISOString().split("T")[0];
  guestsInput.value = 2;

  if (bookingModal) bookingModal.show();
}

// Handle booking
async function handleBooking(e) {
  e.preventDefault();

  // Check if user is logged in
  const userId = getCookie("user_id");
  if (!userId) {
    if (authModal) authModal.show();
    return;
  }

  const startDate = startDateInput.value;
  const endDate = endDateInput.value;
  const guests = guestsInput.value;

  const start = new Date(startDate);
  const end = new Date(endDate);
  const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
  const total = booking.price * nights;

  submitBtn.disabled = true;
  submitBtn.textContent = "Processing...";

  try {
    // Save booking to database
    const formData = new FormData();
    formData.append("save_booking", "1");
    formData.append("user_id", userId);
    formData.append("hotel_id", booking.hotelId);
    formData.append("hotel_name", booking.hotel);
    formData.append("check_in", startDate);
    formData.append("check_out", endDate);
    formData.append("guests", guests);
    formData.append("price_per_night", booking.price);
    formData.append("total_price", total);
    formData.append("hotel_image", booking.image);
    formData.append("status", "Confirmed");

    const response = await fetch(`${PHP_API_URL}/save-booking.php`, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert("Booking confirmed successfully!");
      if (bookingModal) bookingModal.hide();
      loadUserBookings();
    } else {
      alert("Error: " + result.message);
    }
  } catch (error) {
    console.error("Booking error:", error);
    alert("Failed to process booking");
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = "Confirm Booking";
  }
}

// Open bookings sidebar
function openBookingsSidebar() {
  const userId = getCookie("user_id");
  if (!userId) {
    if (authModal) authModal.show();
    return;
  }

  bookingsSidebar?.classList.add("active");
  sidebarOverlay?.classList.add("active");
  loadUserBookings();
}

// Close bookings sidebar
function closeBookingsSidebar() {
  bookingsSidebar?.classList.remove("active");
  sidebarOverlay?.classList.remove("active");
}

// Load user bookings
async function loadUserBookings() {
  const userId = getCookie("user_id");
  if (!userId || !bookingsContent) return;

  bookingsContent.innerHTML = '<p class="text-center">Loading bookings...</p>';

  try {
    const response = await fetch(`${PHP_API_URL}/get-bookings.php?user_id=${userId}`);
    const data = await response.json();

    if (data.success && data.bookings.length > 0) {
      bookingsContent.innerHTML = data.bookings.map(createBookingCard).join("");
      attachBookingListeners();
    } else {
      bookingsContent.innerHTML = `
        <div class="empty-bookings">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          </svg>
          <h4>No bookings yet</h4>
          <p>Start exploring and book your first stay</p>
        </div>
      `;
    }
  } catch (error) {
    console.error("Error loading bookings:", error);
    bookingsContent.innerHTML = '<p class="text-danger text-center">Failed to load bookings</p>';
  }
}

// Create booking card
function createBookingCard(booking) {
  return `
    <div class="booking-item">
      <img src="${booking.hotel_image}" alt="${booking.hotel_name}" class="booking-item-image">
      <h5 class="booking-item-title">${booking.hotel_name}</h5>
      <div class="booking-item-details">
        <div class="booking-item-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          ${booking.check_in} to ${booking.check_out}
        </div>
        <div class="booking-item-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          ${booking.guests} guest(s)
        </div>
      </div>
      <div class="booking-item-price">₱${parseInt(booking.total_price).toLocaleString()}</div>
      <span class="booking-item-status booking-status-${booking.status.toLowerCase()}">${booking.status}</span>
      <div class="booking-item-actions">
        <button class="btn btn-edit edit-booking-btn" data-booking-id="${booking.id}">Edit</button>
        <button class="btn btn-delete delete-booking-btn" data-booking-id="${booking.id}">Cancel</button>
      </div>
    </div>
  `;
}

// Attach booking listeners
function attachBookingListeners() {
  document.querySelectorAll(".edit-booking-btn").forEach((btn) => {
    btn.addEventListener("click", handleEditBookingClick);
  });

  document.querySelectorAll(".delete-booking-btn").forEach((btn) => {
    btn.addEventListener("click", handleDeleteBooking);
  });
}

// Handle edit booking
function handleEditBookingClick(e) {
  const bookingId = e.target.dataset.bookingId;
  // Load booking data and show edit modal
  editBookingIdInput.value = bookingId;
  if (editBookingModal) editBookingModal.show();
}

// Handle edit booking form submit
async function handleEditBooking(e) {
  e.preventDefault();
  const bookingId = editBookingIdInput.value;
  const startDate = editStartDateInput.value;
  const endDate = editEndDateInput.value;
  const guests = editGuestsInput.value;

  try {
    const formData = new FormData();
    formData.append("update_booking", "1");
    formData.append("booking_id", bookingId);
    formData.append("check_in", startDate);
    formData.append("check_out", endDate);
    formData.append("guests", guests);

    const response = await fetch(`${PHP_API_URL}/update-booking.php`, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert("Booking updated successfully!");
      if (editBookingModal) editBookingModal.hide();
      loadUserBookings();
    } else {
      alert("Error: " + result.message);
    }
  } catch (error) {
    console.error("Update error:", error);
    alert("Failed to update booking");
  }
}

// Handle delete booking
async function handleDeleteBooking(e) {
  if (!confirm("Are you sure you want to cancel this booking?")) return;

  const bookingId = e.target.dataset.bookingId;

  try {
    const formData = new FormData();
    formData.append("cancel_booking", "1");
    formData.append("booking_id", bookingId);

    const response = await fetch(`${PHP_API_URL}/cancel-booking.php`, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert("Booking cancelled successfully!");
      loadUserBookings();
    } else {
      alert("Error: " + result.message);
    }
  } catch (error) {
    console.error("Cancel error:", error);
    alert("Failed to cancel booking");
  }
}

// Check user session
function checkUserSession() {
  const userId = getCookie("user_id");
  const userName = getCookie("user_name");
  
  const loginBtn = document.getElementById("loginButton");
  const logoutBtn = document.getElementById("logoutButton");
  const userBtn = document.getElementById("userButton");
  const userAvatar = document.getElementById("userAvatar");

  if (userId && userName) {
    loginBtn?.classList.add("d-none");
    logoutBtn?.classList.remove("d-none");
    userBtn?.classList.remove("d-none");
    if (userAvatar) userAvatar.textContent = userName.charAt(0).toUpperCase();
  } else {
    loginBtn?.classList.remove("d-none");
    logoutBtn?.classList.add("d-none");
    userBtn?.classList.add("d-none");
  }
}

// Cookie helper
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
}

// Logout
document.getElementById("logoutButton")?.addEventListener("click", () => {
  document.cookie = "user_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  document.cookie = "user_name=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  location.reload();
});



