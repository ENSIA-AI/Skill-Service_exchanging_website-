import {events} from './eventsData.js'


function renderEvents(){

    let eventsHtml =``;
    events.forEach((event) => {
        const skillsHtml = skillsList(event.skills);
        const isEventFull = event.attendees >= event.maxAttendees;
        const joinButtonText = isEventFull ? 'Full' : 'Join Event';
        const joinButtonClass = isEventFull ?  'full-event-button' : 'join-event-button';
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
                        <button class="${joinButtonClass} js-join-event-button" ${isEventFull ? 'disabled' : ''}>${joinButtonText}</button>
                    </div>
            </div>
        `

    })
    document.querySelector('.js-events-list').innerHTML = eventsHtml ;
    joinEvent();

}

function skillsList(skills){
       
         if( skills && skills.length > 0 ){
            return skills.map((skill) =>
                `<span class="skill-tag">${skill}</span>`
            ).join(' ');
        }
        
        else return `<span class="skill-tag">No prior knowledge required</span>`;
}

//change join event styles
function joinEvent(){

    let joinButtons = document.querySelectorAll('.js-join-event-button');
    joinButtons.forEach(button =>{
        
        button.addEventListener('click' , () =>{
            if(button.textContent === "Join Event"){
                button.classList.add('joined-event');
                button.textContent = "Requested";
            }
            else if(button.textContent === "Requested"){
                button.classList.remove('joined-event');
                button.textContent = 'Join Event';
            }
        }
    )
})

// create event button
document.querySelector('.js-add-event-button').addEventListener('click', () => {
    window.location.href = "addevent.html";
});

}


renderEvents();