<?php

session_start();
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

//form sumbission 
if ($_SERVER['REQUEST_METHOD']==='POST')
{
   //we first sanitize and validate inputs 

  $fullName =trim($_POST['fullName'] ?? '');
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
      'gender' => $gender,
      'phone' => $phone,
      'birthdate' => $birthdate,
      'location' => $location,
      'terms' => $terms
  ];

  //an associative array to hold error message for each field
   $errors = array('fullName' => '','email'=> '','password'=> '','confirmPassword' =>'','gender' => '','phone' => '','birthdate' => '','location' => '','terms' => '');

   //Validation functions 
    function isValidName($name)
    { return preg_match('/^[a-zA-Z\s]{2,30}$/', $name); }

    function isValidEmail($email)
    {return filter_var($email, FILTER_VALIDATE_EMAIL); }

    function isValidPassword($password)
   { return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/',  $password);}

    function isValidAlgerianPhone($phone)
   { return preg_match('/^(?:\+213\s?)?(?:0)?(5|6|7)[0-9]{8}$/', $phone); }


   function isValidBirthdate($birthdate)
   {
       $bd = new DateTime($birthdate);
       $today = new DateTime();//the default is current date & time
            
       if ($bd > $today)
       {
         return ['valid' => false, 'error' => 'Birth date cannot be in the future'];
        }
            
        if ($bd->format('Y') < 1900)
        {
         return ['valid' => false, 'error' => 'Year must be 1900 or later'];
        }
            
       return ['valid' => true];
   }



   //Validating all the fields
   //Fullname 
   if (!$fullName)
   {
      $errors['fullName']='Full name is required';
   }
   elseif(!isValidName($fullName))
   {
     $errors['fullName']='Please enter a valid fullname';
   }
   else
   {
      $errors['fullName'] = '';
   }

   //Email
     if (!$email)
   {
      $errors['email']='Email is required';
   }
   elseif(!isValidEmail($email))
   {
     $errors['email']='Please enter a valid email address';
   }
   else //checking if the given email already exists in the database or not 
   {
    $checkEmail=$conn->prepare( "SELECT UserId FROM Users WHERE Email = ?" );
    $checkEmail->bind_param('s',$email);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows >0)
    {
       $errors['email']='Email already registered';
    }
    else
    {
      $errors['email'] = '';
    }

    $checkEmail->close();

   }

   //password 
    if (!$password)
    {
      $errors['password'] = 'Password is required';

    } elseif (!isValidPassword($password)) 
    {
        $errors['password'] = 'Password must be at least 6 characters with letters and numbers';
    }
    else
    {
      $errors['password'] = '';
    }


   //confirmpassword 
    if (!$confirmPassword)
   {
      $errors['confirmPassword'] = 'Please confirm your password';
    
    } elseif ($password !== $confirmPassword)
    {
      $errors['confirmPassword'] = 'Passwords do not match';

    }
    else
    {
      $errors['confirmPassword'] = '';
    }

    //gender
    if (!$gender || !in_array($gender, ['M', 'F']))
   {
      $errors['gender'] = 'Please select a valid gender';

    }
    else
    {
      $errors['gender'] = '';
    }

    //phone 
    if (!$phone)
    {
      $errors['phone'] = 'Phone number is required';

    } elseif (!isValidAlgerianPhone($phone))
    {
      $errors['phone'] = 'Please enter a valid Algerian phone number (05/06/07 followed by 8 digits)';

    }
    else
    {
      $errors['phone'] = '';
    }

    //birthdate
    if (!$birthdate) 
    {
        $errors['birthdate'] = 'Birth date is required';

    } else {
      $bdValidation = isValidBirthdate($birthdate);
       if (!$bdValidation['valid'])
       {
          $errors['birthdate'] = $bdValidation['error'];
        }
        else
        {
          $errors['birthdate'] = '';
        }
    }
    //location
    if (!$location)
    {
        $errors['location'] = 'Location is required';
    } elseif (strlen($location) < 2 || strlen($location) > 100)
    {
       $errors['location'] = 'Location must be between 2 and 100 characters';
    }
    else
    {
      $errors['location'] = '';
    }

    //terms 
    if (!$terms)
    {
      $errors['terms'] = 'You must agree to terms and conditions';
    }
    else
    {
      $errors['terms'] = '';
    }


    //now if we passed all the validations then are input values are correct,we can insert them in the database 
    // Check if any error exists
    $hasErrors = false;
    foreach ($errors as $error) {
        if (!empty($error)) {
            $hasErrors = true;
            break;
        }
    }

    if(!$hasErrors)
    {
     $hashedPassword =password_hash($password,PASSWORD_BCRYPT);

     //this is to generate a random username 
     $userName = strtolower(explode(' ', $fullName)[0]) . rand(100, 999);

     $query ="INSERT INTO Users(UserName,FullName,Email,Password,Gender,PhoneNumber,BirthDate,Location,CreditBalance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0) ";
     $stmt=$conn->prepare($query);
     
      
     //this is a basic error handling for database operations
     if (!$stmt) {  die("Prepare failed: " . $conn->error); }

     $stmt->bind_param("ssssssss",$userName, $fullName, $email, $hashedPassword, $gender, $phone, $birthdate, $location);
     
       
      if ($stmt->execute())
      {
         // Keep signup data in session for user to go back if needed
         // Store user ID and email for signup2
         $_SESSION['user_email'] = $email;
         $_SESSION['user_id'] = $stmt->insert_id;
         $stmt->close();
                
         header("Location: signup2.php");
         exit();
        } else 
        { die("Error: " . $conn->error); }


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
    <title>Sign Up - Swap</title>
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
                    <input type="email" id="email" name="email" placeholder="john@example.com" required value="<?php echo htmlspecialchars($_SESSION['signup_data']['email'] ?? ''); ?>" >
                    <small class="error-message"></small>
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

                <button type="submit" class="btn-next">Next</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.html">Login</a>
            </p>
        </div>
    </div>

   <script src="../assets/js/formvalidation.js"></script>
   
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