<?php

require_once __DIR__ . '/../core/database.php';

$db = new Database();
$conn = $db->conn;

// Mapping: signup2.php Key -> Database Category Name (approximate ID based on `Category` table dump)
// "technology" -> "Technology & Programming" (1)
// "creative" -> "Design & Creative" (2)
// "languages" -> "Languages" (8)
// "business" -> "Business & Finance" (11)
// "home" -> "Home Improvement" (13)
// "fitness" -> "Health & Fitness" (7)

$categoryMapping = [
    'technology' => 1,
    'creative' => 2,
    'languages' => 8,
    'business' => 11,
    'home' => 13,
    'fitness' => 7
];

$skillsData = [
    'technology' => [
        "Web Development", "Mobile App Development", "Data Science", "Machine Learning", 
        "Artificial Intelligence", "Cybersecurity", "Cloud Computing", "Database Management", 
        "UI/UX Design", "Software Engineering", "Python Programming", "JavaScript Development", 
        "Java Programming", "C++ Development", "Game Development", "DevOps", "Blockchain", 
        "IoT Development", "API Development", "Quality Assurance"
    ],
    'creative' => [
        "Graphic Design", "Digital Illustration", "Photography", "Video Editing", "3D Modeling", 
        "Animation", "Motion Graphics", "Brand Design", "Typography", "Print Design", "Web Design", 
        "UI Design", "UX Research", "Product Design", "Fashion Design", "Interior Design", 
        "Industrial Design", "Packaging Design", "Art Direction", "Creative Direction"
    ],
    'languages' => [
        "English Conversation", "Business English", "Spanish", "French", "German", 
        "Chinese Mandarin", "Japanese", "Arabic", "Russian", "Italian", "Portuguese", 
        "Public Speaking", "Presentation Skills", "Negotiation", "Interview Preparation", 
        "Accent Reduction", "TOEFL/IELTS Preparation", "Translation", "Proofreading", "Creative Writing"
    ],
    'business' => [
        "Project Management", "Leadership", "Strategic Planning", "Business Development", 
        "Marketing Strategy", "Sales Techniques", "Financial Analysis", "Entrepreneurship", 
        "Time Management", "Team Building", "Conflict Resolution", "Business Writing", 
        "Data Analysis", "Digital Marketing", "Social Media Management", "Customer Service", 
        "Human Resources", "Risk Management", "Supply Chain Management", "Quality Assurance"
    ],
    'home' => [
        "Basic Plumbing", "Electrical Repairs", "Carpentry", "Painting & Decorating", "Gardening", 
        "Landscaping", "Home Organization", "Furniture Assembly", "Appliance Repair", 
        "Home Maintenance", "DIY Projects", "Interior Design", "Cleaning Techniques", 
        "Pest Control", "Home Security", "Energy Efficiency", "Renovation Planning", 
        "Tool Usage & Safety", "Wallpaper Installation", "Tile Setting"
    ],
    'fitness' => [
        "Personal Training", "Yoga Instruction", "Pilates", "Meditation", "Nutrition Coaching", 
        "Weight Training", "Cardio Training", "Martial Arts", "Dance Fitness", "Sports Coaching", 
        "Strength & Conditioning", "Flexibility Training", "Posture Correction", "Injury Prevention", 
        "Rehabilitation Exercises", "Group Fitness", "Boxing", "Swimming", "Cycling", 
        "Running Technique"
    ]
];

echo "Seeding Skills...\n";

foreach ($skillsData as $key => $skills) {
    if (!isset($categoryMapping[$key])) {
        echo "Warning: No category mapping for '$key'\n";
        continue;
    }

    $categoryId = $categoryMapping[$key];
    
    foreach ($skills as $skillName) {
        // Check if skill exists globally (SkillName is UNIQUE)
        $checkStmt = $conn->prepare("SELECT SkillId FROM Skills WHERE SkillName = ?");
        $checkStmt->bind_param("s", $skillName);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows == 0) {
            // Insert
            $insertStmt = $conn->prepare("INSERT INTO Skills (SkillName, CategoryId) VALUES (?, ?)");
            $insertStmt->bind_param("si", $skillName, $categoryId);
            if ($insertStmt->execute()) {
                echo "Added: $skillName (Cat ID: $categoryId)\n";
            } else {
                echo "Error adding $skillName: " . $conn->error . "\n";
            }
        } else {
            echo "Skipped: $skillName (Already exists)\n";
        }
    }
}

echo "Done.\n";
