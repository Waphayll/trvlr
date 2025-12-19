// API Configuration
const API_URL = "http://localhost:3000";
const PHP_API_URL = "http://localhost/web_dev_project";

// Global variables
let currentHotelRates = [];
let bookingModal = null;
let authModal = null;

// Initialize modals
window.addEventListener('load', function() {
    const bookingEl = document.getElementById('bookingModal');
    const authEl = document.getElementById('authRequiredModal');
    
    if (bookingEl) bookingModal = new bootstrap.Modal(bookingEl);
    if (authEl) authModal = new bootstrap.Modal(authEl);
    
    // Load default hotels
    searchHotels('manila');
});


// ==================== SEARCH HOTELS ====================
async function searchHotels(city) {
    const stayRowMain = document.getElementById('stayRowMain');
    const stayRowMore = document.getElementById('stayRowMore');
    const cityName = document.getElementById('cityName');
    const alertContainer = document.getElementById('alertContainer');
    const showMoreContainer = document.getElementById('showMoreContainer');
    
    // Show loading
    stayRowMain.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Searching for hotels in ${city}...</p>
        </div>
    `;
    stayRowMore.innerHTML = '';
    if (alertContainer) alertContainer.innerHTML = '';
    if (showMoreContainer) showMoreContainer.style.display = 'none';
    
    try {
        const url = `${API_URL}/search-hotels?` + new URLSearchParams({
            checkin: getDateString(7),
            checkout: getDateString(9),
            adults: 2,
            city: city,
            countryCode: 'PH',
            environment: 'sandbox'
        });
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.rates && data.rates.length > 0) {
            if (cityName) cityName.textContent = city.charAt(0).toUpperCase() + city.slice(1);
            displayHotels(data.rates);
            loadPlaces(city);
        } else {
            stayRowMain.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                    <p class="mt-3">No hotels found in ${city}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        stayRowMain.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">Error loading hotels. Please try again.</p>
            </div>
        `;
    }
}


// ==================== LOAD PLACES ====================
async function loadPlaces(city) {
    try {
        const url = `${API_URL}/search-places?` + new URLSearchParams({
            cityName: city,
            countryCode: 'PH',
            environment: 'sandbox'
        });
        
        const response = await fetch(url);
        const data = await response.json();
        
        const exploreContent = document.getElementById('exploreContent');
        if (!exploreContent) return;
        
        if (data.places && data.places.length > 0) {
            displayPlaces(data.places, city);
        } else {
            exploreContent.innerHTML = `
                <h3>Explore ${city.charAt(0).toUpperCase() + city.slice(1)}</h3>
                <p>Discover amazing places in this beautiful city.</p>
            `;
        }
    } catch (error) {
        console.error('Error loading places:', error);
    }
}


// ==================== DISPLAY PLACES ====================
function displayPlaces(places, city) {
    const exploreSection = document.getElementById('exploreContent');
    if (!exploreSection) return;
    
    if (places.length === 0) {
        exploreSection.innerHTML = `
            <h3>Explore ${city.charAt(0).toUpperCase() + city.slice(1)}</h3>
            <p>Discover this wonderful destination.</p>
        `;
        return;
    }
    
    const place = places[0];
    const imageUrl = place.images && place.images.length > 0 ? place.images[0] : 'https://via.placeholder.com/600x500?text=Place';
    
    exploreSection.innerHTML = `
        <h3>Explore ${city.charAt(0).toUpperCase() + city.slice(1)}</h3>
        <h5 class="mt-3">${place.name || 'Featured Place'}</h5>
        <p class="text-muted">${truncateText(place.description || 'Discover amazing places in this city.', 200)}</p>
        <h6 class="mt-4">Popular Places:</h6>
        <ul class="list-unstyled mt-3">
            ${places.slice(0, 5).map(p => `<li class="mb-2"><i class="bi bi-geo-alt-fill text-primary"></i> ${p.name}</li>`).join('')}
        </ul>
    `;
    
    const exploreImage = document.getElementById('exploreImage');
    if (exploreImage) {
        exploreImage.style.backgroundImage = `url('${imageUrl}')`;
        exploreImage.style.backgroundSize = 'cover';
        exploreImage.style.backgroundPosition = 'center';
    }
}


