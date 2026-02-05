// Form validation and dynamic functionality for Add Post
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('post-form');
    const description = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    // Offered Skills
    const addOfferedSkillBtn = document.querySelector('.add-skill');
    const offeredSkillsContainer = document.getElementById('skills-container');
    const offeredCategory = document.getElementById('skill-category');
    const offeredSkillSelect = document.getElementById('skill-select');

    // Targeted Skills
    const addTargetSkillBtn = document.querySelector('.add-target-skill');
    const targetSkillsContainer = document.getElementById('target-skills-container');
    const targetCategory = document.getElementById('target-category');
    const targetSkillSelect = document.getElementById('target-skill-select');

    // Availability
    const availabilityInput = document.getElementById('availability-input');
    const addAvailabilityBtn = document.querySelector('.add-availability');
    const availabilityList = document.getElementById('availability-list');

    // Let all sections be visible by default as they are in HTML.

    let offeredSkillCount = 0;
    let targetSkillCount = 0;
    const maxSkills = 5;

    // Add character count for description
    description.addEventListener('input', function () {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/500 characters`;
        charCount.style.color = currentLength > 500 ? 'red' : '';
    });

    // Reusable function to fetch skills
    function setupSkillFetch(categoryElement, skillElement) {
        categoryElement.addEventListener('change', function () {
            const categoryId = this.value;
            skillElement.disabled = true;
            skillElement.innerHTML = '<option value="">Loading...</option>';

            if (!categoryId) {
                skillElement.innerHTML = '<option value="">Select a category first</option>';
                return;
            }

            fetch(`../events/eventsAPI/getSkills.php?categoryid=${categoryId}`)
                .then(response => response.json())
                .then(skills => {
                    skillElement.innerHTML = '<option value="">Select a skill</option>';
                    skills.forEach(skill => {
                        const option = document.createElement('option');
                        option.value = skill.skillid;
                        option.textContent = skill.skillname;
                        skillElement.appendChild(option);
                    });
                    skillElement.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching skills:', error);
                    skillElement.innerHTML = '<option value="">Error loading skills</option>';
                });
        });
    }

    setupSkillFetch(offeredCategory, offeredSkillSelect);
    setupSkillFetch(targetCategory, targetSkillSelect);

    // Reusable function to add skill tags
    function addSkillTag(skillSelect, container, inputName, counterRef, isTarget = false) {
        const skillId = skillSelect.value;
        const skillName = skillSelect.options[skillSelect.selectedIndex].text;

        if (!skillId) {
            alert('Please select a skill first');
            return;
        }

        if (isTarget && targetSkillCount >= maxSkills) {
            alert(`You can only add up to ${maxSkills} targeted skills`);
            return;
        } else if (!isTarget && offeredSkillCount >= maxSkills) {
            alert(`You can only add up to ${maxSkills} offered skills`);
            return;
        }

        // Avoid duplicates in the same container
        const existing = container.querySelectorAll(`input[name="${inputName}"]`);
        for (let input of existing) {
            if (input.value === skillId) {
                alert('This skill has already been added');
                return;
            }
        }

        const skillDiv = document.createElement('div');
        skillDiv.className = 'skill-tag';
        skillDiv.innerHTML = `
            <span>${skillName}</span>
            <input type="hidden" name="${inputName}" value="${skillId}">
            <button type="button" class="remove-skill">×</button>
        `;

        container.appendChild(skillDiv);
        if (isTarget) {
            targetSkillCount++;
        } else {
            offeredSkillCount++;
            // Lock category once a skill is added
            offeredCategory.disabled = true;
        }

        skillDiv.querySelector('.remove-skill').addEventListener('click', function () {
            container.removeChild(skillDiv);
            if (isTarget) {
                targetSkillCount--;
            } else {
                offeredSkillCount--;
                // Unlock category if no skills are left
                if (offeredSkillCount === 0) {
                    offeredCategory.disabled = false;
                }
            }
        });
    }

    addOfferedSkillBtn.addEventListener('click', () => addSkillTag(offeredSkillSelect, offeredSkillsContainer, 'skills[]', offeredSkillCount, false));
    addTargetSkillBtn.addEventListener('click', () => addSkillTag(targetSkillSelect, targetSkillsContainer, 'target_skills[]', targetSkillCount, true));

    // Availability management
    addAvailabilityBtn.addEventListener('click', function () {
        const dateTime = availabilityInput.value;
        if (!dateTime) {
            alert('Please select a date and time');
            return;
        }

        const dateObj = new Date(dateTime);
        const now = new Date();
        
        // Validate that the selected time is not in the past
        if (dateObj <= now) {
            alert('Please select a future date and time. The time slot cannot be in the past.');
            availabilityInput.value = '';
            return;
        }

        const formatted = dateObj.toLocaleString();

        const tag = document.createElement('div');
        tag.className = 'availability-tag';
        tag.innerHTML = `
            <span>${formatted}</span>
            <input type="hidden" name="available_times[]" value="${dateTime}">
            <button type="button" class="remove-availability">×</button>
        `;

        availabilityList.appendChild(tag);
        availabilityInput.value = '';

        tag.querySelector('.remove-availability').addEventListener('click', function () {
            availabilityList.removeChild(tag);
        });
    });

    // Form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        if (validateForm()) {
            const formData = new FormData(form);

            fetch('process_addpost.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Post created successfully!');
                        window.location.href = `postdetails.php?Postid=${data.postId}`;
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    alert('An error occurred while creating the post.');
                });
        }
    });

    function validateForm() {
        let isValid = true;
        const credits = document.getElementById('credits').value;
        const duration = document.getElementById('duration').value;

        // Offered skills are always required
        if (offeredSkillCount === 0) {
            document.getElementById('skills-error').textContent = 'Please add at least one offered skill';
            isValid = false;
        }

        // Context-specific validation (Both are optional but at least one preferred)
        const hasCredits = credits && credits > 0;
        const hasTargetSkills = targetSkillCount > 0;

        if (!hasCredits && !hasTargetSkills) {
            document.getElementById('target-skills-error').textContent = 'Please provide either Targeted Skills or Credits';
            document.getElementById('credits-error').textContent = 'Please provide either Targeted Skills or Credits';
            isValid = false;
        }

        if (hasCredits && !duration) {
            document.getElementById('duration-error').textContent = 'Please select duration for credit-based service';
            isValid = false;
        }

        const availabilityTags = availabilityList.querySelectorAll('input[name="available_times[]"]');
        if (availabilityTags.length === 0) {
            document.getElementById('availability-error').textContent = 'Please add at least one available time slot';
            isValid = false;
        } else {
            // Validate all time slots are still in the future at submission time
            const now = new Date();
            let hasPastSlot = false;
            availabilityTags.forEach(function(input) {
                const slotDate = new Date(input.value);
                if (slotDate <= now) {
                    hasPastSlot = true;
                }
            });
            if (hasPastSlot) {
                document.getElementById('availability-error').textContent = 'One or more time slots are in the past. Please remove them and add valid future times.';
                isValid = false;
            }
        }

        // Basic validation for title/description
        if (document.getElementById('title').value.length < 5) {
            showError(document.getElementById('title'), document.getElementById('title-error'), 'Title too short');
            isValid = false;
        }

        if (document.getElementById('description').value.length < 20) {
            showError(document.getElementById('description'), document.getElementById('description-error'), 'Description too short');
            isValid = false;
        }

        // Delivery type is required (radio always has one checked by default, but safe to check)
        const deliveryType = form.elements['delivery_type'].value;
        if (!deliveryType) {
            document.getElementById('delivery-error').textContent = 'Select a delivery option';
            isValid = false;
        }

        return isValid;
    }

    function showError(field, errorElement, message) {
        errorElement.textContent = message;
        field.classList.add('error');
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(err => err.textContent = '');
        document.querySelectorAll('.error').forEach(f => f.classList.remove('error'));
    }

    document.querySelector('.cancel').addEventListener('click', function () {
        if (confirm('Cancel changes?')) window.history.back();
    });
});