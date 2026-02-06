<?php
/**
 * Events API - Fetch events from database with pagination
 * Endpoint: getEventsAPI.php?page=1&limit=5
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

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 50) $limit = 5;

// Calculate offset
$offset = ($page - 1) * $limit;

try {
    // First, check what status values exist in database
    $statusCheckQuery = "SELECT DISTINCT EventStatus FROM events LIMIT 10";
    $statusCheckResult = $conn->query($statusCheckQuery);
    $statusValues = [];
    if ($statusCheckResult) {
        while ($row = $statusCheckResult->fetch_assoc()) {
            $statusValues[] = $row['EventStatus'];
        }
    }
    
    // Get total count of all events (not filtering by status yet, to debug)
    $countQuery = "SELECT COUNT(*) as total FROM events";
    $countResult = $conn->query($countQuery);
    
    if (!$countResult) {
        throw new Exception("Count query failed: " . $conn->error);
    }
    
    $countRow = $countResult->fetch_assoc();
    $totalEvents = $countRow['total'];
    
    // Fetch events - get ALL events for now to debug
    $eventsQuery = "
        SELECT 
            e.EventId,
            e.EventTitle as title,
            e.EventDescription as description,
            e.EventLocation as location,
            e.EventType as type,
            e.EventStartDate as date,
            e.EventEndDate as endDate,
            e.MaxAttendees as maxAttendees,
            e.CurrentAttendeesNumber as attendees,
            e.EventCost as cost,
            e.EventStatus as status,
            e.CreatedAt as createdDate,
            u.UserName as organizer,
            u.FullName as organizerFullName,
            u.UserId as organizerId
        FROM events e
        LEFT JOIN Users u ON e.OrganizerId = u.UserId
        ORDER BY e.EventStartDate ASC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($eventsQuery);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param('ii', $limit, $offset)) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $events = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get skills for this event from EventSkills table
        $skillsQuery = "
            SELECT s.SkillName 
            FROM EventSkills es
            JOIN Skills s ON es.SkillId = s.SkillId
            WHERE es.EventId = ?
        ";
        
        $skillsStmt = $conn->prepare($skillsQuery);
        $eventId = $row['EventId'];
        $skillsStmt->bind_param('i', $eventId);
        $skillsStmt->execute();
        $skillsResult = $skillsStmt->get_result();
        
        $skills = [];
        while ($skillRow = $skillsResult->fetch_assoc()) {
            $skills[] = $skillRow['SkillName'];
        }
        $skillsStmt->close();
        
        $row['skills'] = !empty($skills) ? $skills : ['Event Skill'];
        
        // Format the cost
        if ($row['cost'] == 0) {
            $row['cost'] = 'Free';
        } else {
            $row['cost'] = $row['cost'] . ' Credits';
        }
        
        // Calculate duration in hours BEFORE formatting dates
        $startTime = strtotime($row['date']);
        $endTime = strtotime($row['endDate']);
        $durationHours = ceil(($endTime - $startTime) / 3600);
        $row['duration'] = $durationHours . ' hours';
        
        // Format dates
        $row['date'] = date('F d, Y', strtotime($row['date']));
        $row['time'] = date('h:i A', strtotime($row['date'])) . ' - ' . date('h:i A', $endTime);
        $row['createdDate'] = date('l, F d, Y \a\t h:i A', strtotime($row['createdDate']));
        
        // Use UserName or FullName for organizer display
        if (empty($row['organizer'])) {
            $row['organizer'] = $row['organizerFullName'] ?? 'Community Admin';
        }
        
        $events[] = $row;
    }
    
    $stmt->close();
    
    // Calculate total pages
    $totalPages = ceil($totalEvents / $limit);
    
    // Return JSON response
    $response = [
        'success' => true,
        'data' => $events,
        'pagination' => [
            'currentPage' => $page,
            'limit' => $limit,
            'totalEvents' => $totalEvents,
            'totalPages' => $totalPages,
            'hasMore' => $page < $totalPages
        ]
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
