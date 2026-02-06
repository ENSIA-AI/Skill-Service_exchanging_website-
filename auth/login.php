<?php
// Error handling and logging setup
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Detect if production (InfinityFree)
$isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
if (!$isProduction) {
    ini_set('display_errors', '1');
}

require_once '../DataBaseManagement/config.php';

// Check if database connection is established
if (!isset($conn) || !$conn) {
    error_log("LOGIN ERROR: Database connection not established in login.php");
    if ($is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['server' => 'Server error. Please try again later.']]);
        exit();
    } else {
        http_response_code(500);
        die('<h1>500 Internal Server Error</h1><p>Unable to connect to database.</p>');
    }
}

$errors = []; // Initialize errors array
session_start();

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $email_uid = htmlspecialchars(trim($_POST['email-uid'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    
    if (empty($email_uid)) {
      $errors['email_uid'] = 'Username or email is required!';
    } else {
      if (!filter_var($email_uid, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]{3,45}$/', $email_uid)) {
        $errors['email_uid'] = 'Invalid Email or Username!';
      }
    }
    if (empty($password)) {
      $errors['password'] = 'Password is required!';
    } elseif (strlen($password) < 8) {
      $errors['password'] = 'Password must be at least 8 characters!';
    } elseif (!preg_match('/[A-Za-z]/', $password)) {
      $errors['password'] = 'Password must contain at least one letter!';
    } elseif (!preg_match('/[0-9]/', $password)) {
      $errors['password'] = 'Password must contain at least one number!';
    }
    
    if (empty($errors)) {
      $stmt = $conn->prepare('SELECT UserId, UserName, Email, Password FROM users WHERE Email=? OR UserName=?');
      
      if (!$stmt) {
        error_log("LOGIN ERROR: Prepare statement failed - " . $conn->error);
        $errors['server'] = 'Database error. Please try again later.';
      } else {
        $stmt->bind_param('ss', $email_uid, $email_uid);
        
        if (!$stmt->execute()) {
          error_log("LOGIN ERROR: Execute statement failed - " . $stmt->error);
          $errors['server'] = 'Database error. Please try again later.';
        } else {
          $stmt->store_result();
          if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_userid, $db_username, $db_email, $db_password);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
              $_SESSION['user_id'] = $db_userid;
              $_SESSION['username'] = $db_username;
              $_SESSION['email'] = $db_email;
              session_regenerate_id(true);
              if ($is_ajax) {
                echo json_encode(['success' => true, 'redirect' => '../dashboard/post/posts.php']);
                exit();
              } else {
                header('Location: ../dashboard/post/posts.php');
                exit();
              }
            } else {
              $errors['password'] = 'Incorrect password!';
            }
          } else {
            $errors['email_uid'] = 'Username Or Email not found!';
          }
        }
        $stmt->close();
      }
    }
  } catch (Exception $e) {
    error_log("LOGIN ERROR: Exception - " . $e->getMessage());
    error_log("LOGIN STACK TRACE: " . $e->getTraceAsString());
    
    // Log the actual exception for debugging
    $errorMsg = $e->getMessage();
    if (strpos($errorMsg, 'password_verify') !== false) {
      $errors['server'] = 'Password verification error. Please try again later.';
    } else {
      $errors['server'] = 'An unexpected error occurred: ' . substr($errorMsg, 0, 100);
    }
  }
  
  if ($is_ajax) {
    // Return 200 for normal form validation errors, 400 for server errors only
    http_response_code(isset($errors['server']) ? 400 : 200);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
  }
  
  if (isset($conn)) {
    $conn->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/images/favicon.png">
  <link rel="stylesheet" href="../assets/css/login.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <title>Login - SkillSwap</title>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    $(document).ready(function () {
      $(".login-form").on('submit', function (event) {
        event.preventDefault();
        var emailuid = $("#email-uid").val();
        var password = $("#password").val();

        $.ajax({
          type: 'POST',
          url: 'login.php',
          data: {
            'email-uid': emailuid,
            'password': password
          },
          dataType: 'json',  // Ensures response is parsed as JSON
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function (response) {
            $('.error-message').remove();
            $('.form-group').removeClass('error');
            if (response.success) {
              window.location.href = response.redirect;
            } else {
              if (response.errors && response.errors.email_uid) {
                $('.email').addClass('error').append('<small class="error-message">' + response.errors.email_uid + '</small>');
              }
              if (response.errors && response.errors.password) {
                $('.password').addClass('error').append('<small class="error-message">' + response.errors.password + '</small>');
              }
            }
          },
          error: function (xhr, status, error) {
            console.log('AJAX Error:', error);
            console.log('Status:', xhr.status);
            console.log('Response:', xhr.responseText);
            alert('An error occurred. Please try again later.');
          }
        });
      });
    });
  </script>
</head>

<body>
  <div class="login-container">
    <!-- Left Side -->
    <div class="login-left">
      <div class="illustration">
        <img src="../assets/images/7943139_3779554.svg" alt="Welcome Back">
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
      <form class="login-form">
        <div class="form-group email">
          <label for="email-uid">Email or Username</label>
          <input type="text" id="email-uid" name="email-uid" placeholder="Enter your email or username" required>
          <small class="error-message"></small>
        </div>
        <div class="form-group password">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <small class="error-message"></small>
        </div>
        <div class="form-options">
          
        </div>
        <button name="submit" type="submit" class="btn-login">Login</button>
      </form>
      <p class="signup-link">
        Don't have an account? <a href="signup1.php">Sign Up</a>
      </p>
    </div>
  </div>
</body>

</html>