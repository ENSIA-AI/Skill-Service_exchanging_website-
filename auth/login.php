<?php
require_once '../DataBaseManagement/config.php';
$errors = []; // Initialize errors array
session_start();

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email_uid = htmlspecialchars(trim($_POST['email-uid']), ENT_QUOTES, 'UTF-8');
  $password = $_POST['password'];
  $remember = isset($_POST['remember']);
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
    $stmt = $conn->prepare('SELECT UserId, UserName, Email, Password FROM Users WHERE Email=? OR UserName=?');
    $stmt->bind_param('ss', $email_uid, $email_uid);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $stmt->bind_result($db_userid, $db_username, $db_email, $db_password);
      $stmt->fetch();
      if (password_verify($password, $db_password)) {
        $_SESSION['user_id'] = $db_userid;
        $_SESSION['username'] = $db_username;
        $_SESSION['email'] = $db_email;
        session_regenerate_id(true);
        if ($remember) {
          setcookie('email', $db_email, time() + (86400 * 30), "/");
          setcookie('username', $db_username, time() + (86400 * 30), "/");
        }
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
    $stmt->close();
  }
  if ($is_ajax) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
  }
  $conn->close();
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
        var remember = $("#remember").is(':checked');

        $.ajax({
          type: 'POST',
          url: 'login.php',
          data: {
            'email-uid': emailuid,
            'password': password,
            'remember': remember
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
          error: function () {
            alert('An error occurred. Please try again.');
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
          <div class="form-check">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          
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