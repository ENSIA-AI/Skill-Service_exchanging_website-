// Form validation and dynamic functionality for Add Post
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('post-form');
    const description = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    const titleInput = document.getElementById('title');
    const creditsInput = document.getElementById('credits');
    const locationInput = document.getElementById('location');

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

    // ===== REAL-TIME VALIDATION FUNCTIONS =====
    
    function showFieldError(field, errorElement, message) {
        errorElement.textContent = message;
        field.classList.add('input-error');
        errorElement.classList.add('visible');
    }

    function clearFieldError(field, errorElement) {
        errorElement.textContent = '';
        field.classList.remove('input-error');
        errorElement.classList.remove('visible');
    }

    function showFieldSuccess(field) {
        field.classList.remove('input-error');
        field.classList.add('input-success');
    }

    function validateTitle() {
        const value = titleInput.value.trim();
        const errorEl = document.getElementById('title-error');
        
        if (value.length === 0) {
            clearFieldError(titleInput, errorEl);
            return false;
        } else if (value.length < 5) {
            showFieldError(titleInput, errorEl, 'Title must be at least 5 characters');
            return false;
        } else if (value.length > 100) {
            showFieldError(titleInput, errorEl, 'Title cannot exceed 100 characters');
            return false;
        } else {
            clearFieldError(titleInput, errorEl);
            showFieldSuccess(titleInput);
            return true;
        }
    }

    function validateDescription() {
        const value = description.value.trim();
        const errorEl = document.getElementById('description-error');
        
        if (value.length === 0) {
            clearFieldError(description, errorEl);
            return false;
        } else if (value.length < 20) {
            showFieldError(description, errorEl, 'Description must be at least 20 characters');
            return false;
        } else if (value.length > 500) {
            showFieldError(description, errorEl, 'Description cannot exceed 500 characters');
            return false;
        } else {
            clearFieldError(description, errorEl);
            showFieldSuccess(description);
            return true;
        }
    }

    function validateCredits() {
        const value = creditsInput.value;
        const errorEl = document.getElementById('credits-error');
        
        if (value === '') {
            clearFieldError(creditsInput, errorEl);
            return true; // Credits are optional if target skills exist
        }
        
        const numValue = parseInt(value, 10);
        if (isNaN(numValue) || numValue < 0) {
            showFieldError(creditsInput, errorEl, 'Credits must be a positive number');
            return false;
        } else if (numValue > 1000) {
            showFieldError(creditsInput, errorEl, 'Credits cannot exceed 1000');
            return false;
        } else {
            clearFieldError(creditsInput, errorEl);
            showFieldSuccess(creditsInput);
            return true;
        }
    }

    function validateLocation() {
        const value = locationInput.value.trim();
        const errorEl = document.getElementById('location-error');
        
        if (value.length > 100) {
            showFieldError(locationInput, errorEl, 'Location cannot exceed 100 characters');
            return false;
        } else {
            clearFieldError(locationInput, errorEl);
            if (value.length > 0) {
                showFieldSuccess(locationInput);
            }
            return true;
        }
    }

    function validateOfferedSkills() {
        const errorEl = document.getElementById('skills-error');
        if (offeredSkillCount === 0) {
            errorEl.textContent = 'Please add at least one offered skill';
            errorEl.classList.add('visible');
            return false;
        } else {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
            return true;
        }
    }

    function validateTargetSkillsOrCredits() {
        const credits = creditsInput.value;
        const hasCredits = credits && parseInt(credits, 10) > 0;
        const hasTargetSkills = targetSkillCount > 0;
        
        const targetErrorEl = document.getElementById('target-skills-error');
        const creditsErrorEl = document.getElementById('credits-error');
        
        if (!hasCredits && !hasTargetSkills) {
            targetErrorEl.textContent = 'Add targeted skills or set credits';
            targetErrorEl.classList.add('visible');
            if (!credits) {
                creditsErrorEl.textContent = 'Add targeted skills or set credits';
                creditsErrorEl.classList.add('visible');
            }
            return false;
        } else {
            targetErrorEl.textContent = '';
            targetErrorEl.classList.remove('visible');
            // Only clear credits error if it was this specific message
            if (creditsErrorEl.textContent === 'Add targeted skills or set credits') {
                creditsErrorEl.textContent = '';
                creditsErrorEl.classList.remove('visible');
            }
            return true;
        }
    }

    function validateAvailability() {
        const errorEl = document.getElementById('availability-error');
        const availabilityTags = availabilityList.querySelectorAll('input[name="available_times[]"]');
        
        if (availabilityTags.length === 0) {
            errorEl.textContent = 'Please add at least one available time slot';
            errorEl.classList.add('visible');
            return false;
        }
        
        const now = new Date();
        let hasPastSlot = false;
        availabilityTags.forEach(function(input) {
            const slotDate = new Date(input.value);
            if (slotDate <= now) {
                hasPastSlot = true;
            }
        });
        
        if (hasPastSlot) {
            errorEl.textContent = 'One or more time slots are in the past. Please remove them.';
            errorEl.classList.add('visible');
            return false;
        }
        
        errorEl.textContent = '';
        errorEl.classList.remove('visible');
        return true;
    }

    // ===== REAL-TIME EVENT LISTENERS =====
    
    // Title validation on input and blur
    titleInput.addEventListener('input', validateTitle);
    titleInput.addEventListener('blur', validateTitle);

    // Description validation on input and blur
    description.addEventListener('input', function () {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/500 characters`;
        charCount.style.color = currentLength > 500 ? 'red' : '';
        validateDescription();
    });
    description.addEventListener('blur', validateDescription);

    // Credits validation
    creditsInput.addEventListener('input', function() {
        validateCredits();
        validateTargetSkillsOrCredits();
    });
    creditsInput.addEventListener('blur', function() {
        validateCredits();
        validateTargetSkillsOrCredits();
    });

    // Location validation
    locationInput.addEventListener('input', validateLocation);
    locationInput.addEventListener('blur', validateLocation);

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
        const errorEl = isTarget ? document.getElementById('target-skills-error') : document.getElementById('skills-error');

        if (!skillId) {
            errorEl.textContent = 'Please select a skill first';
            errorEl.classList.add('visible');
            return;
        }

        if (isTarget && targetSkillCount >= maxSkills) {
            errorEl.textContent = `You can only add up to ${maxSkills} targeted skills`;
            errorEl.classList.add('visible');
            return;
        } else if (!isTarget && offeredSkillCount >= maxSkills) {
            errorEl.textContent = `You can only add up to ${maxSkills} offered skills`;
            errorEl.classList.add('visible');
            return;
        }

        // Avoid duplicates in the same container
        const existing = container.querySelectorAll(`input[name="${inputName}"]`);
        for (let input of existing) {
            if (input.value === skillId) {
                errorEl.textContent = 'This skill has already been added';
                errorEl.classList.add('visible');
                return;
            }
        }

        // Clear error on successful add
        errorEl.textContent = '';
        errorEl.classList.remove('visible');

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
            validateTargetSkillsOrCredits();
        } else {
            offeredSkillCount++;
            validateOfferedSkills();
            // Lock category once a skill is added
            offeredCategory.disabled = true;
        }

        skillDiv.querySelector('.remove-skill').addEventListener('click', function () {
            container.removeChild(skillDiv);
            if (isTarget) {
                targetSkillCount--;
                validateTargetSkillsOrCredits();
            } else {
                offeredSkillCount--;
                validateOfferedSkills();
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
        const errorEl = document.getElementById('availability-error');
        
        if (!dateTime) {
            errorEl.textContent = 'Please select a date and time';
            errorEl.classList.add('visible');
            return;
        }

        const dateObj = new Date(dateTime);
        const now = new Date();
        
        // Validate that the selected time is not in the past
        if (dateObj <= now) {
            errorEl.textContent = 'Please select a future date and time';
            errorEl.classList.add('visible');
            availabilityInput.value = '';
            return;
        }

        // Clear error on successful add
        errorEl.textContent = '';
        errorEl.classList.remove('visible');

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
        validateAvailability();

        tag.querySelector('.remove-availability').addEventListener('click', function () {
            availabilityList.removeChild(tag);
            validateAvailability();
        });
    });

    // ===== TOAST NOTIFICATION SYSTEM =====
    function showToast(message, type = 'info') {
        // Remove existing toast if any
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <span class="toast-message">${message}</span>
        `;
        document.body.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        if (validateForm()) {
            const formData = new FormData(form);
            const submitBtn = form.querySelector('.submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            fetch('process_addpost.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast('Post created successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = `postdetails.php?Postid=${data.postId}`;
                        }, 1000);
                    } else {
                        showToast(getUserFriendlyError(data.message), 'error');
                        showFormError(data.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post';
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    showToast('Connection error. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Post';
                });
        } else {
            showToast('Please fix the errors before submitting', 'error');
            // Scroll to first error
            const firstError = document.querySelector('.error-message.visible, .input-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    function getUserFriendlyError(message) {
        const errorMappings = {
            'User not logged in': 'Please log in to create a post',
            'Database error': 'Something went wrong. Please try again.',
            'Prepare failed': 'Server error. Please try again later.',
            'Execute failed': 'Could not save your post. Please try again.'
        };
        
        for (const [key, friendly] of Object.entries(errorMappings)) {
            if (message && message.includes(key)) {
                return friendly;
            }
        }
        return message || 'An unexpected error occurred';
    }

    function showFormError(message) {
        // Show server error in a form error container
        let errorContainer = document.getElementById('form-error');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'form-error';
            errorContainer.className = 'form-error-message';
            form.insertBefore(errorContainer, form.querySelector('.form-actions'));
        }
        errorContainer.textContent = getUserFriendlyError(message);
        errorContainer.style.display = 'block';
    }

    function validateForm() {
        let isValid = true;

        // Title validation
        if (!validateTitle()) {
            isValid = false;
        }

        // Description validation
        if (!validateDescription()) {
            isValid = false;
        }

        // Offered skills are always required
        if (!validateOfferedSkills()) {
            isValid = false;
        }

        // Target skills or credits validation
        if (!validateTargetSkillsOrCredits()) {
            isValid = false;
        }

        // Credits validation
        if (!validateCredits()) {
            isValid = false;
        }

        // Location validation
        if (!validateLocation()) {
            isValid = false;
        }

        // Availability validation
        if (!validateAvailability()) {
            isValid = false;
        }

        // Delivery type is required (radio always has one checked by default, but safe to check)
        const deliveryType = form.elements['delivery_type'].value;
        if (!deliveryType) {
            const errorEl = document.getElementById('delivery-error');
            errorEl.textContent = 'Select a delivery option';
            errorEl.classList.add('visible');
            isValid = false;
        }

        return isValid;
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(err => {
            err.textContent = '';
            err.classList.remove('visible');
        });
        document.querySelectorAll('.input-error').forEach(f => f.classList.remove('input-error'));
        document.querySelectorAll('.input-success').forEach(f => f.classList.remove('input-success'));
        const formError = document.getElementById('form-error');
        if (formError) formError.style.display = 'none';
    }

    document.querySelector('.cancel').addEventListener('click', function () {
        if (confirm('Cancel changes?')) window.history.back();
    });
});