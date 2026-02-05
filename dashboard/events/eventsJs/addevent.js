document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.getElementById('createEventForm');
    const categorySelect = document.getElementById('category');
    const skillSelect = document.getElementById('skill');
    const addSkillBtn = document.getElementById('addSkillBtn');
    const selectedSkillsContainer = document.getElementById('selectedSkillsDisplay');
    const skillsCountSpan = document.querySelector('.skills-count');
    
    // Selected skills array
    let selectedSkills = [];
    const MAX_SKILLS = 5;
    
    // Update skill options when category changes
    categorySelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        skillSelect.disabled = true;

        if(!selectedCategory){
            skillSelect.innerHTML = '<option value="">Select a category first</option>';
            return;
        }
        
        fetch(`eventsAPI/getSkills.php?categoryid=${selectedCategory}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error status: ${response.status}`);
                }
                return response.json();
            }
                )
                    .then(skills => { 
                        
                        skillSelect.innerHTML = '<option value="">Select skill(s)</option>';

                        skills.forEach(skill => {
                            const option = document.createElement('option');
                            option.value = skill.skillid; 
                            option.textContent = skill.skillname;
                            skillSelect.appendChild(option);
                        })
                        skillSelect.disabled = false;
                        }
                    )
                    .catch(() => {
                        skillSelect.innerHTML = '<option value="">Error loading skills</option>';
                    });
    });
    
    // Add skill functionality
    addSkillBtn.addEventListener('click', function() {
        const categoryId = categorySelect.value;
        const skillId = skillSelect.value;
        const skillName = skillSelect.options[skillSelect.selectedIndex].text;

        if (!categoryId) {
            showError('Please select a category first');
            return;
        }
        
        if (!skillId) {
            showError('Please select a skill');
            return;
        }
        
        if (selectedSkills.length >= MAX_SKILLS) {
            showError(`Maximum ${MAX_SKILLS} skills allowed`);
            return;
        }
        
        // Check if skill already added
        const skillExists = selectedSkills.some(s => s.skillid === skillId);
        if (skillExists) {
            showError('This skill is already added');
            return;
        }
        
        // Add skill to array
        selectedSkills.push({ skillid:  skillId , skillname : skillName , categoryid : categoryId});
        
        // Update UI
        updateSkillsDisplay();
        updateSkillsCount();
        
        // Lock category once a skill is added (prevent mixing categories)
        categorySelect.disabled = true;
        
        // Reset selections
        skillSelect.value = '';
        
        // Clear any errors
        clearError();
    });
    
    // Update skills display
    function updateSkillsDisplay() {
        selectedSkillsContainer.innerHTML = '';
        
        selectedSkills.forEach((skillObj, index) => {
            const skillTag = document.createElement('div');
            skillTag.className = 'skill-tag';
            skillTag.innerHTML = `
                <span>${skillObj.skillname}</span>
                <button type="button" class="remove-skill" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            `;
            
            // Add remove functionality
            skillTag.querySelector('.remove-skill').addEventListener('click', function() {
                removeSkill(index);
            });
            
            selectedSkillsContainer.appendChild(skillTag);
        });
        
        // Show/hide selected skills container
        if (selectedSkills.length > 0) {
            selectedSkillsContainer.style.display = 'flex';
        } else {
            selectedSkillsContainer.style.display = 'none';
        }
    }
    
    // Remove skill
    function removeSkill(index) {
        selectedSkills.splice(index, 1);
        updateSkillsDisplay();
        updateSkillsCount();
        
        // Unlock category if no skills are left (allow changing category)
        if (selectedSkills.length === 0) {
            categorySelect.disabled = false;
        }
    }
    
    // Update skills count
    function updateSkillsCount() {
        skillsCountSpan.textContent = `${selectedSkills.length}/${MAX_SKILLS}`;
    }
    
    // Error handling functions
    function showError(message) {
        // Create global error container if it doesn't exist
        let errorContainer = document.getElementById('global-error');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'global-error';
            errorContainer.className = 'global-error';
            errorContainer.style.cssText = `
                background-color: #ff6b6b;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
            `;
            form.insertBefore(errorContainer, form.firstChild);
        }
        
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorContainer.style.display = 'none';
        }, 5000);
    }
    
    function clearError() {
        const errorContainer = document.getElementById('global-error');
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }
    
    // Form validation
    function validateForm() {
        let isValid = true;
        clearAllErrors();
        
        // Event title validation
        const eventTitle = document.getElementById('eventTitle');
        if (!eventTitle.value.trim()) {
            showFieldError(eventTitle, 'Event title is required');
            isValid = false;
        } else if (eventTitle.value.trim().length < 5) {
            showFieldError(eventTitle, 'Event title must be at least 5 characters long');
            isValid = false;
        }
        
        // Event start date validation
        const eventStartDate = document.getElementById('eventStartDate');
        if (!eventStartDate.value) {
            showFieldError(eventStartDate, 'Start date and time is required');
            isValid = false;
        } else {
            const startDate = new Date(eventStartDate.value);
            const now = new Date();
            
            // Check if start date is in the past
            if (startDate < now) {
                showFieldError(eventStartDate, 'Event start date cannot be in the past');
                isValid = false;
            }
        }
        
        // Event end date validation
        const eventEndDate = document.getElementById('eventEndDate');
        if (!eventEndDate.value) {
            showFieldError(eventEndDate, 'End date and time is required');
            isValid = false;
        } else {
            const startDate = new Date(eventStartDate.value);
            const endDate = new Date(eventEndDate.value);
            const now = new Date();
            
            // Check if end date is before or same as start date
            if (endDate <= startDate) {
                showFieldError(eventEndDate, 'End date must be after start date');
                isValid = false;
            } else {
                // Check if at least 15 minutes apart
                const minutesDiff = (endDate - startDate) / (1000 * 60);
                if (minutesDiff < 15) {
                    showFieldError(eventEndDate, 'Event must be at least 15 minutes long');
                    isValid = false;
                }
            }
            
            // Check if event ends more than one year after start
            const oneYearAfterStart = new Date(startDate);
            oneYearAfterStart.setFullYear(oneYearAfterStart.getFullYear() + 1);
            
            if (endDate > oneYearAfterStart) {
                showFieldError(eventEndDate, 'Event cannot last longer than one year');
                isValid = false;
            }
        }
        
        // Location validation
        const location = document.getElementById('location');
        if (!location.value.trim()) {
            showFieldError(location, 'Location is required');
            isValid = false;
        }
        
        // Description validation
        const description = document.getElementById('description');
        if (description.value.trim() && description.value.trim().length < 20) {
            showFieldError(description, 'Description must be at least 20 characters long');
            isValid = false;
        }
        
        // Max attendees validation
        const maxAttendees = document.getElementById('maxAttendees');
        if (maxAttendees.value && (maxAttendees.value < 1 || maxAttendees.value > 1000)) {
            showFieldError(maxAttendees, 'Max attendees must be between 1 and 1000');
            isValid = false;
        }
        
        // Credit validation (optional field)
        const credit = document.getElementById('credit');
        if (credit.value) {
            const creditValue = parseInt(credit.value);
            if (isNaN(creditValue) || creditValue < 0 || creditValue > 100) {
                showFieldError(credit, 'Credits must be between 0 and 100');
                isValid = false;
            }
        }
        
        // Skills validation
        if (selectedSkills.length === 0) {
            showError('Please add at least one skill');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        field.classList.add('error');
        const formGroup = field.closest('.form-group');
        let errorElement = formGroup.querySelector('.error-message');
        
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'error-message';
            errorElement.style.cssText = 'display: block; color: #ff6b6b; font-size: 0.85rem; margin-top: 5px;';
            formGroup.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    function clearAllErrors() {
        document.querySelectorAll('.form-input.error').forEach(field => {
            field.classList.remove('error');
        });
        
        document.querySelectorAll('.error-message').forEach(error => {
            error.style.display = 'none';
        });
        
        clearError();
    }
    
    // Real-time validation
    const requiredInputs = document.querySelectorAll('input[required], textarea, select');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                showFieldError(this, `${this.previousElementSibling.textContent} is required`);
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error') && this.value.trim()) {
                this.classList.remove('error');
                const errorElement = this.closest('.form-group').querySelector('.error-message');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        });
    });
    
    // Real-time validation for start date
    const eventStartDate = document.getElementById('eventStartDate');
    eventStartDate.addEventListener('change', function() {
        if (this.value) {
            const startDate = new Date(this.value);
            const now = new Date();
            
            if (startDate < now) {
                showFieldError(this, 'Event start date cannot be in the past');
            } else {
                this.classList.remove('error');
                const errorElement = this.closest('.form-group').querySelector('.error-message');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        }
    });
    
    // Real-time validation for end date
    const eventEndDate = document.getElementById('eventEndDate');
    eventEndDate.addEventListener('change', function() {
        validateEndDate();
    });
    
    eventStartDate.addEventListener('change', function() {
        if (eventEndDate.value) {
            validateEndDate();
        }
    });
    
    // Real-time validation for credit field
    const creditField = document.getElementById('credit');
    creditField.addEventListener('input', function() {
        if (this.value) {
            const creditValue = parseInt(this.value);
            if (isNaN(creditValue) || creditValue < 0 || creditValue > 100) {
                if (creditValue < 0 || creditValue > 100) {
                    showFieldError(this, 'Credits must be between 0 and 100');
                }
            } else {
                this.classList.remove('error');
                const errorElement = this.closest('.form-group').querySelector('.error-message');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        }
    });
    
    function validateEndDate() {
        if (!eventEndDate.value || !eventStartDate.value) {
            return;
        }
        
        const startDate = new Date(eventStartDate.value);
        const endDate = new Date(eventEndDate.value);
        const oneYearAfterStart = new Date(startDate);
        oneYearAfterStart.setFullYear(oneYearAfterStart.getFullYear() + 1);
        
        eventEndDate.classList.remove('error');
        const errorElement = eventEndDate.closest('.form-group').querySelector('.error-message');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        if (endDate <= startDate) {
            showFieldError(eventEndDate, 'End date must be after start date');
        } else {
            // Check if at least 15 minutes apart
            const minutesDiff = (endDate - startDate) / (1000 * 60);
            if (minutesDiff < 15) {
                showFieldError(eventEndDate, 'Event must be at least 15 minutes long');
            } else if (endDate > oneYearAfterStart) {
                showFieldError(eventEndDate, 'Event cannot last longer than one year');
            }
        }
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        const formData = new FormData(form);
        
        selectedSkills.forEach(skill => {
            formData.append('skills[]', skill.skillid);
        });
        
        formData.append('category', categorySelect.value);
        
        formData.append('timezone', Intl.DateTimeFormat().resolvedOptions().timeZone);
        
        fetch('addevent.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Event created successfully!');
                
                setTimeout(() => {
                    form.reset();
                    selectedSkills = [];
                    updateSkillsDisplay();
                    updateSkillsCount();
                    skillSelect.disabled = true;
                    skillSelect.innerHTML = '<option value="">Select category first</option>';
                }, 2000);
            } else {
                showError(data.message || 'Error creating event');
                
                if (data.errors) {
                    data.errors.forEach(error => {
                        showError(error);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error occurred');
        });
    });
        
    function showSuccess(message) {
        let successContainer = document.getElementById('global-success');
        if (!successContainer) {
            successContainer = document.createElement('div');
            successContainer.id = 'global-success';
            successContainer.style.cssText = `
                background-color: #51cf66;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
            `;
            form.insertBefore(successContainer, form.firstChild);
        }
        
        successContainer.textContent = message;
        successContainer.style.display = 'block';
        
        setTimeout(() => {
            successContainer.style.display = 'none';
        }, 5000);
    }
    
    // Initialize
    skillSelect.disabled = true;
    updateSkillsCount();
    selectedSkillsContainer.style.display = 'none';
    
    console.log('Create Event page initialized');
});   