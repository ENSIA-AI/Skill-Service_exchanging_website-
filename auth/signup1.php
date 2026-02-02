<?php
session_start();

include_once '../utils/sendMailer.php';
include_once '../DataBaseManagement/config.php';

// Initialize session signup data if not exists
if (!isset($_SESSION['signup_data'])) {
    $_SESSION['signup_data'] = [
        'fullName' => '',
        'email' => '',
        'gender' => '',
        'phone' => '',
        'birthdate' => '',
        'location' => '',
        'terms' => false
    ];
}

// Clear verification session if user comes back
if (isset($_GET['resend'])) {
   
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_expiry']);
}

// Initialize error and success message variables
$errors = [];
$success_message = '';

// Form submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $gender = trim($_POST['gender'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $terms = isset($_POST['terms']);

    // Storing form data in session for persistence
    $_SESSION['signup_data'] = [
        'fullName' => $fullName,
        'email' => $email,
        'password' => $password,
        'gender' => $gender,
        'phone' => $phone,
        'birthdate' => $birthdate,
        'location' => $location,
        'terms' => $terms
    ];

    // Initialize error fields for each form field
    $errors = [
        'fullName' => '',
        'email' => '',
        'password' => '',
        'confirmPassword' => '',
        'gender' => '',
        'phone' => '',
        'birthdate' => '',
        'location' => '',
        'terms' => ''
    ];

    // Validation functions 
    function isValidName($name) {
        return preg_match('/^[a-zA-Z\s]{2,30}$/', $name);
    }

    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function isValidPassword($password) {
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password);
    }

    function isValidAlgerianPhone($phone) {
        return preg_match('/^(?:\+213\s?)?(?:0)?(5|6|7)[0-9]{8}$/', $phone);
    }

    function isValidBirthdate($birthdate) {
        $bd = new DateTime($birthdate);
        $today = new DateTime();
        
        if ($bd > $today) {
            return ['valid' => false, 'error' => 'Birth date cannot be in the future'];
        }
        
        if ($bd->format('Y') < 1900) {
            return ['valid' => false, 'error' => 'Year must be 1900 or later'];
        }
        
        return ['valid' => true];
    }

    // Validating all the fields
    // Fullname 
    if (!$fullName) {
        $errors['fullName'] = 'Full name is required';
    } elseif (!isValidName($fullName)) {
        $errors['fullName'] = 'Please enter a valid fullname';
    } else {
        $errors['fullName'] = '';
    }

    // Email
    if (!$email) {
        $errors['email'] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Checking if the given email already exists in the database
        $checkEmail = $conn->prepare("SELECT UserId FROM Users WHERE Email = ?");
        $checkEmail->bind_param('s', $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $errors['email'] = 'Email already registered';
        } else {
            $errors['email'] = '';
        }
        $checkEmail->close();
    }

    // Password 
    if (!$password) {
        $errors['password'] = 'Password is required';
    } elseif (!isValidPassword($password)) {
        $errors['password'] = 'Password must be at least 6 characters with letters and numbers';
    } else {
        $errors['password'] = '';
    }

    // Confirm password 
    if (!$confirmPassword) {
        $errors['confirmPassword'] = 'Please confirm your password';
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match';
    } else {
        $errors['confirmPassword'] = '';
    }

    // Gender
    if (!$gender || !in_array($gender, ['M', 'F'])) {
        $errors['gender'] = 'Please select a valid gender';
    } else {
        $errors['gender'] = '';
    }

    // Phone 
    if (!$phone) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!isValidAlgerianPhone($phone)) {
        $errors['phone'] = 'Please enter a valid Algerian phone number (05/06/07 followed by 8 digits)';
    } else {
        $errors['phone'] = '';
    }

    // Birthdate
    if (!$birthdate) {
        $errors['birthdate'] = 'Birth date is required';
    } else {
        $bdValidation = isValidBirthdate($birthdate);
        if (!$bdValidation['valid']) {
            $errors['birthdate'] = $bdValidation['error'];
        } else {
            $errors['birthdate'] = '';
        }
    }

    // Location
    if (!$location) {
        $errors['location'] = 'Location is required';
    } elseif (strlen($location) < 2 || strlen($location) > 100) {
        $errors['location'] = 'Location must be between 2 and 100 characters';
    } else {
        $errors['location'] = '';
    }

    // Terms 
    if (!$terms) {
        $errors['terms'] = 'You must agree to terms and conditions';
    } else {
        $errors['terms'] = '';
    }

    // Check if any error exists
    if (empty(array_filter($errors))) {
        // Generate 6-digit verification code
        $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store verification data in session (valid for 10 minutes)
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['verification_expiry'] = time() + (10 * 60); // 10 minutes
        
        // Store hashed password in session for later use
        $_SESSION['signup_data']['hashed_password'] = password_hash($password, PASSWORD_BCRYPT);
        
        // Send verification email
        $subject = 'Your Verification Code - Skill Service Exchange';
        $message = "Hello " . htmlspecialchars($fullName) . ",\n\n";
        $message .= "Your verification code is: " . $verificationCode . "\n\n";
        $message .= "This code will expire in 10 minutes.\n\n";
        $message .= "If you didn't request this code, please ignore this email.\n\n";
        $message .= "Best regards,\nSkill Service Exchange Team";
        
        $headers = "From: noreply@skillserviceexchange.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
      
    

      if (sendVerificationEmail($email, $fullName, $verificationCode)) {
      header("Location: signup_verify.php");
      exit();
    }   else {
    $errors['email'] = 'Email could not be sent.';
    }








    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/signuppages.css">
    <title>Sign Up - Personal Information</title>
</head>
<body>
    <div class="signup-container">
        <!-- Left Side - Image/Illustration -->
        <div class="signup-left">
            <div class="illustration">
                <img src="../assets/images/homeinp/signupside.jpg" alt="Join Community">
            </div>
        </div>

        <!-- Right Side - Signup Form -->
        <div class="signup-right">
            <h2>Personal Information</h2>
            
            <form action="signup1.php" method="post" class="signup-form" id="signupForm">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" placeholder="John Smith" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['fullName'] ?? ''); ?>">
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="john@example.com" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['email'] ?? ''); ?>">
                    <small class="error-message"></small>
                    <small id="emailMsg"></small>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    <small class="error-message"></small>
                </div>
             
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled selected>Select your gender</option>
                        <option value="M" <?php echo ($_SESSION['signup_data']['gender'] ?? '') === 'M' ? 'selected' : ''; ?>>Male</option>
                        <option value="F" <?php echo ($_SESSION['signup_data']['gender'] ?? '') === 'F' ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 234 567 8900" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['phone'] ?? ''); ?>">
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="birthdate">Birth Date</label>
                    <input type="date" id="birthdate" name="birthdate" min="1900-01-01" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['birthdate'] ?? ''); ?>">
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="City, Country" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['location'] ?? ''); ?>">
                    <small class="error-message"></small>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" required <?php echo ($_SESSION['signup_data']['terms'] ?? false) ? 'checked' : ''; ?>>
                    <label for="terms">I agree to Terms & Conditions</label>
                </div>

                <button type="submit" class="btn-next">Verify Email</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>

    <script src="../assets/js/formvalidation.js"></script>


    <script>
   const emailInput = document.getElementById("email");
    const msg = document.getElementById("emailMsg");
    emailInput.addEventListener("blur", function () {

    let email = this.value.trim();
    if(email === "") return;
    fetch("checkEmail.php?email=" + encodeURIComponent(email))
        .then(res => res.json())
        .then(data => {

            if(data.exists){
                msg.textContent = "Email already registered ";
                msg.style.color = "red";
            }
            else{
                msg.textContent = "Email available ";
                msg.style.color = "green";
            }

        });

});

</script>
   
    <?php
    // Display any PHP validation errors in the form
    if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        echo '<script>';
        foreach ($errors as $field => $message) {
            if (!empty($message)) {
                echo "
                (function() {
                    const input = document.getElementById('$field');
                    if (input) {
                        const formGroup = input.closest('.form-group') || input.closest('.form-check');
                        if (formGroup) {
                            let errorEl = formGroup.querySelector('.error-message');
                            if (!errorEl) {
                                errorEl = document.createElement('small');
                                errorEl.className = 'error-message';
                                formGroup.appendChild(errorEl);
                            }
                            errorEl.textContent = '$message';
                            input.classList.add('invalid');
                        }
                    }
                })();
                ";
            }
        }
        echo '</script>';
    }
    ?>
</body>
</html>