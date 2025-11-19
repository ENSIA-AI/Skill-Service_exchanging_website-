// login.js - Import all necessary functions from home.js
import { 
    isValidEmail, 
    showError, 
    clearError,
    setErrorFor,
    setSuccessFor 
} from './home.js';

document.addEventListener("DOMContentLoaded", () => {
    // Select fields based on HTML structure
    const Email = document.querySelector(".email .input-box");
    const Password = document.querySelector(".password .input-box");
    const rememberMe = document.querySelector(".remember-me input");
    const submitBtn = document.querySelector(".login-btn");

    // Add <small> error message boxes to each input field
    document.querySelectorAll(".email, .password").forEach((box) => {
        const small = document.createElement("small");
        small.classList.add("error-message");
        box.appendChild(small);
    });

    submitBtn.addEventListener("click", (event) => {
        event.preventDefault();
        checkInputs();
    });

    function checkInputs() {
        const EmailValue = Email.value.trim();
        const PasswordValue = Password.value.trim();

        // Email Validation - Using imported functions
        if (EmailValue === "") {
            setErrorFor(Email, "Email cannot be blank");
        } else if (!isValidEmail(EmailValue)) {
            setErrorFor(Email, "Email is not valid");
        } else {
            setSuccessFor(Email);
        }

        // Password Validation
        if (PasswordValue === "") {
            setErrorFor(Password, "Password cannot be blank");
        } else if (PasswordValue.length < 6) {
            setErrorFor(Password, "Password must be at least 6 characters");
        } else {
            setSuccessFor(Password);
        }

        // Remember Me
        if (rememberMe.checked && EmailValue !== "") {
            localStorage.setItem("rememberedUser", EmailValue);
        } else {
            localStorage.removeItem("rememberedUser");
        }
    }

    // Auto-fill when loading
    window.addEventListener("load", () => {
        const savedUser = localStorage.getItem("rememberedUser");
        if (savedUser) {
            Email.value = savedUser;
            rememberMe.checked = true;
        }
        Email.value = "";
        Password.value = "";
        rememberMe.checked = false;
    });
});