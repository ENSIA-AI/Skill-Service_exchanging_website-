// Skills data for each category
const skillsData = {
    technology: [
        "Web Development", "Mobile App Development", "Data Science", "Machine Learning",
        "Artificial Intelligence", "Cybersecurity", "Cloud Computing", "Database Management",
        "UI/UX Design", "Software Engineering", "Python Programming", "JavaScript Development",
        "Java Programming", "C++ Development", "Game Development", "DevOps", "Blockchain",
        "IoT Development", "API Development", "Quality Assurance"
    ],
    creative: [
        "Graphic Design", "Digital Illustration", "Photography", "Video Editing", "3D Modeling",
        "Animation", "Motion Graphics", "Brand Design", "Typography", "Print Design", "Web Design",
        "UI Design", "UX Research", "Product Design", "Fashion Design", "Interior Design",
        "Industrial Design", "Packaging Design", "Art Direction", "Creative Direction"
    ],
    languages: [
        "English Conversation", "Business English", "Spanish", "French", "German",
        "Chinese Mandarin", "Japanese", "Arabic", "Russian", "Italian", "Portuguese",
        "Public Speaking", "Presentation Skills", "Negotiation", "Interview Preparation",
        "Accent Reduction", "TOEFL/IELTS Preparation", "Translation", "Proofreading", "Creative Writing"
    ],
    business: [
        "Project Management", "Leadership", "Strategic Planning", "Business Development",
        "Marketing Strategy", "Sales Techniques", "Financial Analysis", "Entrepreneurship",
        "Time Management", "Team Building", "Conflict Resolution", "Business Writing",
        "Data Analysis", "Digital Marketing", "Social Media Management", "Customer Service",
        "Human Resources", "Risk Management", "Supply Chain Management", "Quality Assurance"
    ],
    home: [
        "Basic Plumbing", "Electrical Repairs", "Carpentry", "Painting & Decorating",
        "Gardening", "Landscaping", "Home Organization", "Furniture Assembly", "Appliance Repair",
        "Home Maintenance", "DIY Projects", "Interior Design", "Cleaning Techniques", "Pest Control",
        "Home Security", "Energy Efficiency", "Renovation Planning", "Tool Usage & Safety",
        "Wallpaper Installation", "Tile Setting"
    ],
    fitness: [
        "Personal Training", "Yoga Instruction", "Pilates", "Meditation", "Nutrition Coaching",
        "Weight Training", "Cardio Training", "Martial Arts", "Dance Fitness", "Sports Coaching",
        "Strength & Conditioning", "Flexibility Training", "Posture Correction", "Injury Prevention",
        "Rehabilitation Exercises", "Group Fitness", "Boxing", "Swimming", "Cycling", "Running Technique"
    ]
};

// Initialize skills when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateSkills();
    setupRealTimeValidation();
});

function updateSkills() {
    const categorySelect = document.getElementById('skillsCategory');
    const skillSelect = document.getElementById('skillsSkill');
    
    const selectedCategory = categorySelect.value;
    
    // Clear existing skills
    skillSelect.innerHTML = '<option value="">Select skill</option>';
    
    // Get skills for selected category
    const skills = skillsData[selectedCategory] || [];
    
    // Populate skills dropdown
    skills.forEach(skill => {
        const option = document.createElement('option');
        option.value = skill.toLowerCase().replace(/\s+/g, '-');
        option.textContent = skill;
        skillSelect.appendChild(option);
    });
}

function addSkill() {
    const categorySelect = document.getElementById('skillsCategory');
    const skillSelect = document.getElementById('skillsSkill');
    const skillsList = document.getElementById('skillsSkillsList');
    const skillsCounter = document.querySelector('.skills-counter');
    
    const category = categorySelect.options[categorySelect.selectedIndex].text;
    const skill = skillSelect.options[skillSelect.selectedIndex].text;
    
    if (!skill || skill === 'Select a skill from the list') {
        showError('Please select a skill from the list');
        return;
    }
    
    // Count current skills
    const currentSkills = skillsList.querySelectorAll('.skill-item').length;
    if (currentSkills >= 5) {
        showError('Maximum 5 skills allowed');
        return;
    }
    
    // Check if skill already exists
    const existingSkills = Array.from(skillsList.querySelectorAll('.skill-item')).map(item => 
        item.textContent.replace('×', '').trim()
    );
    if (existingSkills.includes(skill)) {
        showError('This skill has already been added');
        return;
    }
    
    // Create skill item
    const skillItem = document.createElement('div');
    skillItem.className = 'skill-item';
    skillItem.innerHTML = `
        ${skill}
        <button type="button" class="remove-skill" onclick="removeSkill(this)">×</button>
    `;
    
    skillsList.appendChild(skillItem);
    
    // Update counter
    const newCount = currentSkills + 1;
    skillsCounter.textContent = `${newCount}/5 skills selected`;
    
    // Reset skill select
    skillSelect.selectedIndex = 0;
    
    // Clear any errors
    clearError();
}

