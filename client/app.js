// Global variables
let currentHotels = [];
let currentSearchParams = {};
let currentPage = 1;
const HOTELS_PER_PAGE = 8;

// Debounce function for search
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

// Enhanced search with loading state and error handling
async function searchHotels(city) {
    const stayRowMain = document.getElementById('stayRowMain');
    const stayRowMore = document.getElementById('stayRowMore');
    const cityName = document.getElementById('cityName');
    const alertContainer = document.getElementById('alertContainer');
    const showMoreContainer = document.getElementById('showMoreContainer');

    if (!stayRowMain) {
        console.error('stayRowMain element not found');
        return;
    }

    // Reset pagination
    currentPage = 1;

    // Show loading with skeleton cards
    stayRowMain.innerHTML = generateSkeletonCards(4);
    
    if (stayRowMore) stayRowMore.innerHTML = '';
    if (showMoreContainer) showMoreContainer.style.display = 'none';
    if (alertContainer) alertContainer.innerHTML = '';

    // Add timeout for better UX
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000);

    try {
        const response = await fetch(
            `hotel-search.php?city=${encodeURIComponent(city)}`,
            { signal: controller.signal }
        );
        
        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.hotels && data.hotels.length > 0) {
            if (cityName) cityName.textContent = city;
            currentHotels = data.hotels;
            currentSearchParams = data.searchParams;
            displayHotels(data.hotels, data.searchParams);
        } else {
            stayRowMain.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3"></i>
                    <h4>No hotels found in ${escapeHtml(city)}</h4>
                    <p class="text-muted">Try searching for a different destination</p>
                </div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        
        let errorMessage = 'Error loading hotels. Please try again.';
        if (error.name === 'AbortError') {
            errorMessage = 'Request timed out. Please check your connection and try again.';
        }
        
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${errorMessage}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        }
        
        stayRowMain.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-circle fs-1 text-danger mb-3"></i>
                <h4>Oops! Something went wrong</h4>
                <button class="btn btn-primary mt-3" onclick="searchHotels('${escapeHtml(city)}')">
                    <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
            </div>`;
    }
}

// Generate skeleton loading cards
function generateSkeletonCards(count) {
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100">
                    <div class="bg-light" style="height: 200px;">
                        <div class="placeholder-glow h-100">
                            <span class="placeholder col-12 h-100"></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title placeholder-glow">
                            <span class="placeholder col-6"></span>
                        </h5>
                        <p class="card-text placeholder-glow">
                            <span class="placeholder col-12"></span>
                            <span class="placeholder col-8"></span>
                        </p>
                        <p class="placeholder-glow">
                            <span class="placeholder col-4"></span>
                        </p>
                    </div>
                </div>
            </div>`;
    }
    return html;
}

// Display hotels with pagination
function displayHotels(hotels, searchParams) {
    const stayRowMain = document.getElementById('stayRowMain');
    const stayRowMore = document.getElementById('stayRowMore');
    const showMoreContainer = document.getElementById('showMoreContainer');

    if (!stayRowMain) return;

    const mainHotels = hotels.slice(0, 4);
    const moreHotels = hotels.slice(4);

    stayRowMain.innerHTML = '';
    if (stayRowMore) stayRowMore.innerHTML = '';
    
    // Use DocumentFragment for better performance
    const mainFragment = document.createDocumentFragment();
    mainHotels.forEach((hotel, index) => {
        const cardElement = createHotelCardElement(hotel, index, searchParams);
        mainFragment.appendChild(cardElement);
    });
    stayRowMain.appendChild(mainFragment);
    
    if (stayRowMore && moreHotels.length > 0) {
        const moreFragment = document.createDocumentFragment();
        moreHotels.forEach((hotel, index) => {
            const cardElement = createHotelCardElement(hotel, index + 4, searchParams);
            moreFragment.appendChild(cardElement);
        });
        stayRowMore.appendChild(moreFragment);
    }
    
    if (showMoreContainer) {
        showMoreContainer.style.display = moreHotels.length > 0 ? 'block' : 'none';
    }
}

