
document.addEventListener("DOMContentLoaded", () => {

    const questions = document.querySelectorAll(".question");

    questions.forEach(q => {
        const btn = q.querySelector(".question-button");
        const answer = q.querySelector(".answer");
        const icon = q.querySelector(".plus-icon");

        btn.addEventListener("click", () => {
            const isOpen = answer.style.maxHeight && answer.style.maxHeight !== "0px";

            if (!isOpen) {
                // Open answer
                answer.style.maxHeight = answer.scrollHeight + "px";
                answer.style.paddingTop = "10px";
                answer.style.paddingBottom = "15px";
                icon.textContent = "-";
            } else {
                // Close answer
                answer.style.maxHeight = "0";
                answer.style.paddingTop = "0";
                answer.style.paddingBottom = "0";
                icon.textContent = "+";
            }
        });
    });

    // Active nav-link highlight
    const currentPage = window.location.pathname.split("/").pop();
    const links = document.querySelectorAll(".nav-link");

    links.forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });
});




 //forms validation 
 // The common validation functions
function isValidEmail(email) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
    return regex.test(email);
}

function isValidName(name) {
    const regex = /^[a-zA-Z]{2,30}$/;
    return regex.test(name);
}

function isValidPassword(password) {
    // Min 6 chars, at least 1 letter and 1 number
    const regex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
    return regex.test(password);
}

function showError(input, message) {
    const formGroup = input.closest('.form-group');
    let errorEl = formGroup.querySelector('.error-message');
    if(!errorEl) {
        errorEl = document.createElement('small');
        errorEl.className = 'error-message';
        formGroup.appendChild(errorEl);
    }
    errorEl.textContent = message;
    input.classList.add('invalid');
}

function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorEl = formGroup.querySelector('.error-message');
    if(errorEl) formGroup.removeChild(errorEl);
    input.classList.remove('invalid');
}
//Contact us form validation
const contactForm = document.querySelector('.contact-form');

if(contactForm) {
    contactForm.addEventListener('submit', function(event) {
        event.preventDefault();
        let valid = true;

        const firstName = document.getElementById('first-name');
        const lastName = document.getElementById('last-name');
        const email = document.getElementById('email');
        const message = document.getElementById('message');

        clearError(firstName);
        clearError(lastName);
        clearError(email);
        clearError(message);

        if(!isValidName(firstName.value)) {
            showError(firstName, 'Please enter a valid first name');
            valid = false;
        }
        if(!isValidName(lastName.value)) {
            showError(lastName, 'Please enter a valid last name');
            valid = false;
        }
        if(!isValidEmail(email.value)) {
            showError(email, 'Please enter a valid email');
            valid = false;
        }
        if(message.value.trim().length < 5) {
            showError(message, 'Message should be at least 5 characters');
            valid = false;
        }

        if(valid) contactForm.submit();
    });
}
//sign up form validation 
const signupForm = document.querySelector('.signup-form');

if(signupForm) {
    signupForm.addEventListener('submit', function(event) {
        event.preventDefault();
        let valid = true;

        const fullName = document.getElementById('fullName');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const location = document.getElementById('location');
        const terms = document.getElementById('terms');

        clearError(fullName);
        clearError(email);
        clearError(password);
        clearError(confirmPassword);
        clearError(location);

        if(!isValidName(fullName.value)) {
            showError(fullName, 'Please enter a valid name');
            valid = false;
        }
        if(!isValidEmail(email.value)) {
            showError(email, 'Please enter a valid email');
            valid = false;
        }
        if(!isValidPassword(password.value)) {
            showError(password, 'Password must be at least 6 characters and contain letters and numbers');
            valid = false;
        }
        if(password.value !== confirmPassword.value) {
            showError(confirmPassword, 'Passwords do not match');
            valid = false;
        }
        if(location.value.trim() === '') {
            showError(location, 'Please enter your location');
            valid = false;
        }
        if(!terms.checked) {
            alert('You must agree to terms and conditions');
            valid = false;
        }

        if(valid) signupForm.submit();
    });
}

//login form validation 
const loginForm = document.querySelector('.login-form');

