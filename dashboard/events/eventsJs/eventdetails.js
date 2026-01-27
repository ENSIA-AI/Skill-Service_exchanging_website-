import {events} from './eventsData.js'

function getEventIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('id'));
}

function findEventById(eventId) {
    return events.find(event => event.id === eventId);
}

function renderEventDetails() {
    const eventId = getEventIdFromURL();
    const event = findEventById(eventId);

    if (!event) {
        document.querySelector('.js-event-details-container').innerHTML = `
            <div class="event-detail-card">
                <p style="text-align: center; font-size: 1.2rem; color: #c5c7d9;">Event not found</p>
            </div>
        `;
        return;
    }

    const skillsHtml = event.skills && event.skills.length > 0 
        ? event.skills.map(skill => `<span class="skill-badge">${skill}</span>`).join('')
        : '<span class="skill-badge">No prior knowledge required</span>';

    const isEventFull = event.attendees >= event.maxAttendees;
    const buttonText = isEventFull ? 'Event is Full' : 'Join Event';
    const buttonDisabled = isEventFull ? 'disabled' : '';
    const organiserInitials = event.organizer.split(' ').map(n => n.charAt(0)).join('').toUpperCase();

    const detailsHtml = `
        <div class="event-detail-card main-info">
            <div class="event-title-detail">${event.title}</div>
            
            <div class="event-status-badges">
                <span class="status-badge">${event.type}</span>
                <span class="status-badge">${event.attendees}/${event.maxAttendees} Joined</span>
            </div>

            <div class="event-info-grid">
                <div class="info-item">
                    <span class="info-label">Date</span>
                    <span class="info-value">
                        <i class="fas fa-calendar-alt"></i>${event.date}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Time</span>
                    <span class="info-value">
                        <i class="fas fa-clock"></i>${event.time}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location</span>
                    <span class="info-value">
                        <i class="fas fa-map-marker-alt"></i>${event.location}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cost</span>
                    <span class="info-value">
                        <i class="fas fa-coins"></i>${event.cost}
                    </span>
                </div>
            </div>

            <div class="organized-by-section">
                <div class="organizer-avatar">${organiserInitials}</div>
                <div class="organizer-info">
                    <h3>Organized by</h3>
                    <p>${event.organizer}</p>
                </div>
            </div>

            <button class="join-event-button-detail" data-event-id="${event.id}" ${buttonDisabled}>
                ${buttonText}
            </button>
            <button class="unsend-request-button" data-event-id="${event.id}" style="display: none;">
                <i class="fas fa-times"></i> Unsend Request
            </button>
        </div>

        <div class="event-detail-card">
            <h2 class="section-title">About This Event</h2>
            <p class="section-content">${event.description}</p>
        </div>

        <div class="event-detail-card">
            <h2 class="section-title">Required Skills</h2>
            <p style="color: #e0e0e0; margin-bottom: 1rem; font-size: 0.95rem;">Participants should have knowledge or experience in the following skills:</p>
            <div class="skills-list">
                ${skillsHtml}
            </div>
        </div>

        <div class="event-detail-card">
            <h2 class="section-title">Additional Information</h2>
            <div class="additional-info">
                <div class="info-box">
                    <div class="info-box-label">Event Created</div>
                    <div class="info-box-value">${event.createdDate}</div>
                </div>
                <div class="info-box">
                    <div class="info-box-label">Duration</div>
                    <div class="info-box-value">${event.duration}</div>
                </div>
            </div>
        </div>

        <div class="event-detail-card">
            <h2 class="section-title">Attendees</h2>
            <div class="attendees-section">
                <div class="attendees-count">${event.attendees}</div>
                <div class="attendees-info">
                    <h3>People joined</h3>
                    <p>${event.attendees} out of ${event.maxAttendees} spots filled</p>
                </div>
            </div>
        </div>
    `;

    document.querySelector('.js-event-details-container').innerHTML = detailsHtml;

    // Add event listeners
    setupEventListeners();
}

function setupEventListeners() {
    // Back button - redirect to events page
    document.querySelector('.js-back-button').addEventListener('click', () => {
        window.location.href = 'events.php';
    });

    // Join event button
    const joinButton = document.querySelector('.join-event-button-detail');
    const unsendButton = document.querySelector('.unsend-request-button');

    if (joinButton && !joinButton.disabled) {
        joinButton.addEventListener('click', () => {
            const eventId = joinButton.getAttribute('data-event-id');
            // Handle join event logic here
            joinButton.style.display = 'none';
            unsendButton.style.display = 'block';
            joinButton.textContent = 'Requested';
            joinButton.disabled = true;
            // You can add a success message or redirect here
        });
    }

    // Unsend request button
    if (unsendButton) {
        unsendButton.addEventListener('click', () => {
            const eventId = unsendButton.getAttribute('data-event-id');
            // Handle unsend request logic here
            unsendButton.style.display = 'none';
            joinButton.style.display = 'block';
            joinButton.textContent = 'Join Event';
            joinButton.disabled = false;
            // You can add a confirmation message here
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    renderEventDetails();
});

// Also render immediately in case DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderEventDetails);
} else {
    renderEventDetails();
}
