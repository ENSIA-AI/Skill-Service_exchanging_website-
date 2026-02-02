<?php
session_start();

require_once '../utils/sendMailer.php';
include_once '../DataBaseManagement/config.php';

// Prevent direct access if signup1.php data is missing
if (!isset($_SESSION['signup_data']) || !isset($_SESSION['verification_code'])) {
    header("Location: signup1.php");
    exit();
}

// Initialize variables
$error = '';
$verification_error = '';
$success_message = '';

// Check if verification code is expired
if (isset($_SESSION['verification_expiry']) && time() > $_SESSION['verification_expiry']) {
    $error = "Verification code has expired. Please start over.";
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_expiry']);
}

// Handle form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle resend request
    if (isset($_POST['resend'])) {
        // Generate new verification code
        $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store new verification data in session
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['verification_expiry'] = time() + (10 * 60); // 10 minutes
        
        // Get email from session
        $email = $_SESSION['signup_data']['email'];
        $fullName = $_SESSION['signup_data']['fullName'];
        
        // Send new verification email
        $subject = 'Your New Verification Code - Skill Service Exchange';
        $message = "Hello " . htmlspecialchars($fullName) . ",\n\n";
        $message .= "Your new verification code is: " . $verificationCode . "\n\n";
        $message .= "This code will expire in 10 minutes.\n\n";
        $message .= "If you didn't request this code, please ignore this email.\n\n";
        $message .= "Best regards,\nSkill Service Exchange Team";
        
        $headers = "From: noreply@skillserviceexchange.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
     

    if (sendVerificationEmail($email, $fullName, $verificationCode)) {
    $success_message = "New verification code sent.";
   } else {
    $verification_error = "Failed to send email.";
     }

    } 
    // Handle verification code submission
    elseif (isset($_POST['verify'])) {
       $enteredCode = '';

      if (isset($_POST['verification_code']) && is_array($_POST['verification_code'])) {
       $enteredCode = implode('', $_POST['verification_code']);
       }

        
        // Check if code is expired
        if (isset($_SESSION['verification_expiry']) && time() > $_SESSION['verification_expiry']) {
            $verification_error = "Verification code has expired. Please request a new one.";
        } 
        // Check if code matches
        elseif (empty($enteredCode)) {
            $verification_error = "Please enter the verification code.";
        } 
        elseif (!isset($_SESSION['verification_code']) || $enteredCode !== $_SESSION['verification_code']) {
            $verification_error = "Invalid verification code. Please try again.";
        } 
        else {
            // Code is correct and not expired - create user account
            $fullName = $_SESSION['signup_data']['fullName'];
            $email = $_SESSION['signup_data']['email'];
            $hashedPassword = $_SESSION['signup_data']['hashed_password'];
            $gender = $_SESSION['signup_data']['gender'];
            $phone = $_SESSION['signup_data']['phone'];
            $birthdate = $_SESSION['signup_data']['birthdate'];
            $location = $_SESSION['signup_data']['location'];
            
            // Generate unique username
            $baseUsername = strtolower(explode(' ', $fullName)[0]);
            $userName = $baseUsername . rand(100, 999);
            
            // Ensure username is unique
            $checkUsername = $conn->prepare("SELECT UserId FROM Users WHERE UserName = ?");
            $checkUsername->bind_param('s', $userName);
            $checkUsername->execute();
            $checkUsername->store_result();
            
            while ($checkUsername->num_rows > 0 && $attemptCount < 10) {
                $userName = $baseUsername . rand(10000, 99999);
                $checkUsername->bind_param('s', $userName);
                $checkUsername->execute();
                $checkUsername->store_result();
                $attemptCount++;
            }
            $checkUsername->close();
            
            if ($attemptCount >= 10) {
                $verification_error = "Unable to generate unique username. Please try again later.";
            } else {
                // Insert user into database
                $query = "INSERT INTO Users(UserName, FullName, Email, Password, Gender, PhoneNumber, BirthDate, Location, CreditBalance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    $verification_error = "Database error: " . $conn->error;
                } else {
                    $stmt->bind_param("ssssssss", $userName, $fullName, $email, $hashedPassword, $gender, $phone, $birthdate, $location);
                    
                    if ($stmt->execute()) {
                        // Store user ID and email for signup2.php
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $stmt->close();
                        
                        // Clean up verification session data
                        unset($_SESSION['verification_code']);
                        unset($_SESSION['verification_expiry']);
                        unset($_SESSION['signup_data']['password']);
                        unset($_SESSION['signup_data']['hashed_password']);
                        
                        // Redirect to signup2.php
                        header("Location: signup2.php");
                        exit();
                    } else {
                        $verification_error = "Error creating account: " . $stmt->error;
                    }
                }
            }
        }
    }
}