function removeSkill(button) {
    const skillItem = button.parentElement;
    const skillsList = document.getElementById('skillsSkillsList');
    const skillsCounter = document.querySelector('.skills-counter');
    
    skillItem.remove();
    
    // Update counter
    const currentSkills = skillsList.querySelectorAll('.skill-item').length;
    skillsCounter.textContent = `${currentSkills}/5 skills selected`;
}

// Form validation functions
function validateForm() {
    let isValid = true;
    const errors = [];

    // Clear previous errors
    clearAllErrors();

    // Validate Event Title
    const title = document.getElementById('event-title').value.trim();
    if (!title) {
        showFieldError('event-title', 'Event title is required');
        isValid = false;
        errors.push('Event title is required');
    } else if (title.length < 5) {
        showFieldError('event-title', 'Event title must be at least 5 characters long');
        isValid = false;
        errors.push('Event title must be at least 5 characters long');
    }

    // Validate Event Date
    const date = document.getElementById('event-date').value;
    if (!date) {
        showFieldError('event-date', 'Event date is required');
        isValid = false;
        errors.push('Event date is required');
    } else {
        const selectedDate = new Date(date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate < today) {
            showFieldError('event-date', 'Event date cannot be in the past');
            isValid = false;
            errors.push('Event date cannot be in the past');
        }
    }

    // Validate Location
    const location = document.getElementById('location').value.trim();
    if (!location) {
        showFieldError('location', 'Location is required');
        isValid = false;
        errors.push('Location is required');
    } else if (location.length < 3) {
        showFieldError('location', 'Location must be at least 3 characters long');
        isValid = false;
        errors.push('Location must be at least 3 characters long');
    }

    // Validate Description
    const description = document.getElementById('description').value.trim();
    if (!description) {
        showFieldError('description', 'Description is required');
        isValid = false;
        errors.push('Description is required');
    } else if (description.length < 20) {
        showFieldError('description', 'Description must be at least 20 characters long');
        isValid = false;
        errors.push('Description must be at least 20 characters long');
    } else if (description.length > 500) {
        showFieldError('description', 'Description cannot exceed 500 characters');
        isValid = false;
        errors.push('Description cannot exceed 500 characters');
    }

    // Validate Max Attendees
    const maxAttendees = document.getElementById('max-attendees').value;
    if (!maxAttendees) {
        showFieldError('max-attendees', 'Maximum attendees is required');
        isValid = false;
        errors.push('Maximum attendees is required');
    } else if (maxAttendees < 1) {
        showFieldError('max-attendees', 'Maximum attendees must be at least 1');
        isValid = false;
        errors.push('Maximum attendees must be at least 1');
    } else if (maxAttendees > 1000) {
        showFieldError('max-attendees', 'Maximum attendees cannot exceed 1000');
        isValid = false;
        errors.push('Maximum attendees cannot exceed 1000');
    }

    // Validate Category
    const category = document.getElementById('skillsCategory').value;
    if (!category) {
        showFieldError('skillsCategory', 'Please select a category');
        isValid = false;
        errors.push('Please select a category');
    }

    // Validate Skills
    const selectedSkills = document.querySelectorAll('.skill-item');
    if (selectedSkills.length === 0) {
        showError('Please add at least one skill');
        isValid = false;
        errors.push('Please add at least one skill');
    }

    return { isValid, errors };
}

// Error handling functions
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-section') || field.closest('.input-group');
    
    // Add error class to field
    field.classList.add('error');
    
    // Create or update error message
    let errorElement = formGroup.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
    }
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function showError(message) {
    // Create global error container if it doesn't exist
    let errorContainer = document.getElementById('global-error');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'global-error';
        errorContainer.className = 'global-error';
        document.querySelector('.event-form').prepend(errorContainer);
    }
    
    errorContainer.innerHTML = `<div class="error-message">${message}</div>`;
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