// ==================== DISPLAY HOTELS ====================
function displayHotels(rates) {
    const stayRowMain = document.getElementById('stayRowMain');
    const stayRowMore = document.getElementById('stayRowMore');
    const showMoreContainer = document.getElementById('showMoreContainer');
    
    stayRowMain.innerHTML = '';
    stayRowMore.innerHTML = '';
    
    currentHotelRates = rates;
    
    // Display first 4 hotels
    rates.slice(0, 4).forEach((rate, index) => {
        stayRowMain.innerHTML += createHotelCard(rate, index);
    });
    
    // Display next 4 hotels
    if (rates.length > 4) {
        rates.slice(4, 8).forEach((rate, index) => {
            stayRowMore.innerHTML += createHotelCard(rate, index + 4);
        });
        if (showMoreContainer) showMoreContainer.style.display = 'block';
    }
    
    // Attach click listeners
    attachCardListeners();
}


// ==================== CREATE HOTEL CARD ====================
function createHotelCard(rate, index) {
    if (!rate.hotel || !rate.roomTypes[0] || !rate.roomTypes[0].rates[0]) {
        return '';
    }
    
    const hotel = rate.hotel;
    const minRate = rate.roomTypes[0].rates[0];
    const price = minRate.retailRate.total[0].amount;
    const currency = minRate.retailRate.total[0].currency;
    
    const imageUrl = hotel.main_photo || hotel.thumbnail || 'https://via.placeholder.com/400x300?text=Hotel';
    const description = hotel.hotelDescription ? extractHeadline(hotel.hotelDescription) : 'Great accommodation';
    const stars = hotel.stars ? '⭐'.repeat(hotel.stars) : '';
    const address = hotel.address || hotel.city || '';
    
    return `
        <div class="col-md-3 mb-3">
            <div class="card h-100 hotel-card" 
                 data-rate-index="${index}"
                 data-hotel="${encodeURIComponent(hotel.name)}"
                 data-price="${price}"
                 data-currency="${currency}"
                 data-image="${encodeURIComponent(imageUrl)}"
                 data-meta="${encodeURIComponent(stars + ' • ' + address)}"
                 data-address="${encodeURIComponent(address)}"
                 data-stars="${hotel.stars || 0}"
                 style="cursor: pointer;">
                <div class="card-img-top" style="height: 200px; background-image: url('${imageUrl}'); background-size: cover; background-position: center;">
                </div>
                <div class="card-body">
                    <h6 class="card-title">${hotel.name}</h6>
                    ${stars ? `<p class="mb-1"><small>${stars}</small></p>` : ''}
                    <p class="card-text"><small class="text-muted">${truncateText(description, 80)}</small></p>
                    <p class="mb-0"><small><i class="bi bi-geo-alt"></i> ${address}</small></p>
                </div>
                <div class="card-footer">
                    <p class="mb-0 text-primary"><strong>$${parseFloat(price).toFixed(2)}</strong> <small>${currency}/night</small></p>
                </div>
            </div>
        </div>
    `;
}


// ==================== ATTACH CARD LISTENERS ====================
function attachCardListeners() {
    document.querySelectorAll(".hotel-card")?.forEach((card) => {
        card.addEventListener("click", function () {
            const rateIndex = parseInt(this.dataset.rateIndex);
            const hotelName = decodeURIComponent(this.dataset.hotel);
            const hotelPrice = parseFloat(this.dataset.price);
            const hotelCurrency = this.dataset.currency;
            const hotelImage = decodeURIComponent(this.dataset.image);
            const hotelMeta = decodeURIComponent(this.dataset.meta);
            const hotelAddress = decodeURIComponent(this.dataset.address);
            const hotelStars = parseInt(this.dataset.stars);
            
            openBookingModal(hotelName, hotelPrice, hotelCurrency, hotelImage, hotelMeta, hotelAddress, hotelStars, rateIndex);
        });
    });
}


// ==================== OPEN BOOKING MODAL ====================
function openBookingModal(name, price, currency, image, meta, address, stars, rateIndex) {
    // Optional: Check if user is logged in
    // if (!userLoggedIn) {
    //     authModal?.show();
    //     return;
    // }
    
    // Store booking data
    const bookingData = {
        hotel: name,
        price: price,
        currency: currency,
        image: image,
        meta: meta,
        address: address,
        stars: stars,
        rateIndex: rateIndex,
        fullRateData: currentHotelRates[rateIndex]
    };
    
    localStorage.setItem('currentBooking', JSON.stringify(bookingData));
    
    // Populate modal
    const hotelInput = document.getElementById('bookingHotel');
    const priceDisplay = document.getElementById('bookingPriceValue');
    const metaDisplay = document.getElementById('bookingMeta');
    const hotelImage = document.getElementById('bookingHotelImage');
    const startDateInput = document.getElementById('bookingStartDate');
    const endDateInput = document.getElementById('bookingEndDate');
    
    if (hotelInput) hotelInput.value = name;
    if (priceDisplay) priceDisplay.textContent = `$${price.toFixed(2)} ${currency}`;
    if (metaDisplay) metaDisplay.textContent = meta;
    if (hotelImage) hotelImage.src = image;
    
    // Set default dates
    if (startDateInput) startDateInput.value = getDateString(7);
    if (endDateInput) endDateInput.value = getDateString(9);
    
    // Show modal
    bookingModal?.show();
    
    // Update total price when dates change
    updateTotalPrice();
}


