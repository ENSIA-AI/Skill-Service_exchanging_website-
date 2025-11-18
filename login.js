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

  // Email Validation
  if (EmailValue === "") {
    setErrorFor(Email, "Email cannot be blank");
  } else if (!isEmail(EmailValue)) {
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

// Functions

function setErrorFor(input, message) {
  const parent = input.parentElement;
  const small = parent.querySelector("small");

  small.innerText = message;

  input.classList.remove("success");
  input.classList.add("error");
}

function setSuccessFor(input) {
  const parent = input.parentElement;
  const small = parent.querySelector("small");

  small.innerText = "";

  input.classList.remove("error");
  input.classList.add("success");
}

function isEmail(email) {
  return /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/.test(email);
}

// auto-fill when loading
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