function clearAllErrors() {
    // Clear field errors
    document.querySelectorAll('.error').forEach(field => {
        field.classList.remove('error');
    });
    
    document.querySelectorAll('.error-message').forEach(error => {
        error.style.display = 'none';
    });
    
    // Clear global error
    clearError();
}

// Real-time validation setup
function setupRealTimeValidation() {
    const fields = [
        'event-title',
        'event-date', 
        'location',
        'description',
        'max-attendees',
        'skillsCategory'
    ];

    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', function() {
                validateField(fieldId);
            });
            
            field.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    clearFieldError(fieldId);
                }
            });
        }
    });
}

function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    const value = field.value.trim();
    
    switch(fieldId) {
        case 'event-title':
            if (!value) {
                showFieldError(fieldId, 'Event title is required');
            } else if (value.length < 5) {
                showFieldError(fieldId, 'Event title must be at least 5 characters long');
            } else {
                clearFieldError(fieldId);
            }
            break;
            
        case 'event-date':
            if (!value) {
                showFieldError(fieldId, 'Event date is required');
            } else {
                const selectedDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (selectedDate < today) {
                    showFieldError(fieldId, 'Event date cannot be in the past');
                } else {
                    clearFieldError(fieldId);
                }
            }
            break;
            
        case 'location':
            if (!value) {
                showFieldError(fieldId, 'Location is required');
            } else if (value.length < 3) {
                showFieldError(fieldId, 'Location must be at least 3 characters long');
            } else {
                clearFieldError(fieldId);
            }
            break;
            
        case 'description':
            if (!value) {
                showFieldError(fieldId, 'Description is required');
            } else if (value.length < 20) {
                showFieldError(fieldId, 'Description must be at least 20 characters long');
            } else if (value.length > 500) {
                showFieldError(fieldId, 'Description cannot exceed 500 characters');
            } else {
                clearFieldError(fieldId);
            }
            break;
            
        case 'max-attendees':
            if (!value) {
                showFieldError(fieldId, 'Maximum attendees is required');
            } else if (value < 1) {
                showFieldError(fieldId, 'Maximum attendees must be at least 1');
            } else if (value > 1000) {
                showFieldError(fieldId, 'Maximum attendees cannot exceed 1000');
            } else {
                clearFieldError(fieldId);
            }
            break;
            
        case 'skillsCategory':
            if (!value) {
                showFieldError(fieldId, 'Please select a category');
            } else {
                clearFieldError(fieldId);
            }
            break;
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-section') || field.closest('.input-group');
    
    field.classList.remove('error');
    const errorElement = formGroup.querySelector('.error-message');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

// Enhanced form submission handling
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const validation = validateForm();
    
    if (validation.isValid) {
        const selectedSkills = Array.from(document.querySelectorAll('.skill-item')).map(item => 
            item.textContent.replace('×', '').trim()
        );
        
        const formData = {
            title: document.getElementById('event-title').value.trim(),
            date: document.getElementById('event-date').value,
            location: document.getElementById('location').value.trim(),
            description: document.getElementById('description').value.trim(),
            maxAttendees: document.getElementById('max-attendees').value,
            category: document.getElementById('skillsCategory').value,
            skills: selectedSkills
        };
        
        console.log('Form submitted successfully:', formData);
        
        // Show success message
        showSuccess('Event created successfully!');
        
        // Optional: Reset form
        // this.reset();
        // document.getElementById('skillsSkillsList').innerHTML = '';
        // document.querySelector('.skills-counter').textContent = '0/5 skills selected';
        
        // Uncomment to actually submit the form:
        // this.submit();
        
    } else {
        showError('Please fix the errors below before submitting');
        // Scroll to first error
        const firstError = document.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

function showSuccess(message) {
    // Create success container if it doesn't exist
    let successContainer = document.getElementById('global-success');
    if (!successContainer) {
        successContainer = document.createElement('div');
        successContainer.id = 'global-success';
        successContainer.className = 'global-success';
        document.querySelector('.event-form').prepend(successContainer);
    }
    
    successContainer.innerHTML = `<div class="success-message">${message}</div>`;
    successContainer.style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        successContainer.style.display = 'none';
    }, 5000);
}

// Cancel button
document.querySelector('.cancel-button').addEventListener('click', function() {
    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
        window.history.back();
    }
});