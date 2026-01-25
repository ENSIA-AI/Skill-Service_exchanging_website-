<?php

class EventsController extends Controller {
    
    public function index() {
        $eventModel = $this->model('Event');
        $events = $eventModel->getAllEvents();
        
        $this->view('events/index', ['events' => $events]);
    }

    public function details($id = null) {
        if (!$id) {
             header('Location: /Skill-Service_exchanging_website-/public/events');
             exit;
        }
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($id);
        
        $this->view('events/details', ['event' => $event]);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Skill-Service_exchanging_website-/public/auth/login');
            exit;
        }

        $eventModel = $this->model('Event');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             // Basic validation
             $data = [
                 'title' => $_POST['eventTitle'],
                 'start_date' => $_POST['eventStartDate'],
                 'end_date' => $_POST['eventEndDate'],
                 'type' => $_POST['eventType'],
                 'location' => $_POST['location'],
                 'description' => $_POST['description'],
                 'max_attendees' => $_POST['maxAttendees'],
                 'organizer_id' => $_SESSION['user_id'],
                 'skills' => $_POST['selectedSkills'] ?? [] 
                 // Note: selectedSkills comes from JS as a comma string or hidden inputs? 
                 // Legacy HTML had: <input type="hidden" name="selectedSkills" id="selectedSkillsInput">
                 // So it's a string like "1,2,3"
             ];
             
             if ($eventModel->createEvent($data)) {
                 header('Location: /Skill-Service_exchanging_website-/public/events');
                 exit;
             } else {
                 echo "Failed to create event"; // TODO: better error view
             }
        } else {
             $categories = $eventModel->getCategories();
             $this->view('events/create', ['categories' => $categories]);
        }
    }

    public function getSkills() {
        $categoryId = $_GET['category_id'] ?? null;
        if ($categoryId) {
            $eventModel = $this->model('Event');
            $skills = $eventModel->getSkillsByCategory($categoryId);
            header('Content-Type: application/json');
            echo json_encode($skills);
            exit;
        }
        echo json_encode([]);
        exit;
    }
}
