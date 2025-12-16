// Search hotels function
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
    alertContainer.innerHTML = '';
    showMoreContainer.style.display = 'none';
    
    try {
        const url = `http://localhost:3000/search-hotels?` + new URLSearchParams({
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
            cityName.textContent = city.charAt(0).toUpperCase() + city.slice(1);
            displayHotels(data.rates);
            // Load places for the explore section
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

// Load places/landmarks for a city
async function loadPlaces(city) {
    try {
        const url = `http://localhost:3000/search-places?` + new URLSearchParams({
            cityName: city,
            countryCode: 'PH',
            environment: 'sandbox'
        });
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.places && data.places.length > 0) {
            displayPlaces(data.places, city);
        } else {
            // Default explore content
            const exploreContent = document.getElementById('exploreContent');
            exploreContent.innerHTML = `
                <h3>Explore ${city.charAt(0).toUpperCase() + city.slice(1)}</h3>
                <p>Discover amazing places in this beautiful city.</p>
            `;
        }
    } catch (error) {
        console.error('Error loading places:', error);
    }
}

// Display places in explore section
function displayPlaces(places, city) {
    const exploreSection = document.getElementById('exploreContent');
    
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
    
    // Update image
    const exploreImage = document.getElementById('exploreImage');
    exploreImage.style.backgroundImage = `url('${imageUrl}')`;
    exploreImage.style.backgroundSize = 'cover';
    exploreImage.style.backgroundPosition = 'center';
}

// Display hotels
function displayHotels(rates) {
    const stayRowMain = document.getElementById('stayRowMain');
    const stayRowMore = document.getElementById('stayRowMore');
    const showMoreContainer = document.getElementById('showMoreContainer');
    
    stayRowMain.innerHTML = '';
    stayRowMore.innerHTML = '';
    
    // Display first 4 hotels
    rates.slice(0, 4).forEach(rate => {
        stayRowMain.innerHTML += createHotelCard(rate);
    });
    
    // Display next 4 hotels in "Show More" section
    if (rates.length > 4) {
        rates.slice(4, 8).forEach(rate => {
            stayRowMore.innerHTML += createHotelCard(rate);
        });
        showMoreContainer.style.display = 'block';
    }
}

// Create hotel card HTML
function createHotelCard(rate) {
    if (!rate.hotel || !rate.roomTypes[0] || !rate.roomTypes[0].rates[0]) {
        return '';
    }
    
    const hotel = rate.hotel;
    const minRate = rate.roomTypes[0].rates[0];
    const price = minRate.retailRate.total[0].amount;
    const currency = minRate.retailRate.total[0].currency;
    
    // Use correct field names from API response
    const imageUrl = hotel.main_photo || hotel.thumbnail || 'https://via.placeholder.com/400x300?text=Hotel';
    const description = hotel.hotelDescription ? extractHeadline(hotel.hotelDescription) : 'Great accommodation';
    const stars = hotel.stars ? '⭐'.repeat(hotel.stars) : '';
    const address = hotel.address || hotel.city || '';
    
    return `
        <div class="col-md-3 mb-3">
            <div class="card h-100">
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

// Extract headline or first sentence from hotel description
function extractHeadline(description) {
    // Remove HTML tags
    const text = description.replace(/<[^>]*>/g, ' ').trim();
    
    // Try to extract HeadLine if it exists
    const headlineMatch = text.match(/HeadLine\s*:\s*([^<]+)/i);
    if (headlineMatch) {
        return headlineMatch[1].trim();
    }
    
    // Otherwise get first sentence
    const firstSentence = text.split(/[.!?]/)[0];
    return firstSentence || 'Great hotel';
}

// Truncate text to specified length
function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Get date string (days from now)
function getDateString(daysFromNow) {
    const date = new Date();
    date.setDate(date.getDate() + daysFromNow);
    return date.toISOString().split('T')[0];
}

// Handle search form submission
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const city = document.getElementById('searchCity').value.trim();
    if (city) {
        searchHotels(city);
        document.getElementById('stay').scrollIntoView({ behavior: 'smooth' });
    }
});

// Toggle button text
const stayToggleBtn = document.getElementById('stayToggleBtn');
document.getElementById('stayMore').addEventListener('show.bs.collapse', function () {
    stayToggleBtn.textContent = 'Show Less';
});
document.getElementById('stayMore').addEventListener('hide.bs.collapse', function () {
    stayToggleBtn.textContent = 'Show More';
});

// Load default hotels on page load
window.addEventListener('load', function() {
    searchHotels('manila');
});
