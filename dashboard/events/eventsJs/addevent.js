// Skills data for each category
const skillsData = {
    technology: [
        "Web Development", "Mobile App Development", "Data Science", "Machine Learning",
        "Artificial Intelligence", "Cybersecurity", "Cloud Computing", "Database Management",
        "UI/UX Design", "Software Engineering", "Python Programming", "JavaScript Development",
        "Java Programming", "C++ Development", "Game Development", "DevOps", "Blockchain",
        "IoT Development", "API Development", "Quality Assurance"
    ],
    design: [
        "Graphic Design", "Digital Illustration", "Photography", "Video Editing", "3D Modeling",
        "Animation", "Motion Graphics", "Brand Design", "Typography", "Print Design", "Web Design",
        "UI Design", "UX Research", "Product Design", "Fashion Design", "Interior Design",
        "Industrial Design", "Packaging Design", "Art Direction", "Creative Direction"
    ],
    business: [
        "Project Management", "Leadership", "Strategic Planning", "Business Development",
        "Marketing Strategy", "Sales Techniques", "Financial Analysis", "Entrepreneurship",
        "Time Management", "Team Building", "Conflict Resolution", "Business Writing",
        "Data Analysis", "Digital Marketing", "Social Media Management", "Customer Service",
        "Human Resources", "Risk Management", "Supply Chain Management", "Quality Assurance"
    ],
    marketing: [
        "Digital Marketing", "Social Media Marketing", "SEO", "Content Marketing", 
        "Email Marketing", "Copywriting", "Brand Strategy", "Analytics", "PPC Advertising",
        "Influencer Marketing", "Market Research", "Public Relations", "Event Marketing",
        "Video Marketing", "Affiliate Marketing", "Growth Hacking", "Marketing Automation"
    ],
    writing: [
        "Content Writing", "Copywriting", "Technical Writing", "Creative Writing", 
        "Editing", "Proofreading", "Blogging", "Journalism", "Scriptwriting",
        "Business Writing", "Academic Writing", "Grant Writing", "Resume Writing",
        "Social Media Writing", "Email Writing", "Web Content", "SEO Writing"
    ],
    education: [
        "Tutoring", "Curriculum Development", "Training", "Instructional Design", 
        "Public Speaking", "Mentoring", "Workshop Facilitation", "E-Learning",
        "Educational Technology", "Assessment Design", "Classroom Management",
        "Adult Education", "Online Teaching", "Course Creation", "Educational Consulting"
    ],
    health: [
        "Nutrition Counseling", "Fitness Training", "Yoga Instruction", "Meditation", 
        "Mental Health Support", "Physical Therapy", "Health Coaching", "Wellness Planning",
        "Sports Coaching", "Rehabilitation", "Holistic Health", "Stress Management",
        "Weight Management", "Lifestyle Coaching", "Exercise Programming"
    ],
    home: [
        "Basic Plumbing", "Electrical Repairs", "Carpentry", "Painting & Decorating",
        "Gardening", "Landscaping", "Home Organization", "Furniture Assembly", 
        "Appliance Repair", "Home Maintenance", "DIY Projects", "Interior Design", 
        "Cleaning Techniques", "Pest Control", "Home Security", "Energy Efficiency", 
        "Renovation Planning", "Tool Usage & Safety", "Wallpaper Installation", "Tile Setting"
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.getElementById('createEventForm');
    const categorySelect = document.getElementById('category');
    const skillSelect = document.getElementById('skill');
    const addSkillBtn = document.getElementById('addSkillBtn');
    const selectedSkillsContainer = document.getElementById('selectedSkills');
    const skillsCountSpan = document.querySelector('.skills-count');
    
    // Selected skills array
    let selectedSkills = [];
    const MAX_SKILLS = 5;
    
    // Update skill options when category changes
    categorySelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        skillSelect.innerHTML = '<option value="">Select skill</option>';
        
        if (selectedCategory && skillsData[selectedCategory]) {
            skillsData[selectedCategory].forEach(skill => {
                const option = document.createElement('option');
                option.value = skill;
                option.textContent = skill;
                skillSelect.appendChild(option);
            });
            skillSelect.disabled = false;
        } else {
            skillSelect.disabled = true;
            skillSelect.innerHTML = '<option value="">Select category first</option>';
        }
    });
    
    // Add skill functionality
    addSkillBtn.addEventListener('click', function() {
        const category = categorySelect.value;
        const skill = skillSelect.value;
        
        if (!category) {
            showError('Please select a category first');
            return;
        }
        
        if (!skill) {
            showError('Please select a skill');
            return;
        }
        
        if (selectedSkills.length >= MAX_SKILLS) {
            showError(`Maximum ${MAX_SKILLS} skills allowed`);
            return;
        }
        
        // Check if skill already added
        const skillExists = selectedSkills.some(s => s.skill === skill);
        if (skillExists) {
            showError('This skill is already added');
            return;
        }
        
        // Add skill to array
        selectedSkills.push({ category, skill });
        
        // Update UI
        updateSkillsDisplay();
        updateSkillsCount();
        
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
                <span>${skillObj.skill}</span>
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
        
        // Event date validation
        const eventDate = document.getElementById('eventDate');
        if (!eventDate.value) {
            showFieldError(eventDate, 'Event date is required');
            isValid = false;
        } else {
            const selectedDate = new Date(eventDate.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError(eventDate, 'Event date cannot be in the past');
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
        
        // Gather form data
        const formData = {
            title: document.getElementById('eventTitle').value.trim(),
            date: document.getElementById('eventDate').value,
            location: document.getElementById('location').value.trim(),
            description: document.getElementById('description').value.trim(),
            maxAttendees: document.getElementById('maxAttendees').value,
            skills: selectedSkills
        };
        
        console.log('Event Created:', formData);
        
        // Show success message
        showSuccess('Event created successfully!');
        
        // Reset form after 2 seconds
        setTimeout(() => {
            form.reset();
            selectedSkills = [];
            updateSkillsDisplay();
            updateSkillsCount();
            skillSelect.disabled = true;
            skillSelect.innerHTML = '<option value="">Select category first</option>';
        }, 2000);
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