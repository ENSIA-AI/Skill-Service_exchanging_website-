document.addEventListener('DOMContentLoaded', function () {
    // Form elements
    const form = document.getElementById('createEventForm');
    const categorySelect = document.getElementById('category');
    const skillSelect = document.getElementById('skill');
    const addSkillBtn = document.getElementById('addSkillBtn');
    const selectedSkillsContainer = document.getElementById('selectedSkillsDisplay');
    const skillsCountSpan = document.getElementById('skillsCount');
    const selectedSkillsInput = document.getElementById('selectedSkillsInput'); // Hidden input

    // Selected skills array
    let selectedSkills = [];
    const MAX_SKILLS = 5;

    console.log('AddEvent JS Loaded');

    // Update skill options when category changes
    categorySelect.addEventListener('change', function () {
        const selectedCategory = this.value;
        skillSelect.disabled = true;

        if (!selectedCategory) {
            skillSelect.innerHTML = '<option value="">Select a category first</option>';
            return;
        }

        // Corrected Fetch Path for MVC Controller
        fetch(`/Skill-Service_exchanging_website-/public/events/getSkills?category_id=${selectedCategory}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error status: ${response.status}`);
                }
                return response.json();
            })
            .then(skills => {
                skillSelect.innerHTML = '<option value="">Select skill(s)</option>';
                if (skills.length === 0) {
                    skillSelect.innerHTML = '<option value="">No skills found</option>';
                }
                skills.forEach(skill => {
                    const option = document.createElement('option');
                    option.value = skill.skillid;
                    option.textContent = skill.skillname;
                    skillSelect.appendChild(option);
                })
                skillSelect.disabled = false;
            })
            .catch((e) => {
                console.error(e);
                skillSelect.innerHTML = '<option value="">Error loading skills</option>';
            });
    });

    // Add skill functionality
    addSkillBtn.addEventListener('click', function () {
        const categoryId = categorySelect.value;
        const skillId = skillSelect.value;
        const skillName = skillSelect.options[skillSelect.selectedIndex]?.text;

        if (!categoryId) {
            alert('Please select a category first');
            return;
        }

        if (!skillId) {
            alert('Please select a skill');
            return;
        }

        if (selectedSkills.length >= MAX_SKILLS) {
            alert(`Maximum ${MAX_SKILLS} skills allowed`);
            return;
        }

        // Check if skill already added
        const skillExists = selectedSkills.some(s => s.skillid === skillId);
        if (skillExists) {
            alert('This skill is already added');
            return;
        }

        // Add skill to array
        selectedSkills.push({ skillid: skillId, skillname: skillName });

        // Update UI
        updateSkillsDisplay();

        // Reset selections
        skillSelect.value = '';
    });

    // Update skills display
    function updateSkillsDisplay() {
        selectedSkillsContainer.innerHTML = '';

        selectedSkills.forEach((skillObj, index) => {
            const skillTag = document.createElement('div');
            skillTag.className = 'skill-tag';
            skillTag.innerHTML = `
                <span>${skillObj.skillname}</span>
                <button type="button" class="remove-skill" data-index="${index}" style="margin-left:5px; cursor:pointer;">&times;</button>
            `;

            // Add remove functionality
            skillTag.querySelector('.remove-skill').addEventListener('click', function () {
                removeSkill(index);
            });

            selectedSkillsContainer.appendChild(skillTag);
        });

        if (skillsCountSpan) skillsCountSpan.textContent = `${selectedSkills.length}/${MAX_SKILLS}`;

        // Update hidden input for form submission
        // Join skill IDs with comma
        if (selectedSkillsInput) {
            selectedSkillsInput.value = selectedSkills.map(s => s.skillid).join(',');
            console.log('Hidden Input Updated:', selectedSkillsInput.value);
        }
    }

    // Remove skill
    function removeSkill(index) {
        selectedSkills.splice(index, 1);
        updateSkillsDisplay();
    }

    // Form submission
    form.addEventListener('submit', function (e) {
        // Do NOT preventDefault if we want standard submission.
        // But we might want basic validation.

        const title = document.getElementById('eventTitle').value;
        if (title.length < 5) {
            e.preventDefault();
            alert("Title must be at least 5 chars");
            return;
        }

        // Ensure skills are populated
        if (selectedSkills.length > 0 && selectedSkillsInput.value === "") {
            selectedSkillsInput.value = selectedSkills.map(s => s.skillid).join(',');
        }

        console.log("Submitting form...");
    });

    // Initialize
    skillSelect.disabled = true;
});