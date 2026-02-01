// Event Pagination State
let currentPage = 1;
let totalPages = 1;
let isLoading = false;

/**
 * Fetch events from the database API
 */
async function fetchEvents(page = 1) {
    isLoading = true;
    try {
        // Construct the API URL - use relative path from the current page
        const apiUrl = `./eventsAPI/getEventsAPI.php?page=${page}&limit=5`;
        console.log('Fetching from:', apiUrl);
        
        const response = await fetch(apiUrl);
        
        console.log('API Response Status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Data:', data);
        
        if (data && data.success) {
            currentPage = data.pagination.currentPage;
            totalPages = data.pagination.totalPages;
            console.log(`Loaded ${data.data.length} events from page ${currentPage}`);
            return data.data;
        } else {
            console.error('API Error:', data);
            showErrorMessage('Failed to load events. Please try again.');
            return [];
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        showErrorMessage('Failed to fetch events from server. Error: ' + error.message);
        return [];
    } finally {
        isLoading = false;
    }
}

/**
 * Render events to the DOM
 */
function renderEvents(events) {
    let eventsHtml = ``;
    
    events.forEach((event) => {
        const skillsHtml = skillsList(event.skills);
        const isEventFull = event.attendees >= event.maxAttendees;
        const viewButtonClass = isEventFull ? 'full-event-button' : 'view-details-button';
        const viewButtonText = 'View Details';
        
        eventsHtml += `
            <div class="event-card">
                    <div class="event-title">${event.title}</div>
                    <div class="details-text">
                        <div class="detail-item">
                            <div class="event-date">
                                <i class="fas fa-calendar-alt"></i> ${event.date}
                            </div>
                        </div>
                        <div class="detail-item">    
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i> ${event.location}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-attendees">
                                <i class="fas fa-users"></i> ${event.attendees}/${event.maxAttendees}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-organizer">
                                Organized by: ${event.organizer}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-skills">
                                Skills needed: ${skillsHtml}
                            </div>
                        </div>
                    </div>
                    <div class="join-event-div">
                        <button class="${viewButtonClass} js-view-details-button" data-event-id="${event.EventId}" ${isEventFull ? 'disabled' : ''}>${viewButtonText}</button>
                    </div>
            </div>
        `;
    });
    
    document.querySelector('.js-events-list').innerHTML = eventsHtml;
    viewEventDetails();
    updateSeeMoreButton();
}

/**
 * Format skills into HTML tags
 */
function skillsList(skills) {
    if (skills && skills.length > 0) {
        return skills.map((skill) =>
            `<span class="skill-tag">${skill}</span>`
        ).join(' ');
    }
    return `<span class="skill-tag">No prior knowledge required</span>`;
}

/**
 * Handle view details button clicks
 */
function viewEventDetails() {
    let viewButtons = document.querySelectorAll('.js-view-details-button');
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const eventId = button.getAttribute('data-event-id');
            window.location.href = `eventdetails.php?id=${eventId}`;
        });
    });
}

/**
 * Update "See More" button visibility
 */
function updateSeeMoreButton() {
    const seeMoreBtn = document.querySelector('.js-see-more-button');
    if (seeMoreBtn) {
        if (currentPage < totalPages && !isLoading) {
            seeMoreBtn.style.display = 'block';
            seeMoreBtn.disabled = false;
        } else if (currentPage >= totalPages) {
            seeMoreBtn.style.display = 'none';
        }
    }
}

/**
 * Load more events
 */
async function loadMoreEvents() {
    const seeMoreBtn = document.querySelector('.js-see-more-button');
    
    if (seeMoreBtn) {
        seeMoreBtn.disabled = true;
        seeMoreBtn.textContent = 'Loading...';
    }
    
    const moreEvents = await fetchEvents(currentPage + 1);
    
    if (moreEvents.length > 0) {
        // Append new events to existing ones
        const eventsList = document.querySelector('.js-events-list');
        let moreEventsHtml = ``;
        
        moreEvents.forEach((event) => {
            const skillsHtml = skillsList(event.skills);
            const isEventFull = event.attendees >= event.maxAttendees;
            const viewButtonClass = isEventFull ? 'full-event-button' : 'view-details-button';
            
            moreEventsHtml += `
                <div class="event-card">
                        <div class="event-title">${event.title}</div>
                        <div class="details-text">
                            <div class="detail-item">
                                <div class="event-date">
                                    <i class="fas fa-calendar-alt"></i> ${event.date}
                                </div>
                            </div>
                            <div class="detail-item">    
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i> ${event.location}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-attendees">
                                    <i class="fas fa-users"></i> ${event.attendees}/${event.maxAttendees}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-organizer">
                                    Organized by: ${event.organizer}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-skills">
                                    Skills needed: ${skillsHtml}
                                </div>
                            </div>
                        </div>
                        <div class="join-event-div">
                            <button class="${viewButtonClass} js-view-details-button" data-event-id="${event.EventId}" ${isEventFull ? 'disabled' : ''}>View Details</button>
                        </div>
                </div>
            `;
        });
        
        eventsList.innerHTML += moreEventsHtml;
        viewEventDetails();
        updateSeeMoreButton();
    }
    
    if (seeMoreBtn) {
        seeMoreBtn.textContent = 'See More Events';
    }
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    const eventsList = document.querySelector('.js-events-list');
    eventsList.innerHTML = `<p class="error-message">${message}</p>`;
}

/**
 * Initialize events on page load
 */
async function initializeEvents() {
    const events = await fetchEvents(1);
    renderEvents(events);
}

// Create event button handler
document.addEventListener('DOMContentLoaded', () => {
    // Initialize events from database
    initializeEvents();
    
    // Add event button
    const addEventBtn = document.querySelector('.js-add-event-button');
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => {
            window.location.href = "createEvent.php";
        });
    }
    
    // See More button handler
    const seeMoreBtn = document.querySelector('.js-see-more-button');
    if (seeMoreBtn) {
        seeMoreBtn.addEventListener('click', loadMoreEvents);
    }
});