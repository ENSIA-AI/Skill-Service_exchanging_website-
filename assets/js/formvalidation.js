

 //forms validation 
 // The common validation functions
function isValidEmail(email) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
    return regex.test(email);
}

function isValidName(name) {
   
    const regex = /^[a-zA-Z\s]{2,30}$/;
    return regex.test(name);
}

function isValidPassword(password) {
    // Min 6 chars, at least 1 letter and 1 number
    const regex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
    return regex.test(password);
}

function isValidAlgerianPhone(phone) {
   
    const regex = /^(?:\+213\s?)?(?:0)?(5|6|7)[0-9]{8}$/;
    return regex.test(phone);
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
        const phone = document.getElementById('phone');
        const gender = document.getElementById('gender');
        const birthdate = document.getElementById('birthdate');

        clearError(fullName);
        clearError(email);
        clearError(password);
        clearError(confirmPassword);
        clearError(location);
        clearError(phone);

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
         if(!isValidAlgerianPhone(phone.value)) {
            showError(phone, 'Please enter a valid Algerian phone number (05/06/07 followed by 8 digits)');
            valid = false;
        }

        if(!gender.value || gender.value === '') {
       showError(gender, 'Please select your gender');
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