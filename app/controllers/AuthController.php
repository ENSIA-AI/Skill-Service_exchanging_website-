<?php

class AuthController extends Controller {
    
    public function index() {
        $this->login();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User');
            // Handle login logic
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $userModel->login($email, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['UserId']; // Note: Schema uses UserId (Cap), not id
                $_SESSION['user_name'] = $user['FullName'] ?? $user['UserName'] ?? 'User';
                
                // Redirect to dashboard
                // In a real app you might have a dedicated DashboardController
                header('Location: /Skill-Service_exchanging_website-/public/events'); 
                exit;
            } else {
                 // Login Failed
                 $this->view('auth/login', ['error' => 'Invalid email or password']);
            }
        }
        $this->view('auth/login');
    }

    public function signup() {
        // Step 1 View
        $this->view('auth/signup1');
    }
    
    public function signup2() {
        // Handle submission from Step 1
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Store Step 1 data in session
            $_SESSION['signup_data'] = array_merge($_SESSION['signup_data'] ?? [], $_POST);
        }
        
        $this->view('auth/signup2');
    }

    public function signup3() {
        // Handle submission from Step 2
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Store Step 2 data in session
            $_SESSION['signup_data'] = array_merge($_SESSION['signup_data'] ?? [], $_POST);
        }
        
        $this->view('auth/signup3');
    }

    public function register() {
        // Handle submission from Step 3 (Final Registration)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             // Store Step 3 data
             $_SESSION['signup_data'] = array_merge($_SESSION['signup_data'] ?? [], $_POST);
             
             // Now we have all data in $_SESSION['signup_data']
             $data = $_SESSION['signup_data'];
             
             // Validate if we have the full data (Step 1 is critical)
             if (empty($data['email']) || empty($data['fullName'])) {
                 // Session expired or flow broken
                 // Redirect to start
                 echo "<script>alert('Session expired. Please sign up again.'); window.location.href='/Skill-Service_exchanging_website-/public/auth/signup';</script>";
                 exit;
             }
             
             $userModel = $this->model('User');
             if ($userModel->register($data)) {
                 // Clear session signup data
                 unset($_SESSION['signup_data']);
                 
                 // Redirect to Login
                 header('Location: /Skill-Service_exchanging_website-/public/auth/login');
                 exit;
             } else {
                 echo "Registration Failed! (Email might be taken)";
                 // View with error?
             }
        }
    }
    
    public function forgot_password() {
        $this->view('auth/forgot_password');
    }
}
