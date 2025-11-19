document.addEventListener("DOMContentLoaded", () => {
    loadUserDataIntoForm();
    setupOfferingDropdowns();
    setupSeekingDropdowns();
    setupOfferingSkillsManagement();
    setupSeekingSkillsManagement();
    setupFormSubmission();
    setupSliders();
});

function loadUserDataIntoForm() {
    const userData = window.profileManager.getData();

    document.getElementById('username').value = userData.username;
    document.getElementById('fullname').value = userData.fullname;
    document.querySelector('input[name="professional_title"]').value = userData.professionalTitle;
    document.getElementById('location').value = userData.location;
    document.getElementById('email').value = userData.email;
    document.getElementById('phone').value = userData.phone;
    document.getElementById('about-me').value = userData.aboutMe;

    const avatarImg = document.querySelector('.edit-profile-avatar img');
    if (avatarImg) {
        avatarImg.src = userData.profilePicture;
    }

    loadOfferingSkills(userData.offeringSkills);
    loadSeekingSkills(userData.seekingSkills);
}

function loadOfferingSkills(skills) {
    const container = document.querySelector('.cards-section');
    container.innerHTML = '';

    skills.forEach(skill => {
        const card = createSkillCard(skill);
        container.appendChild(card);
        makeSliderInteractive(card);
    });
}

function createSkillCard(skill) {
    const card = document.createElement('div');
    card.className = 'card';
    const rateId = `rate-${skill.name.toLowerCase().replace(/\s+/g, '-')}`;
    
    card.innerHTML = `
        <div class="card-header">
            <h3 class="card-title">${skill.name}</h3>
            <button type="button" class="delete-skill">×</button>
        </div>

        <div class="proficiency-section">
            <div class="section-header">
                <span class="label">Proficiency Level</span>
                <span class="proficiency-value">${skill.proficiency}%</span>
            </div>
            <div class="slider-container">
                <div class="slider-track">
                    <div class="slider-fill" style="--val: ${skill.proficiency}"></div>
                </div>
                <div class="slider-thumb" style="--val: ${skill.proficiency}"></div>
            </div>
        </div>

        <div class="rate-section">
            <div class="label">
                <label for="${rateId}">Rate (credits/hour)*</label>
            </div>
            <input type="number" id="${rateId}" name="${rateId}" class="textbox"
                   value="${skill.rate}" min="1" required>
        </div>
    `;

    const deleteBtn = card.querySelector('.delete-skill');
    deleteBtn.addEventListener('click', () => {
        if (confirm(`Remove "${skill.name}"?`)) {
            window.profileManager.removeOfferingSkill(skill.name);
            card.remove();
        }
    });

    const rateInput = card.querySelector('input[type="number"]');
    rateInput.addEventListener('change', () => {
        window.profileManager.updateOfferingSkill(skill.name, {
            rate: parseFloat(rateInput.value)
        });
    });

    return card;
}

function loadSeekingSkills(skills) {
    const list = document.querySelector('.list');
    list.innerHTML = '';

    skills.forEach(skill => {
        const li = createSeekingSkillItem(skill);
        list.appendChild(li);
    });

    updateSkillCounter();
}

function createSeekingSkillItem(skillName) {
    const li = document.createElement('li');
    li.textContent = skillName;

    const deleteBtn = document.createElement('span');
    deleteBtn.className = 'delete-from-list';
    deleteBtn.innerHTML = '&times;';
    deleteBtn.addEventListener('click', () => {
        window.profileManager.removeSeekingSkill(skillName);
        li.remove();
        updateSkillCounter();
    });

    li.appendChild(deleteBtn);
    return li;
}

function setupOfferingDropdowns() {
    const categorySelect = document.getElementById('offering-category');
    const skillSelect = document.getElementById('offering-skills');

    categorySelect.addEventListener('change', () => {
        const selectedCategory = categorySelect.value;
        skillSelect.disabled = selectedCategory === '';

        Array.from(skillSelect.options).forEach(option => {
            if (!option.dataset.category || option.dataset.category === selectedCategory) {
                option.hidden = false;
            } else {
                option.hidden = true;
            }
        });

        skillSelect.value = '';
    });
}

function setupSeekingDropdowns() {
    const categorySelect = document.getElementById('seeking-category');
    const skillSelect = document.getElementById('seeking-skills');

    categorySelect.addEventListener('change', () => {
        const selectedCategory = categorySelect.value;
        skillSelect.disabled = selectedCategory === '';

        Array.from(skillSelect.options).forEach(option => {
            if (!option.dataset.category || option.dataset.category === selectedCategory) {
                option.hidden = false;
            } else {
                option.hidden = true;
            }
        });

        skillSelect.value = '';
    });
}

function setupOfferingSkillsManagement() {
    const addBtn = document.getElementById('add-offering-skill-btn');
    const skillSelect = document.getElementById('offering-skills');
    const categorySelect = document.getElementById('offering-category');
    const proficiencyInput = document.getElementById('offering-proficiency');
    const rateInput = document.getElementById('offering-rate');
    const container = document.querySelector('.cards-section');

    addBtn.addEventListener('click', () => {
        const skillName = skillSelect.value.trim();
        const proficiency = parseInt(proficiencyInput.value);
        const rate = parseFloat(rateInput.value);

        if (!skillName) {
            alert('Please select a skill.');
            return;
        }

        if (isNaN(rate) || rate <= 0) {
            alert('Please enter a valid rate.');
            return;
        }

        const newSkill = {
            name: skillName,
            proficiency: proficiency,
            rate: rate
        };

        const added = window.profileManager.addOfferingSkill(newSkill);
        if (!added) {
            alert('You already added this skill.');
            return;
        }

        const card = createSkillCard(newSkill);
        container.appendChild(card);
        makeSliderInteractive(card);

        skillSelect.value = '';
        categorySelect.value = '';
        skillSelect.disabled = true;
        rateInput.value = '';

        const newSkillSlider = document.getElementById('new-skill-slider');
        const thumb = document.getElementById('new-skill-thumb');
        const fill = document.getElementById('new-skill-fill');
        const display = document.getElementById('new-skill-proficiency-display');
        
        thumb.style.setProperty('--val', 50);
        fill.style.setProperty('--val', 50);
        display.textContent = '50%';
        proficiencyInput.value = 50;
    });
}

