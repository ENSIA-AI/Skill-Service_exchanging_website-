// Skills data for each category
const skillsData = {
    technology: [
        "Web Development",
        "Mobile App Development",
        "Data Science",
        "Machine Learning",
        "Artificial Intelligence",
        "Cybersecurity",
        "Cloud Computing",
        "Database Management",
        "UI/UX Design",
        "Software Engineering",
        "Python Programming",
        "JavaScript Development",
        "Java Programming",
        "C++ Development",
        "Game Development",
        "DevOps",
        "Blockchain",
        "IoT Development",
        "API Development",
        "Quality Assurance"
    ],
    creative: [
        "Graphic Design",
        "Digital Illustration",
        "Photography",
        "Video Editing",
        "3D Modeling",
        "Animation",
        "Motion Graphics",
        "Brand Design",
        "Typography",
        "Print Design",
        "Web Design",
        "UI Design",
        "UX Research",
        "Product Design",
        "Fashion Design",
        "Interior Design",
        "Industrial Design",
        "Packaging Design",
        "Art Direction",
        "Creative Direction"
    ],
    languages: [
        "English Conversation",
        "Business English",
        "Spanish",
        "French",
        "German",
        "Chinese Mandarin",
        "Japanese",
        "Arabic",
        "Russian",
        "Italian",
        "Portuguese",
        "Public Speaking",
        "Presentation Skills",
        "Negotiation",
        "Interview Preparation",
        "Accent Reduction",
        "TOEFL/IELTS Preparation",
        "Translation",
        "Proofreading",
        "Creative Writing"
    ],
    business: [
        "Project Management",
        "Leadership",
        "Strategic Planning",
        "Business Development",
        "Marketing Strategy",
        "Sales Techniques",
        "Financial Analysis",
        "Entrepreneurship",
        "Time Management",
        "Team Building",
        "Conflict Resolution",
        "Business Writing",
        "Data Analysis",
        "Digital Marketing",
        "Social Media Management",
        "Customer Service",
        "Human Resources",
        "Risk Management",
        "Supply Chain Management",
        "Quality Assurance"
    ],
    home: [
        "Basic Plumbing",
        "Electrical Repairs",
        "Carpentry",
        "Painting & Decorating",
        "Gardening",
        "Landscaping",
        "Home Organization",
        "Furniture Assembly",
        "Appliance Repair",
        "Home Maintenance",
        "DIY Projects",
        "Interior Design",
        "Cleaning Techniques",
        "Pest Control",
        "Home Security",
        "Energy Efficiency",
        "Renovation Planning",
        "Tool Usage & Safety",
        "Wallpaper Installation",
        "Tile Setting"
    ],
    fitness: [
        "Personal Training",
        "Yoga Instruction",
        "Pilates",
        "Meditation",
        "Nutrition Coaching",
        "Weight Training",
        "Cardio Training",
        "Martial Arts",
        "Dance Fitness",
        "Sports Coaching",
        "Strength & Conditioning",
        "Flexibility Training",
        "Posture Correction",
        "Injury Prevention",
        "Rehabilitation Exercises",
        "Group Fitness",
        "Boxing",
        "Swimming",
        "Cycling",
        "Running Technique"
    ]
};

// Initialize skills when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateSkills();
});

function updateSkills() {
    const categorySelect = document.getElementById('skillsCategory');
    const skillSelect = document.getElementById('skillsSkill');
    
    const selectedCategory = categorySelect.value;
    
    // Clear existing skills
    skillSelect.innerHTML = '<option value="">Select a skill from the list</option>';
    
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
        alert('Please select a skill');
        return;
    }
    
    // Count current skills
    const currentSkills = skillsList.querySelectorAll('.skill-item').length;
    if (currentSkills >= 5) {
        alert('Maximum 5 skills allowed');
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

// Form submission handling
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selectedSkills = Array.from(document.querySelectorAll('.skill-item')).map(item => 
        item.textContent.replace('×', '').trim()
    );
    
    const formData = {
        title: document.getElementById('event-title').value,
        date: document.getElementById('event-date').value,
        location: document.getElementById('location').value,
        description: document.getElementById('description').value,
        maxAttendees: document.getElementById('max-attendees').value,
        category: document.getElementById('skillsCategory').value,
        skills: selectedSkills
    };
    
    console.log('Form submitted:', formData);
    alert('Event created successfully!');

    // this.submit();
});

// Cancel button
document.querySelector('.cancel-button').addEventListener('click', function() {
    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
        window.history.back();
    }
});