if(loginForm) {
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        let valid = true;

        const email = document.getElementById('email');
        const password = document.getElementById('password');

        clearError(email);
        clearError(password);

        if(!email.value.trim()) {
            showError(email, 'Email or username is required');
            valid = false;
        }
        if(!password.value.trim()) {
            showError(password, 'Password is required');
            valid = false;
        }

        if(valid) loginForm.submit();
    });
}
// Adding a skill in the second sign up page
document.addEventListener("DOMContentLoaded", () => {
    let currentSkillType = '';
    let teachSkills = [];
    let learnSkills = [];

    // Get DOM elements
    const modal = document.getElementById('skillModal');
    const closeBtn = document.querySelector('.close');
    const skillNameInput = document.getElementById('skillName');
    const skillLevelSelect = document.getElementById('skillLevel');

    // Open modal
    window.addSkill = function(type) {
        currentSkillType = type;
        const categorySelect = document.getElementById(type + 'Category');

        if (!categorySelect.value) {
            alert('Please select a category first');
            return;
        }

        // Reset modal inputs
        skillNameInput.value = '';
        skillLevelSelect.value = type === 'teach' ? 'intermediate' : 'beginner';
        
        // Show modal
        modal.style.display = 'block';
    };

    // Save skill
    window.saveSkill = function() {
        const skillName = skillNameInput.value.trim();
        const skillLevel = skillLevelSelect.value;

        if (!skillName) {
            alert('Please enter a skill name');
            return;
        }

        const skillData = {
            name: skillName,
            level: skillLevel,
            category: document.getElementById(currentSkillType + 'Category').value
        };

        if (currentSkillType === 'teach') {
            if (teachSkills.length >= 5) {
                alert('Maximum 5 teaching skills allowed');
                return;
            }
            teachSkills.push(skillData);
            updateSkillsDisplay('teach');
        } else {
            if (learnSkills.length >= 5) {
                alert('Maximum 5 learning skills allowed');
                return;
            }
            learnSkills.push(skillData);
            updateSkillsDisplay('learn');
        }

        // Close modal
        modal.style.display = 'none';
    };

    // Update display
    function updateSkillsDisplay(type) {
        const container = document.getElementById(type + 'Skills');
        const array = type === 'teach' ? teachSkills : learnSkills;
        const counter = document.getElementById(type + 'Counter') || 
                       container.closest('.skills-section').querySelector('.skills-counter');

        counter.textContent = `${array.length}/5 skills selected`;

        container.innerHTML = array.map((skill, index) => `
            <div class="skill-tag">
                <span class="skill-name">${skill.name}</span>
                <span class="skill-level">(${skill.level})</span>
                <span class="remove" onclick="removeSkill('${type}', ${index})">×</span>
            </div>
        `).join('');
    }

    // Remove skill
    window.removeSkill = function(type, index) {
        if (type === 'teach') {
            teachSkills.splice(index, 1);
            updateSkillsDisplay('teach');
        } else {
            learnSkills.splice(index, 1);
            updateSkillsDisplay('learn');
        }
    };

    // Modal closing
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Add Enter key support for skill input
    if (skillNameInput) {
        skillNameInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                window.saveSkill();
            }
        });
    }

    // Form validation before submission
    const skillsForm = document.querySelector('form[action*="signup3.html"]');
    if (skillsForm) {
        skillsForm.addEventListener('submit', (event) => {
            // Check if at least one skill is added in each section
            if (teachSkills.length === 0) {
                alert('Please add at least one skill you can teach');
                event.preventDefault();
                return;
            }
            
            if (learnSkills.length === 0) {
                alert('Please add at least one skill you want to learn');
                event.preventDefault();
                return;
            }

            // Add skills data to form as hidden inputs for submission
            teachSkills.forEach((skill, index) => {
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = `teachSkills[${index}][name]`;
                nameInput.value = skill.name;
                skillsForm.appendChild(nameInput);

                const levelInput = document.createElement('input');
                levelInput.type = 'hidden';
                levelInput.name = `teachSkills[${index}][level]`;
                levelInput.value = skill.level;
                skillsForm.appendChild(levelInput);

                const categoryInput = document.createElement('input');
                categoryInput.type = 'hidden';
                categoryInput.name = `teachSkills[${index}][category]`;
                categoryInput.value = skill.category;
                skillsForm.appendChild(categoryInput);
            });

            learnSkills.forEach((skill, index) => {
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = `learnSkills[${index}][name]`;
                nameInput.value = skill.name;
                skillsForm.appendChild(nameInput);

                const levelInput = document.createElement('input');
                levelInput.type = 'hidden';
                levelInput.name = `learnSkills[${index}][level]`;
                levelInput.value = skill.level;
                skillsForm.appendChild(levelInput);

                const categoryInput = document.createElement('input');
                categoryInput.type = 'hidden';
                categoryInput.name = `learnSkills[${index}][category]`;
                categoryInput.value = skill.category;
                skillsForm.appendChild(categoryInput);
            });
        });
    }
});