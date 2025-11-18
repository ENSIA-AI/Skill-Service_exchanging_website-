
//Home page 

document.addEventListener("DOMContentLoaded",()=>
{
 
    const questions = document.querySelectorAll(".question");
    questions.forEach(q =>
     {
       const btn =q.querySelector(".question-button")
       const answer =q.querySelector(".answer");
       const icon = q.querySelector(".plus-icon");
       btn.addEventListener("click",() =>
        {
          const isOpen =answer.style.maxHeight && answer.style.maxHeight !== "0px"
             
           if (!isOpen) 
           {
                // if the answer closed we open it  
                answer.style.maxHeight = answer.scrollHeight +"px";
                answer.style.paddingTop = "10px";
                answer.style.paddingBottom = "15px";
                icon.textContent = "-";
            } else {
                // if the answer is open close it 
                answer.style.maxHeight = "0";
                answer.style.paddingTop = "0";
                answer.style.paddingBottom = "0";
                icon.textContent = "+";
            }

        });


    });

});
  

        const currentPage = window.location.pathname.split("/").pop();  
        const links = document.querySelectorAll(".nav-link");

          
        links.forEach(link => {
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active");
            }

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
const signupForm = document.querySelector('.signup-right form');

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
const loginForm = document.querySelector('.signup-right form');

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

//Adding a skill  in the second sign up page  
  let currentSkillType = '';
        let teachSkills = [];
        let learnSkills = [];

        function addSkill(type) {
            currentSkillType = type;
            const categorySelect = document.getElementById(type + 'Category');
            
            if (!categorySelect.value) {
                alert('Please select a category first');
                return;
            }

            document.getElementById('skillModal').style.display = 'block';
            document.getElementById('skillName').value = '';
            document.getElementById('skillLevel').value = 'intermediate';
        }

        function saveSkill() {
            const skillName = document.getElementById('skillName').value.trim();
            const skillLevel = document.getElementById('skillLevel').value;

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
                    alert('Maximum 5 skills allowed for teaching');
                    return;
                }
                teachSkills.push(skillData);
                updateSkillsDisplay('teach');
            } else {
                if (learnSkills.length >= 5) {
                    alert('Maximum 5 skills allowed for learning');
                    return;
                }
                learnSkills.push(skillData);
                updateSkillsDisplay('learn');
            }

            document.getElementById('skillModal').style.display = 'none';
        }

        function updateSkillsDisplay(type) {
            const container = document.getElementById(type + 'Skills');
            const skillsArray = type === 'teach' ? teachSkills : learnSkills;
            const counter = document.querySelector(`#${type}Skills`).closest('.skills-section').querySelector('.skills-counter');
            
            counter.textContent = `${skillsArray.length}/5 skills selected`;
            
            container.innerHTML = skillsArray.map((skill, index) => `
                <div class="skill-tag">
                    <span class="skill-name">${skill.name}</span>
                    <span class="skill-level">(${skill.level})</span>
                    <span class="remove" onclick="removeSkill('${type}', ${index})">×</span>
                </div>
            `).join('');
        }

        function removeSkill(type, index) {
            if (type === 'teach') {
                teachSkills.splice(index, 1);
                updateSkillsDisplay('teach');
            } else {
                learnSkills.splice(index, 1);
                updateSkillsDisplay('learn');
            }
        }

        // Modal functionality
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('skillModal').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('skillModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
 
   