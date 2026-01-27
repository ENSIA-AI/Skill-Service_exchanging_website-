document.addEventListener('DOMContentLoaded', function () {
    console.log('Profile page loaded');
    loadProfileData();
    setupTabNavigation();
});

function loadProfileData() {
    console.log('Loading profile data...');
    const userData = window.profileManager.getData();
    console.log('User data:', userData);
    console.log('Offering skills:', userData.offeringSkills);
    console.log('Seeking skills:', userData.seekingSkills);

    updateProfileHeader(userData);
    updateProfileMeta(userData);
    updateTabsContent(userData);
}

function updateProfileHeader(userData) {
    const avatarImg = document.querySelector('.avatar img');
    if (avatarImg) {
        avatarImg.src = userData.profilePicture;
    }

    const userInfoName = document.querySelector('.user-info-profile-name span');
    if (userInfoName) {
        userInfoName.textContent = userData.fullname;
    }

    const username = document.querySelector('.user-name span');
    if (username) {
        username.textContent = userData.username;
    }
}

function updateProfileMeta(userData) {
    const profileName = document.querySelector('.profile-name span');
    if (profileName) {
        profileName.textContent = userData.fullname;
    }

    const major = document.querySelector('.major span');
    if (major) {
        major.textContent = userData.professionalTitle;
    }

    const location = document.querySelector('.location span');
    if (location) {
        location.textContent = `Location: ${userData.location}`;
    }

    const exchanges = document.querySelector('.user-meta-card:nth-child(2) .user-meta-card-info-details');
    if (exchanges) {
        exchanges.textContent = userData.exchanges.toString().padStart(2, '0');
    }

    const memberSince = document.querySelector('.user-meta-card:nth-child(3) .user-meta-card-info-details');
    if (memberSince) {
        memberSince.textContent = userData.memberSince;
    }
}

function updateTabsContent(userData) {
    const aboutText = document.querySelector('#about-content p');
    if (aboutText) {
        aboutText.innerHTML = `&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${userData.aboutMe}`;
    }

    updateSkillsSection(userData.offeringSkills);
    updateInterestsSection(userData.seekingSkills);
    updateAvailabilitySection(userData.availability);
}

function updateSkillsSection(skills) {
    const skillsContainer = document.querySelector('.skills');
    if (!skillsContainer) return;

    skillsContainer.innerHTML = '';

    skills.forEach(skill => {
        const skillDiv = document.createElement('div');
        skillDiv.className = 'skill';

        const donutDiv = document.createElement('div');
        donutDiv.className = 'donut';
        donutDiv.style.setProperty('--val', skill.proficiency);

        const span = document.createElement('span');
        span.textContent = `${skill.proficiency}%`;
        donutDiv.appendChild(span);

        const p = document.createElement('p');
        p.innerHTML = `
            <strong>${skill.name}</strong><br><br>
            <span class="muted">${skill.rate} credits/hours</span>
        `;

        skillDiv.appendChild(donutDiv);
        skillDiv.appendChild(p);
        skillsContainer.appendChild(skillDiv);
    });

    console.log('Skills updated:', skills);
}

function updateInterestsSection(seekingSkills) {
    const interestsContent = document.querySelector('#interests-content section');
    if (!interestsContent) return;


    const skillsContainer = document.createElement('div');
    skillsContainer.style.cssText = 'background:#5e6591; display: flex; flex-wrap: wrap; gap: 10px; padding-left: 20px ;';

    seekingSkills.forEach(skill => {
        const skillBadge = document.createElement('span');
        skillBadge.style.cssText = 'background:#2d3250; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 500; ';
        skillBadge.textContent = skill;
        skillsContainer.appendChild(skillBadge);
    });

    interestsContent.appendChild(skillsContainer);
}

function updateAvailabilitySection(availability) {

    const container = document.getElementById('availability-list-container');

    if (!container || !availability) return;

    container.innerHTML = '';

    const days = [
        { key: 'monday', label: 'Monday' },
        { key: 'tuesday', label: 'Tuesday' },
        { key: 'wednesday', label: 'Wednesday' },
        { key: 'thursday', label: 'Thursday' },
        { key: 'friday', label: 'Friday' },
        { key: 'saturday', label: 'Saturday' },
        { key: 'sunday', label: 'Sunday' }
    ];

    days.forEach(day => {
        
        if (availability[day.key] && availability[day.key].available) {

            const dayRow = document.createElement('div');
            dayRow.className = 'availability-row'; 

            const dayLabel = document.createElement('span');
            dayLabel.className = 'day-label'; 
            dayLabel.textContent = day.label;

            const timeRange = document.createElement('span');
            timeRange.className = 'time-slot'; 
            timeRange.textContent = `${availability[day.key].startTime} - ${availability[day.key].endTime}`;

            dayRow.appendChild(dayLabel);
            dayRow.appendChild(timeRange);

            container.appendChild(dayRow);
        }
    });
}

if (availabilityContainer.children.length === 0) {
    const noAvailability = document.createElement('p');
    noAvailability.style.cssText = 'color: #d4dedd; margin-top: 20px;';
    noAvailability.textContent = 'No availability set. Please update your schedule in the edit profile page.';
    availabilityContainer.appendChild(noAvailability);
}

availabilityContent.appendChild(availabilityContainer);


function setupTabNavigation() {
    const navItems = document.querySelectorAll('.nav-item span');
    const tabContents = document.querySelectorAll('.tab-content');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            navItems.forEach(i => i.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));

            item.classList.add('active');
            const target = document.getElementById(item.dataset.target);
            if (target) {
                target.classList.add('active');
            }
        });
    });
}