// ==================== UPDATE TOTAL PRICE ====================
function updateTotalPrice() {
    const startDateInput = document.getElementById('bookingStartDate');
    const endDateInput = document.getElementById('bookingEndDate');
    const totalDisplay = document.getElementById('bookingTotalValue');
    
    if (!startDateInput || !endDateInput || !totalDisplay) return;
    
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (startDate && endDate && endDate > startDate) {
        const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
        const bookingData = JSON.parse(localStorage.getItem('currentBooking'));
        const total = bookingData.price * nights;
        
        totalDisplay.textContent = `$${total.toFixed(2)} ${bookingData.currency} (${nights} night${nights > 1 ? 's' : ''})`;
    }
}


// ==================== HANDLE BOOKING SUBMISSION ====================
async function handleBooking(e) {
    e.preventDefault();
    
    const bookingData = JSON.parse(localStorage.getItem('currentBooking'));
    const startDate = document.getElementById('bookingStartDate').value;
    const endDate = document.getElementById('bookingEndDate').value;
    const guests = document.getElementById('bookingGuests').value;
    
    // Calculate nights and total
    const start = new Date(startDate);
    const end = new Date(endDate);
    const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    const totalAmount = bookingData.price * nights;
    
    const booking = {
        userId: 'user123', // Replace with actual user ID from your auth system
        hotel: bookingData.hotel,
        startDate: startDate,
        endDate: endDate,
        guests: parseInt(guests),
        nights: nights,
        totalAmount: totalAmount,
        image: bookingData.image
    };
    
    // Show loading state
    const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    try {
        const response = await fetch(`${PHP_API_URL}/bookings.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(booking)
        });
        
        const result = await response.json();
        
        if (result.success) {
            bookingModal?.hide();
            alert('Booking successful! 🎉');
            
            // Optional: Redirect to thank you page
            // window.location.href = 'thankyou.php';
        } else {
            alert('Booking failed: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Booking failed. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}


// ==================== HELPER FUNCTIONS ====================
function extractHeadline(description) {
    const text = description.replace(/<[^>]*>/g, ' ').trim();
    const headlineMatch = text.match(/HeadLine\s*:\s*([^<]+)/i);
    if (headlineMatch) {
        return headlineMatch[1].trim();
    }
    const firstSentence = text.split(/[.!?]/)[0];
    return firstSentence || 'Great hotel';
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function getDateString(daysFromNow) {
    const date = new Date();
    date.setDate(date.getDate() + daysFromNow);
    return date.toISOString().split('T')[0];
}


// ==================== EVENT LISTENERS ====================
// Handle search form submission
const searchForm = document.getElementById('searchForm');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const city = document.getElementById('searchCity').value.trim();
        if (city) {
            searchHotels(city);
            const staySection = document.getElementById('stay');
            if (staySection) {
                staySection.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
}

// Handle booking form submission
const bookingForm = document.getElementById('bookingForm');
if (bookingForm) {
    bookingForm.addEventListener('submit', handleBooking);
}

// Update total price when dates change
const startDateInput = document.getElementById('bookingStartDate');
const endDateInput = document.getElementById('bookingEndDate');
if (startDateInput) startDateInput.addEventListener('change', updateTotalPrice);
if (endDateInput) endDateInput.addEventListener('change', updateTotalPrice);

// Toggle button text for "Show More"
const stayToggleBtn = document.getElementById('stayToggleBtn');
const stayMoreSection = document.getElementById('stayMore');
if (stayToggleBtn && stayMoreSection) {
    stayMoreSection.addEventListener('show.bs.collapse', function () {
        stayToggleBtn.textContent = 'Show Less';
    });
    stayMoreSection.addEventListener('hide.bs.collapse', function () {
        stayToggleBtn.textContent = 'Show More';
    });
}

// Auth modal login button
const modalLoginBtn = document.getElementById('modalLoginButton');
if (modalLoginBtn) {
    modalLoginBtn.addEventListener('click', function() {
        // Redirect to login page or trigger auth
        alert('Redirect to login page');
        // window.location.href = 'login.html';
    });
}