// Display error if verification expired
if (isset($error)) {
    $verification_error = $error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/signuppages.css">
    <title>Email Verification - Skill Service Exchange</title>
    <style>
      

        .verification-container {
            display: flex;
            width: 80%;
            max-width: 1400px;
            min-height: 600px;
            background-color: var(--clr-bars);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        /* Left Side - Full Width Illustration */
        .verification-left {
            flex: 1;
            background: linear-gradient(135deg, var(--clr-bars) 0%, var(--clr-main) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

       

        /* Right Side - Verification Form */
        .verification-right {
            flex: 1;
            padding: 40px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .verification-right h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Messages */
        .success-message {
            color: #4CAF50;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #4CAF50;
            border-radius: 8px;
            background-color: rgba(76, 175, 80, 0.1);
            text-align: center;
        }

        .error-message {
            color: #ff6b6b;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ff6b6b;
            border-radius: 8px;
            background-color: rgba(255, 107, 107, 0.1);
            text-align: center;
        }

        /* Verification Info Box */
        .verification-info {
            background-color: var(--clr-main);
            border-left: 4px solid var(--clr-btn);
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: left;
        }

        .verification-info p {
            margin-bottom: 10px;
            font-size: 1rem;
            line-height: 1.5;
        }

        .verification-info strong {
            color: var(--clr-btn);
            font-weight: 600;
        }

        /* Timer */
        .timer {
            color: var(--clr-btn);
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            font-size: 1.1rem;
            padding: 10px;
            background-color: rgba(249, 177, 122, 0.1);
            border-radius: 8px;
        }

        /* Verification Code Input */
        .verification-code-input {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 25px 0;
        }

        .verification-code-input input {
            width: 60px;
            height: 70px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            border: 2px solid var(--clr-lighter);
            border-radius: 8px;
            background-color: var(--clr-main);
            color: var(--clr-font);
            transition: all 0.3s ease;
        }

        .verification-code-input input:focus {
            border-color: var(--clr-btn);
            outline: none;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(249, 177, 122, 0.3);
        }

        /* Buttons */
        .btn-verify {
            width: 100%;
            background-color: var(--clr-btn);
            color: #ffffff;
            border: none;
            padding: 18px 30px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-verify:hover {
            background-color: #e65c00;
            transform: translateY(-3px);
        }

        .btn-verify:active {
            transform: translateY(-1px);
        }

        .resend-form {
            margin-top: 20px;
            text-align: center;
        }

        .resend-form p {
            margin-bottom: 10px;
            color: var(--clr-font);
            font-size: 1rem;
        }

        .btn-resend {
            background-color: transparent;
            color: var(--clr-btn);
            border: 2px solid var(--clr-btn);
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 250px;
        }

        .btn-resend:hover {
            background-color: var(--clr-btn);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 177, 122, 0.3);
        }

        /* Back Link */
        .back-link {
            margin-top: 30px;
            text-align: center;
        }

        .back-link a {
            color: var(--clr-font);
            text-decoration: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--clr-btn);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .verification-container {
                flex-direction: column;
                min-height: auto;
                border-radius: 15px;
                margin: 10px 5px;
                max-height: 95vh;
                width: 100%;
                overflow-y: auto;
            }

            .verification-left {
                display: none;
            }

            .verification-right {
                padding: 30px 20px;
                flex: none;
                overflow-y: visible;
                max-height: none;
            }

            .verification-right h2 {
                font-size: 1.6rem;
                margin-bottom: 15px;
            }

            .verification-code-input {
                gap: 10px;
            }

            .verification-code-input input {
                width: 50px;
                height: 60px;
                font-size: 24px;
            }

            .btn-verify {
                padding: 16px 20px;
                font-size: 1rem;
            }

            .btn-resend {
                padding: 10px 20px;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .verification-code-input {
                gap: 8px;
            }

            .verification-code-input input {
                width: 45px;
                height: 55px;
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <!-- Left Side - Image/Illustration -->
        <div class="verification-left">
            <div class="illustration">
                <img src="../assets/images/homeinp/signupside.jpg" alt="Email Verification">
            </div>
        </div>

        <!-- Right Side - Verification Form -->
        <div class="verification-right">
            <h2>Email Verification</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($verification_error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($verification_error); ?>
                </div>
            <?php endif; ?>
            
            <div class="verification-info">
                <p>We've sent a 6-digit verification code to:</p>
                <p><strong><?php echo htmlspecialchars($_SESSION['signup_data']['email'] ?? ''); ?></strong></p>
                <p>Please check your email and enter the code below.</p>
            </div>
            
            <?php if (isset($_SESSION['verification_expiry'])): ?>
                <?php 
                $remaining = $_SESSION['verification_expiry'] - time();
                $minutes = floor($remaining / 60);
                $seconds = $remaining % 60;
                ?>
                <div class="timer" id="timer">
                    Code expires in: <span id="time"><?php echo $minutes . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT); ?></span>
                </div>
            <?php endif; ?>
            
            <form action="signup_verify.php" method="post" id="verificationForm">
                <div class="verification-code-input">
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required autofocus>
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required>
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required>
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required>
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required>
                    <input type="text" name="verification_code[]" maxlength="1" pattern="\d" required>
                </div>
                
                <input type="hidden" name="verification_code_full" id="verification_code_full">
                
                <button type="submit" name="verify" class="btn-verify">Verify & Continue</button>
            </form>
            
            <form action="signup_verify.php" method="post" class="resend-form">
                <p>Didn't receive the code?</p>
                <button type="submit" name="resend" class="btn-resend">Resend Verification Code</button>
            </form>
            
            <div class="back-link">
                <a href="signup1.php">
                    <span>←</span> Back to Sign Up
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus and auto-tab between input fields
        const inputs = document.querySelectorAll('.verification-code-input input');
        
        inputs.forEach((input, index) => {
            // Auto-tab to next input
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenInput();
            });
            
            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Prevent non-numeric input
            input.addEventListener('keypress', function(e) {
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            
            // Paste handling
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    pasteData.split('').forEach((char, idx) => {
                        if (inputs[idx]) {
                            inputs[idx].value = char;
                        }
                    });
                    updateHiddenInput();
                    // Focus the last input
                    if (inputs[5]) {
                        inputs[5].focus();
                    }
                }
            });
        });
        
        // Combine individual inputs into one hidden input
        function updateHiddenInput() {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            document.getElementById('verification_code_full').value = code;
        }
        
        // Timer countdown
        <?php if (isset($_SESSION['verification_expiry'])): ?>
        function updateTimer() {
            const timerElement = document.getElementById('time');
            if (!timerElement) return;
            
            const timeParts = timerElement.textContent.split(':');
            let minutes = parseInt(timeParts[0]);
            let seconds = parseInt(timeParts[1]);
            
            if (seconds === 0) {
                if (minutes === 0) {
                    // Time's up - refresh page to show expired message
                    location.reload();
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }
            
            timerElement.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        }
        
        // Update timer every second
        setInterval(updateTimer, 1000);
        <?php endif; ?>
        
        // Form submission - ensure full code is captured
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            updateHiddenInput();
            const fullCode = document.getElementById('verification_code_full').value;
            if (fullCode.length !== 6) {
                e.preventDefault();
                // Highlight empty inputs
                inputs.forEach((input, index) => {
                    if (!input.value) {
                        input.style.borderColor = '#ff6b6b';
                        setTimeout(() => {
                            input.style.borderColor = '';
                        }, 2000);
                    }
                });
                
                // Show error message
                const errorDiv = document.querySelector('.error-message') || document.createElement('div');
                if (!errorDiv.classList.contains('error-message')) {
                    errorDiv.className = 'error-message';
                    errorDiv.textContent = 'Please enter all 6 digits of the verification code.';
                    document.querySelector('.verification-right h2').after(errorDiv);
                }
            }
        });
        
        // Auto-focus first input on page load
        if (inputs[0]) {
            inputs[0].focus();
        }
    </script>
</body>
</html>