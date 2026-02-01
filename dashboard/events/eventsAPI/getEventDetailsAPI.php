<?php
/**
 * Event Details API - Fetch a single event by ID from database
 * Endpoint: getEventDetailsAPI.php?id=1
 */

// Set JSON header FIRST, before any output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting to catch issues
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors as HTML
ini_set('log_errors', '1');
$errorLogPath = realpath(__DIR__ . '/../../') . '/error_log.txt';
ini_set('error_log', $errorLogPath);

// Include database configuration
$configPath = __DIR__ . '/../../../DataBaseManagement/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Config file not found: ' . $configPath
    ]);
    exit;
}

require_once $configPath;

// Check if connection exists
if (!isset($conn) || is_null($conn)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection not established. $conn is not set.'
    ]);
    exit;
}

// Check connection error
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error: ' . $conn->connect_error
    ]);
    exit;
}

// Get event ID parameter
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid event ID'
    ]);
    exit;
}

try {
    // Fetch single event details
    $eventQuery = "
        SELECT 
            e.EventId,
            e.EventTitle as title,
            e.EventDescription as description,
            e.EventLocation as location,
            e.EventType as type,
            e.EventStartDate as startDate,
            e.EventEndDate as endDate,
            e.MaxAttendees as maxAttendees,
            e.CurrentAttendeesNumber as attendees,
            e.EventCost as cost,
            e.EventStatus as status,
            e.CreatedAt as createdDate,
            u.UserName as organizer,
            u.FullName as organizerFullName,
            u.UserId as organizerId
        FROM Events e
        LEFT JOIN Users u ON e.OrganizerId = u.UserId
        WHERE e.EventId = ?
    ";
    
    $stmt = $conn->prepare($eventQuery);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param('i', $eventId)) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Event not found'
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $event = $result->fetch_assoc();
    $stmt->close();
    
    // Get skills for this event from EventSkills table
    $skillsQuery = "
        SELECT s.SkillName 
        FROM EventSkills es
        JOIN Skills s ON es.SkillId = s.SkillId
        WHERE es.EventId = ?
    ";
    
    $skillsStmt = $conn->prepare($skillsQuery);
    $skillsStmt->bind_param('i', $eventId);
    $skillsStmt->execute();
    $skillsResult = $skillsStmt->get_result();
    
    $skills = [];
    while ($skillRow = $skillsResult->fetch_assoc()) {
        $skills[] = $skillRow['SkillName'];
    }
    $skillsStmt->close();
    
    $event['skills'] = !empty($skills) ? $skills : [];
    
    // Format the cost
    if ($event['cost'] == 0 || $event['cost'] === null) {
        $event['cost'] = 'Free';
    } else {
        $event['cost'] = $event['cost'] . ' Credits';
    }
    
    // Calculate duration in hours from start and end dates
    $startTime = strtotime($event['startDate']);
    $endTime = strtotime($event['endDate']);
    $durationSeconds = $endTime - $startTime;
    
    // Format duration nicely
    $durationHours = floor($durationSeconds / 3600);
    $durationMinutes = floor(($durationSeconds % 3600) / 60);
    
    if ($durationHours > 0 && $durationMinutes > 0) {
        $event['duration'] = $durationHours . ' hour' . ($durationHours != 1 ? 's' : '') . ' ' . $durationMinutes . ' minute' . ($durationMinutes != 1 ? 's' : '');
    } elseif ($durationHours > 0) {
        $event['duration'] = $durationHours . ' hour' . ($durationHours != 1 ? 's' : '');
    } else {
        $event['duration'] = $durationMinutes . ' minute' . ($durationMinutes != 1 ? 's' : '');
    }
    
    // Format date display
    $event['date'] = date('F d, Y', $startTime);
    
    // Format time display (start time - end time)
    $event['time'] = date('h:i A', $startTime) . ' - ' . date('h:i A', $endTime);
    
    // Format created date
    $event['createdDate'] = date('l, F d, Y \a\t h:i A', strtotime($event['createdDate']));
    
    // Use UserName or FullName for organizer display
    if (empty($event['organizer'])) {
        $event['organizer'] = $event['organizerFullName'] ?? 'Community Admin';
    }
    
    // Return JSON response
    $response = [
        'success' => true,
        'data' => $event
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => 'Exception caught: ' . $e->getFile() . ' line ' . $e->getLine()
    ]);
}

// Only close if connection exists
if (isset($conn) && !is_null($conn)) {
    $conn->close();
}
?>
