<?php
require_once __DIR__ . '/DataBaseManagement/config.php';

// Seed events for Organizer ID 1
$organizerId = 1;

$events = [
    [
        'title' => 'Web Development Workshop',
        'description' => 'Learn the basics of HTML, CSS, and JS in this hands-on workshop.',
        'location' => 'Online',
        'type' => 'online',
        'start' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'end' => date('Y-m-d H:i:s', strtotime('+1 day + 3 hours')),
        'max' => 50
    ],
    [
        'title' => 'Graphic Design Masterclass',
        'description' => 'Advanced tips for using Figma and Adobe Illustrator.',
        'location' => 'Tech Hub, Algiers',
        'type' => 'in-person',
        'start' => date('Y-m-d H:i:s', strtotime('+2 days')),
        'end' => date('Y-m-d H:i:s', strtotime('+2 days + 4 hours')),
        'max' => 20
    ]
];

echo "Seeding events for Organizer ID $organizerId...\n";

foreach ($events as $e) {
    $stmt = $conn->prepare("INSERT INTO Events (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $organizerId, $e['title'], $e['description'], $e['location'], $e['type'], $e['start'], $e['end'], $e['max']);
    
    if ($stmt->execute()) {
        echo "Created event: " . $e['title'] . " (ID: " . $stmt->insert_id . ")\n";
    } else {
        echo "Failed to create event: " . $e['title'] . " - " . $conn->error . "\n";
    }
    $stmt->close();
}

echo "Done.";
?>