// Enhanced hotel card creation with lazy loading
function createHotelCardElement(hotel, index, searchParams) {
    const name = hotel.name || 'Hotel';
    const imageUrl = hotel.heroImage || hotel.images?.[0] || 'https://via.placeholder.com/400x300?text=Hotel';
    const rating = hotel.rating ? parseFloat(hotel.rating).toFixed(1) : null;
    const price = hotel.price?.current || hotel.price || 'N/A';
    const address = hotel.address || 'Address not available';
    const description = hotel.description || 'Discover this wonderful hotel.';
    
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-3 mb-4';
    
    const card = document.createElement('div');
    card.className = 'card h-100 shadow-sm hotel-card';
    
    // Lazy load images
    const img = document.createElement('img');
    img.dataset.src = imageUrl;
    img.alt = name;
    img.className = 'card-img-top lazy-load';
    img.style.height = '200px';
    img.style.objectFit = 'cover';
    
    // Placeholder until image loads
    img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23eee" width="400" height="300"/%3E%3C/svg%3E';
    
    // Observe for lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                    observer.unobserve(img);
                }
            });
        });
        imageObserver.observe(img);
    } else {
        img.src = imageUrl;
    }
    
    const cardBody = document.createElement('div');
    cardBody.className = 'card-body d-flex flex-column';
    
    const title = document.createElement('h5');
    title.className = 'card-title';
    title.textContent = name;
    
    if (rating) {
        const badge = document.createElement('span');
        badge.className = 'badge bg-warning text-dark ms-2';
        badge.innerHTML = `<i class="bi bi-star-fill"></i> ${rating}`;
        title.appendChild(badge);
    }
    
    const desc = document.createElement('p');
    desc.className = 'card-text text-muted small';
    desc.textContent = truncateText(description, 80);
    
    const addr = document.createElement('p');
    addr.className = 'card-text small';
    addr.innerHTML = `<i class="bi bi-geo-alt"></i> ${escapeHtml(address)}`;
    
    const priceDiv = document.createElement('div');
    priceDiv.className = 'mt-auto';
    priceDiv.innerHTML = `
        <p class="text-primary fw-bold fs-5 mb-0">₱${escapeHtml(price.toString())}</p>
        <small class="text-muted">per night</small>
    `;
    
    cardBody.appendChild(title);
    cardBody.appendChild(desc);
    cardBody.appendChild(addr);
    cardBody.appendChild(priceDiv);
    
    const cardFooter = document.createElement('div');
    cardFooter.className = 'card-footer bg-white border-top';
    
    const bookButton = document.createElement('button');
    bookButton.className = 'btn btn-primary w-100';
    bookButton.innerHTML = '<i class="bi bi-calendar-check"></i> Book Now';
    bookButton.setAttribute('aria-label', `Book ${name}`);
    
    bookButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        showBookingModal(hotel, searchParams);
    });
    
    cardFooter.appendChild(bookButton);
    
    card.appendChild(img);
    card.appendChild(cardBody);
    card.appendChild(cardFooter);
    col.appendChild(card);
    
    return col;
}

