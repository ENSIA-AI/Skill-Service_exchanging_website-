<?php

class PostsController extends Controller {
    
    public function index() {
        $postModel = $this->model('Post');
        $posts = $postModel->getAllPosts();
        
        $this->view('posts/index', ['posts' => $posts]);
    }

    public function details($id = null) {
        if (!$id) {
             header('Location: /Skill-Service_exchanging_website-/public/posts');
             exit;
        }
        $postModel = $this->model('Post');
        $post = $postModel->getPostById($id);
        
        $this->view('posts/details', ['post' => $post]);
    }
    
    public function create() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Skill-Service_exchanging_website-/public/auth/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $data = [
                'user_id' => $_SESSION['user_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'post_type' => $_POST['post_type'],
                'category_id' => $_POST['category'],
                'duration' => $_POST['duration'],
                'location' => $_POST['location'],
                'available_date' => $_POST['available_date'],
                'credits' => $_POST['credits']
            ];

            $postModel = $this->model('Post');
            if ($postModel->createPost($data)) {
                 header('Location: /Skill-Service_exchanging_website-/public/posts');
                 exit;
            } else {
                 echo "Failed to create post.";
            }

        } else {
             $this->view('posts/create');
        }
    }
}
