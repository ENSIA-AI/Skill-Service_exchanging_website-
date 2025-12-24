import {events} from './eventsData.js'


function renderEvents(){

    let eventsHtml =``;
    events.forEach((event) => {
        const skillsHtml = skillsList(event.skills);
        const isEventFull = event.attendees >= event.maxAttendees;
        const viewButtonClass = isEventFull ?  'full-event-button' : 'view-details-button';
        const viewButtonText = 'View Details';
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
                                Skills needed: ${skillsHtml}
                            </div>
                        </div>
                    </div>
                    <div class="join-event-div">
                        <button class="${viewButtonClass} js-view-details-button" data-event-id="${event.id}" ${isEventFull ? 'disabled' : ''}>${viewButtonText}</button>
                    </div>
            </div>
        `

    })
    document.querySelector('.js-events-list').innerHTML = eventsHtml ;
    viewEventDetails();

}

function skillsList(skills){
       
         if( skills && skills.length > 0 ){
            return skills.map((skill) =>
                `<span class="skill-tag">${skill}</span>`
            ).join(' ');
        }
        
        else return `<span class="skill-tag">No prior knowledge required</span>`;
}

// view event details
function viewEventDetails(){

    let viewButtons = document.querySelectorAll('.js-view-details-button');
    viewButtons.forEach(button =>{
        
        button.addEventListener('click' , () =>{
            const eventId = button.getAttribute('data-event-id');
            window.location.href = `eventdetails.php?id=${eventId}`;
        }
    )
})

// create event button
document.querySelector('.js-add-event-button').addEventListener('click', () => {
    window.location.href = "createEvent.php";
});

}


renderEvents();