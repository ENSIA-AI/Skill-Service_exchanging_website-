import {events} from './eventsData.js'


function renderEvents(){

    let eventsHtml =``;
    events.forEach((event) => {
        eventsHtml +=
        `
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
                                <i class="fas fa-map-marker-alt"></i>${event.location}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-attendees">
                                <i class="fas fa-users"></i>${event.attendees}/${event.maxAttendees}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-organizer">
                                Organized by: ${event.organizer}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="event-skills">
                                Skills needed: ${event.skills.join(', ')}
                            </div>
                        </div>
                    </div>
                    <div class="join-event-div">
                        <button class="join-event-button">Join Event</button>
                    </div>
            </div>
        `

    })
    document.querySelector('.js-events-list').innerHTML = eventsHtml ;
}

renderEvents();