function setupSeekingSkillsManagement() {
    const addBtn = document.getElementById('add-skill-btn');
    const skillSelect = document.getElementById('seeking-skills');
    const categorySelect = document.getElementById('seeking-category');
    const list = document.querySelector('.list');

    addBtn.addEventListener('click', () => {
        const skillName = skillSelect.value.trim();

        if (!skillName) {
            alert('Please select a skill first.');
            return;
        }

        const currentCount = list.querySelectorAll('li').length;
        if (currentCount >= 10) {
            alert('You can only add up to 10 skills.');
            return;
        }

        const added = window.profileManager.addSeekingSkill(skillName);
        if (!added) {
            alert('You already added this skill.');
            return;
        }

        const li = createSeekingSkillItem(skillName);
        list.appendChild(li);
        updateSkillCounter();

        skillSelect.value = '';
        categorySelect.value = '';
        skillSelect.disabled = true;
    });
}

function updateSkillCounter() {
    const counter = document.getElementById('skill-counter');
    const count = document.querySelectorAll('.list li').length;
    counter.textContent = `${count}/10 Skills selected`;
    counter.style.color = count >= 10 ? 'red' : 'white';
}

function setupSliders() {
    document.querySelectorAll('.card').forEach(card => {
        makeSliderInteractive(card);
    });

    const newSkillSlider = document.getElementById('new-skill-slider');
    const newSkillThumb = document.getElementById('new-skill-thumb');
    const newSkillFill = document.getElementById('new-skill-fill');
    const newSkillDisplay = document.getElementById('new-skill-proficiency-display');
    const newSkillHiddenInput = document.getElementById('offering-proficiency');

    if (newSkillSlider) {
        let isDragging = false;

        function updateNewSkillSlider(e) {
            const rect = newSkillSlider.getBoundingClientRect();
            let position = e.clientX - rect.left;
            position = Math.max(0, Math.min(position, rect.width));
            const percentage = Math.round((position / rect.width) * 100);

            newSkillThumb.style.setProperty('--val', percentage);
            newSkillFill.style.setProperty('--val', percentage);
            newSkillDisplay.textContent = `${percentage}%`;
            newSkillHiddenInput.value = percentage;
        }

        newSkillSlider.addEventListener('mousedown', (e) => {
            isDragging = true;
            updateNewSkillSlider(e);
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) updateNewSkillSlider(e);
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        newSkillSlider.addEventListener('click', updateNewSkillSlider);
    }
}

function makeSliderInteractive(card) {
    const slider = card.querySelector('.slider-container');
    const thumb = card.querySelector('.slider-thumb');
    const fill = card.querySelector('.slider-fill');
    const valueDisplay = card.querySelector('.proficiency-value');
    const skillName = card.querySelector('.card-title').textContent;

    if (!slider || !thumb || !fill || !valueDisplay) return;

    let isDragging = false;

    function updateSlider(e) {
        const rect = slider.getBoundingClientRect();
        let position = e.clientX - rect.left;
        position = Math.max(0, Math.min(position, rect.width));
        const percentage = Math.round((position / rect.width) * 100);

        thumb.style.setProperty('--val', percentage);
        fill.style.setProperty('--val', percentage);
        valueDisplay.textContent = `${percentage}%`;

        window.profileManager.updateOfferingSkill(skillName, {
            proficiency: percentage
        });
    }

    slider.addEventListener('mousedown', (e) => {
        isDragging = true;
        updateSlider(e);
        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) updateSlider(e);
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    slider.addEventListener('click', updateSlider);
}

function setupFormSubmission() {
    const form = document.getElementById('update-profile-form');
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = {
            username: document.getElementById('username').value,
            fullname: document.getElementById('fullname').value,
            professionalTitle: document.querySelector('input[name="professional_title"]').value,
            location: document.getElementById('location').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            aboutMe: document.getElementById('about-me').value
        };

        const offeringSkills = [];
        document.querySelectorAll('.cards-section .card').forEach(card => {
            const skillName = card.querySelector('.card-title').textContent;
            const proficiencyText = card.querySelector('.proficiency-value').textContent;
            const proficiency = parseInt(proficiencyText.replace('%', '').trim());
            const rate = parseFloat(card.querySelector('input[type="number"]').value);
            
            offeringSkills.push({
                name: skillName,
                proficiency: proficiency,
                rate: rate
            });
        });

        const seekingSkills = [];
        document.querySelectorAll('.list li').forEach(li => {
            const skillName = li.textContent.trim().replace('×', '').trim();
            seekingSkills.push(skillName);
        });

        formData.offeringSkills = offeringSkills;
        formData.seekingSkills = seekingSkills;
        window.profileManager.saveData(formData);

        console.log('Saved data:', formData);

        alert('Profile updated successfully!');
        
        window.location.href = 'profile.html';
    });
}