// XSS protection helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Enhanced booking modal
function showBookingModal(hotel, searchParams) {
    const modalElement = document.getElementById('bookingModal');
    if (!modalElement) {
        console.error('Booking modal not found!');
        showAlert('error', 'Booking system unavailable. Please try again later.');
        return;
    }
    
    // Validate search params
    if (!searchParams.checkin || !searchParams.checkout) {
        showAlert('warning', 'Please set check-in and check-out dates.');
        return;
    }
    
    // Populate modal with sanitized data
    const elements = {
        modalImage: document.getElementById('modalHotelImage'),
        modalName: document.getElementById('modalHotelName'),
        modalAddress: document.getElementById('modalHotelAddress'),
        modalRating: document.getElementById('modalHotelRating'),
        modalDescription: document.getElementById('modalHotelDescription'),
        modalPrice: document.getElementById('modalHotelPrice')
    };
    
    if (elements.modalImage) elements.modalImage.src = hotel.heroImage || 'https://via.placeholder.com/600x400?text=Hotel';
    if (elements.modalName) elements.modalName.textContent = hotel.name || 'Hotel';
    if (elements.modalAddress) elements.modalAddress.textContent = hotel.address || 'Address not available';
    if (elements.modalRating) elements.modalRating.textContent = hotel.rating ? parseFloat(hotel.rating).toFixed(1) : 'N/A';
    if (elements.modalDescription) elements.modalDescription.textContent = hotel.description || 'A wonderful place to stay.';
    if (elements.modalPrice) elements.modalPrice.textContent = `₱${hotel.price?.current || hotel.price || 'N/A'}`;
    
    // Calculate booking details
    const checkIn = new Date(searchParams.checkin);
    const checkOut = new Date(searchParams.checkout);
    const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
    const pricePerNight = parseFloat(hotel.price?.current || hotel.price || 0);
    const totalPrice = nights * pricePerNight;
    
    // Update booking details
    const bookingElements = {
        modalCheckIn: document.getElementById('modalCheckIn'),
        modalCheckOut: document.getElementById('modalCheckOut'),
        modalGuests: document.getElementById('modalGuests'),
        modalNights: document.getElementById('modalNights'),
        modalTotalPrice: document.getElementById('modalTotalPrice')
    };
    
    if (bookingElements.modalCheckIn) bookingElements.modalCheckIn.textContent = searchParams.checkin;
    if (bookingElements.modalCheckOut) bookingElements.modalCheckOut.textContent = searchParams.checkout;
    if (bookingElements.modalGuests) bookingElements.modalGuests.textContent = searchParams.adults;
    if (bookingElements.modalNights) bookingElements.modalNights.textContent = nights;
    if (bookingElements.modalTotalPrice) bookingElements.modalTotalPrice.textContent = `₱${totalPrice.toFixed(2)}`;
    
    // Store booking data
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        Object.assign(bookingForm.dataset, {
            hotelId: hotel.id || hotel.name,
            hotelName: hotel.name,
            hotelAddress: hotel.address || '',
            hotelImage: hotel.heroImage || hotel.images?.[0] || '',
            hotelRating: hotel.rating || '',
            hotelPrice: pricePerNight.toString(),
            checkIn: searchParams.checkin,
            checkOut: searchParams.checkout,
            guests: searchParams.adults.toString(),
            nights: nights.toString(),
            totalPrice: totalPrice.toString()
        });
    }
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Handle booking form submission
async function handleBookingSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    // Get form data
    const formData = {
        hotel_id: form.dataset.hotelId,
        hotel_name: form.dataset.hotelName,
        hotel_address: form.dataset.hotelAddress,
        hotel_image: form.dataset.hotelImage,
        hotel_rating: form.dataset.hotelRating,
        hotel_price: form.dataset.hotelPrice,
        check_in: form.dataset.checkIn,
        check_out: form.dataset.checkOut,
        guests: form.dataset.guests,
        nights: form.dataset.nights,
        total_price: form.dataset.totalPrice,
        card_holder_name: document.getElementById('cardHolderName').value,
        card_number: document.getElementById('cardNumber').value,
        expiry_date: document.getElementById('expiryDate').value,
        cvv: document.getElementById('cvv').value,
        billing_address: document.getElementById('billingAddress').value
    };
    
    try {
        const response = await fetch('process-booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Close modal
            const modalElement = document.getElementById('bookingModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();
            
            // Reset form
            form.reset();
            
            // Show success message
            showAlert('success', 'Booking confirmed successfully!', {
                url: 'bookings.php',
                text: 'View My Bookings'
            });
        } else {
            showAlert('error', result.message || 'Booking failed. Please try again.');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showAlert('error', 'An error occurred. Please try again.');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
}

// Unified alert function
function showAlert(type, message, link = null) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const icons = {
        success: 'check-circle-fill',
        error: 'exclamation-triangle-fill',
        warning: 'exclamation-circle-fill',
        info: 'info-circle-fill'
    };
    
    const linkHtml = link ? `<a href="${link.url}" class="alert-link ms-2">${link.text}</a>` : '';
    
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${icons[type]} me-2"></i>
            ${message}
            ${linkHtml}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Truncate text helper
function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

// Debounced search
const debouncedSearch = debounce(searchHotels, 500);

// Search form handler
const searchForm = document.getElementById('searchForm');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const cityInput = document.getElementById('searchCity');
        const city = cityInput?.value.trim();
        
        if (city) {
            searchHotels(city);
            document.getElementById('stay')?.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

// Search destination function
function searchDestination(destination) {
    const cityInput = document.getElementById('searchCity');
    if (cityInput) cityInput.value = destination;
    
    searchHotels(destination);
    document.getElementById('stay')?.scrollIntoView({ behavior: 'smooth' });
}

// Show more hotels toggle
const showMoreBtn = document.getElementById('stayToggleBtn');
if (showMoreBtn) {
    showMoreBtn.addEventListener('click', function() {
        const moreRow = document.getElementById('stayRowMore');
        if (moreRow) {
            const isHidden = moreRow.style.display === 'none' || !moreRow.style.display;
            moreRow.style.display = isHidden ? 'flex' : 'none';
            this.innerHTML = isHidden 
                ? '<i class="bi bi-chevron-up"></i> Show Less'
                : '<i class="bi bi-chevron-down"></i> Show More';
        }
    });
}

// Input formatters for payment form
const cardNumberInput = document.getElementById('cardNumber');
if (cardNumberInput) {
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
        e.target.value = value.match(/.{1,4}/g)?.join(' ') || value;
    });
}

const expiryDateInput = document.getElementById('expiryDate');
if (expiryDateInput) {
    expiryDateInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
    });
}

const cvvInput = document.getElementById('cvv');
if (cvvInput) {
    cvvInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
    });
}

// Attach booking form handler
const bookingForm = document.getElementById('bookingForm');
if (bookingForm) {
    bookingForm.addEventListener('submit', handleBookingSubmit);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    searchHotels('Manila');
    
    // Set date constraints
    const today = new Date().toISOString().split('T')[0];
    const checkInInput = document.getElementById('checkin');
    const checkOutInput = document.getElementById('checkout');
    
    if (checkInInput) {
        checkInInput.setAttribute('min', today);
        checkInInput.addEventListener('change', function() {
            if (checkOutInput) {
                checkOutInput.setAttribute('min', this.value);
            }
        });
    }
});

// Smooth scroll for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});