document.addEventListener('DOMContentLoaded', function() {
    loadProfileData();
    setupTabNavigation();
});

function loadProfileData() {
    const userData = window.profileManager.getData();
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

    interestsContent.innerHTML = '<h3><strong><span class="title">Interests</span></strong></h3>';

    const skillsContainer = document.createElement('div');
    skillsContainer.style.cssText = 'display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px;';
    
    seekingSkills.forEach(skill => {
        const skillBadge = document.createElement('span');
        skillBadge.style.cssText = 'background: linear-gradient(135deg, #424769, #5e6591); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 500;';
        skillBadge.textContent = skill;
        skillsContainer.appendChild(skillBadge);
    });

    interestsContent.appendChild(skillsContainer);
}

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