<?php
require_once __DIR__ . '/php/notify.php';
require_once __DIR__ . '/DataBaseManagement/config.php';

// Seed notifications for User 1
$userId = 1;

$notifications = [
    [
        'type' => 'booking',
        'title' => 'New Skill Request',
        'message' => 'Sarah has requested to learn PHP from you.',
        'section' => 'Exchange',
        'senderId' => 2 // Sarah
    ],
    [
        'type' => 'like',
        'title' => 'Post Liked',
        'message' => 'Ahmed liked your Web Development post.',
        'section' => 'Reviews',
        'senderId' => 3 // Ahmed
    ],
    [
        'type' => 'acceptedInEvent',
        'title' => 'Event Confirmed',
        'message' => 'You have been accepted to the Tech Workshop.',
        'section' => 'events',
        'senderId' => 1 // Organizer
    ],
    [
        'type' => 'earned',
        'title' => 'Credits Received',
        'message' => 'You earned 50 credits for your last session.',
        'section' => 'credits',
        'senderId' => null // System
    ]
];

echo "Seeding notifications for User ID $userId...\n";

foreach ($notifications as $n) {
    $result = create_notification($conn, $userId, $n['type'], $n['title'], $n['message'], $n['section'], $n['senderId']);
    if ($result) {
        echo "Created notification ID: $result\n";
    } else {
        echo "Failed to create notification: " . $n['title'] . "\n";
    }
}

echo "Done.";
?>
