<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- UPDATED LINK: Pointing to public/assets -->
    <link rel="icon" type="image/png" href="<?= '/Skill-Service_exchanging_website-/public/assets/images/favicon.png' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/login.css' ?>">
    <title>Login - SkillSwap</title>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="login-left">
            <div class="illustration">
                <!-- UPDATED LINK -->
                <img src="<?= '/Skill-Service_exchanging_website-/public/assets/images/7943139_3779554.svg' ?>" alt="Welcome Back">
            </div>
        </div>

        <!-- Right Side -->
        <div class="login-right">
            <div class="logo">
                <h1>Swap</h1>
            </div>
            
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to continue your learning journey</p>
            
            <div class="divider"></div>
            
            <!-- UPDATED ACTION: Pointing to the controller route -->
            <!-- Was: ../components/posts.html -->
            <form action="<?= '/Skill-Service_exchanging_website-/public/auth/login' ?>" method="POST" class="login-form">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert-error" style="color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                        <?= htmlspecialchars($data['error']) ?>
                    </div>
                <?php endif; ?>
                <div class="form-group email">
                    <label for="email">Email or Username</label>
                    <input type="text" id="email" name="email" placeholder="Enter your email or username" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group password">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="<?= '/Skill-Service_exchanging_website-/public/auth/forgot_password' ?>" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-login" disabled>Login</button>
            </form>

            <p class="signup-link">
                Don't have an account? <a href="<?= '/Skill-Service_exchanging_website-/public/auth/signup' ?>">Sign Up</a>
            </p>
        </div>
    </div>

   <script> 
    window.addEventListener("load", () => {
    const form = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMe = document.getElementById('remember');
    const submitBtn = form.querySelector('.btn-login');
    
    let emailTouched = false;
    let passwordTouched = false;
    
    function showError(input, message) {
        const errorMsg = input.nextElementSibling;
        errorMsg.textContent = message;
        input.classList.add('error');
    }
    
    function clearError(input) {
        const errorMsg = input.nextElementSibling;
        errorMsg.textContent = '';
        input.classList.remove('error');
    }
    
    function isValidEmail(email) {
        return /\S+@\S+\.\S+/.test(email);
    }
    
    function isValidPassword(password) {
        return password.length >= 6 && /[A-Za-z]/.test(password) && /\d/.test(password);
    }
    
    emailInput.addEventListener('focus', () => {
        emailTouched = true;
    });
    
    passwordInput.addEventListener('focus', () => {
        passwordTouched = true;
    });
    
    [emailInput, passwordInput].forEach(input => {
        input.addEventListener('input', () => {
            if (input === emailInput && emailTouched) {
                if (!input.value.trim()) showError(input, "Email is required");
                else if (!isValidEmail(input.value.trim())) showError(input, "Email is not valid");
                else clearError(input);
            }
            if (input === passwordInput && passwordTouched) {
                if (!input.value.trim()) showError(input, "Password is required");
                else if (!isValidPassword(input.value.trim())) showError(input, "Password must be at least 6 characters and contain letters and numbers");
                else clearError(input);
            }
        });
    });
    
    // UPDATED SUBMISSION LOGIC
    // We want the form to submit to the server (PHP), so we might want to remove the preventDefault() 
    // OR change the window.location.href to match the new route if we are mocking it.
    // The original code had specific JS validation. I will keep it.
    // Ideally, we should remove the manual redirection and let the form submit to the Controller.
    
    form.addEventListener("submit", (event) => {
        // event.preventDefault(); // allow form submission to server
        // But the original code was doing client-side redirect? 
        // "window.location.href = "/Skill-Service_exchanging_website-/dashboard/post/posts.php";"
        // If we want real auth, we should let it submit to PHP.
        // For now, I will let it submit if valid.
        
        let valid = true;
        
        emailTouched = true;
        passwordTouched = true;
        
        // Validation logic...
        if (!emailInput.value.trim()) { showError(emailInput, "Email is required"); valid = false; }
        else if (!isValidEmail(emailInput.value.trim())) { showError(emailInput, "Please enter a valid email"); valid = false; }
        
        if (!passwordInput.value.trim()) { showError(passwordInput, "Password is required"); valid = false; }
        else if (!isValidPassword(passwordInput.value.trim())) { showError(passwordInput, "Please enter a valid password"); valid = false; }
        
        if (!valid) {
            event.preventDefault(); // Stop submission if invalid
            return;
        }
        
        // If valid, the form will submit to action="..." which is /auth/login (POST)
    });
    
    submitBtn.disabled = false;
});
</script>
</body>
</html>
