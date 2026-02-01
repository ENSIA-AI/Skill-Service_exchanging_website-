<?php

session_start();
include_once '../DataBaseManagement/config.php';

// Check if user is coming from signup1
if (!isset($_SESSION['user_id'])) {
    header("Location: signup1.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch categories from database
$categories = [];
$categoryResult = $conn->query("SELECT CategoryId, CategoryName FROM Category ORDER BY CategoryName");
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teachSkills = isset($_POST['teachSkills']) ? (array) $_POST['teachSkills'] : [];
    $learnSkills = isset($_POST['learnSkills']) ? (array) $_POST['learnSkills'] : [];
    
    // Validate that at least one skill is selected for each
    if (empty($teachSkills) || empty($learnSkills)) {
        $error = "Please select at least 1 skill you can teach and 1 skill you want to learn.";
    } else {
        // Get proficiency level from form
        $proficiency = isset($_POST['proficiencyLevel']) ? $_POST['proficiencyLevel'] : 'beginner';
        
        // Save teach skills
        foreach ($teachSkills as $skillId) {
            $stmt = $conn->prepare("INSERT INTO UserSkills (UserId, SkillId, SkillType, ProficiencyLevel) VALUES (?, ?, 'teach', ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $skillId = intval($skillId);
            $stmt->bind_param("iis", $userId, $skillId, $proficiency);
            if (!$stmt->execute()) {
                die("Error saving skill: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Save learn skills
        foreach ($learnSkills as $skillId) {
            $stmt = $conn->prepare("INSERT INTO UserSkills (UserId, SkillId, SkillType, ProficiencyLevel) VALUES (?, ?, 'learn', ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $skillId = intval($skillId);
            $stmt->bind_param("iis", $userId, $skillId, $proficiency);
            if (!$stmt->execute()) {
                die("Error saving skill: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Clear signup data and redirect to posts
        unset($_SESSION['signup_data']);
        header("Location: ../dashboard/post/posts.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/signuppages.css">
    <title>Sign Up - Skills & Interests</title>
</head>
<body>
    <div class="signup-container ">
        <!-- Left Side - Image/Illustration -->
        <div class="signup-left">
            <div class="illustration">
                <img src="../assets/images/homeinp/signupside.jpg" alt="Skills Illustration">
            </div>
        </div>

        <!-- Right Side - Skills Form -->
        <div class="signup-right">
            <h2>Your Skills & Interests</h2>
            
            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="signup2.php" method="post" id="skillsForm" >
            <!--Skills i can teach section -->
                <div class="skills-section">
                    <div class="section-header">
                        <h3>Skills I Can Teach</h3>
                        <span class="skills-counter">0/5 skills selected</span>
                    </div>

                    <div class="form-group">
                        <label for="teachCategory">Category</label>
                        <select id="teachCategory" name="teachCategory" onchange="updateSkills('teach')">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['CategoryId']); ?>">
                                    <?php echo htmlspecialchars($cat['CategoryName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group skills-dropdown-container" id="teachSkillsContainer">
                        <label for="teachSkill">Skill</label>
                        <select id="teachSkill" name="teachSkill">
                            <option value="">Select a skill from the list</option>
                        </select>
                    </div>

                    <div class="add-skill">
                        <button type="button" class="btn-add-skill" onclick="addSkill('teach')">+ Add Skill</button>
                    </div>

                    <div class="selected-skills" id="teachSkillsList">
                        <!--here the  Selected skills will appear  -->
                    </div>
                </div>

                <!-- Skills I Want to Learn Section -->
                <div class="skills-section">
                    <div class="section-header">
                        <h3>Skills I Want to Learn</h3>
                        <span class="skills-counter">0/5 skills selected</span>
                    </div>

                    <div class="form-group">
                        <label for="learnCategory">Category</label>
                        <select id="learnCategory" name="learnCategory" onchange="updateSkills('learn')">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['CategoryId']); ?>">
                                    <?php echo htmlspecialchars($cat['CategoryName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group skills-dropdown-container" id="learnSkillsContainer">
                        <label for="learnSkill">Skill</label>
                        <select id="learnSkill" name="learnSkill">
                            <option value="">Select a skill from the list</option>
                        </select>
                    </div>

                    <div class="add-skill">
                        <button type="button" class="btn-add-skill" onclick="addSkill('learn')">+ Add Skill</button>
                    </div>

                    <div class="selected-skills" id="learnSkillsList">
                        <!-- Selected skills will appear here -->
                    </div>
                </div>

                <div class="form-actions">
                   <button type="button" class="btn-back" onclick="window.location.href='signup1.php'">Back</button>
                    <button type="submit" class="btn-next">Next</button>
                </div>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.html">Login</a>
            </p>
        </div>
    </div>

    <script>
        // Initialize skills when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateSkills('teach');
            updateSkills('learn');
        });

        function updateSkills(type) {
            const categorySelect = document.getElementById(`${type}Category`);
            const skillSelect = document.getElementById(`${type}Skill`);
            
            const selectedCategory = categorySelect.value;
            
            // Clear existing skills
            skillSelect.innerHTML = '<option value="">Select a skill from the list</option>';
            
            // Fetch skills from server via AJAX
            if (selectedCategory) {
                fetch(`getSkills.php?categoryId=${selectedCategory}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Server error:', data.error);
                            skillSelect.innerHTML = '<option value="">Error loading skills</option>';
                            return;
                        }
                        
                        // Populate skills dropdown with SkillId as value
                        data.forEach(skill => {
                            const option = document.createElement('option');
                            option.value = skill.SkillId;  // Use SkillId as value
                            option.textContent = skill.SkillName;
                            skillSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching skills:', error);
                        skillSelect.innerHTML = '<option value="">Error loading skills</option>';
                    });
            }
        }

        function addSkill(type) {
            const categorySelect = document.getElementById(`${type}Category`);
            const skillSelect = document.getElementById(`${type}Skill`);
            const skillsList = document.getElementById(`${type}SkillsList`);
            const skillsCounter = document.querySelector(`#${type}SkillsList`).closest('.skills-section').querySelector('.skills-counter');
            
            const category = categorySelect.options[categorySelect.selectedIndex].text;
            const skill = skillSelect.options[skillSelect.selectedIndex].text;
            const skillId = skillSelect.value;
            
            if (!skill || skill === 'Select a skill from the list') {
                alert('Please select a skill');
                return;
            }
            
            // Check if skill already added
            const existingSkills = Array.from(skillsList.querySelectorAll('.skill-item')).map(item => 
                item.getAttribute('data-skill-id')
            );
            if (existingSkills.includes(skillId)) {
                alert('This skill is already added');
                return;
            }
            
            // Count current skills
            const currentSkills = skillsList.querySelectorAll('.skill-item').length;
            if (currentSkills >= 5) {
                alert('Maximum 5 skills allowed');
                return;
            }
            
            // Create skill item
            const skillItem = document.createElement('div');
            skillItem.className = 'skill-item';
            skillItem.setAttribute('data-skill-id', skillId);
            skillItem.innerHTML = `
                <span class="skill-name">${skill}</span>
                <button type="button" class="remove-skill" onclick="removeSkill(this, '${type}')">×</button>
            `;
            
            skillsList.appendChild(skillItem);
            
            // Update counter
            const newCount = currentSkills + 1;
            skillsCounter.textContent = `${newCount}/5 skills selected`;
            
            // Reset skill select
            skillSelect.selectedIndex = 0;
        }

        function removeSkill(button, type) {
            const skillItem = button.parentElement;
            const skillsList = document.getElementById(`${type}SkillsList`);
            const skillsCounter = skillsList.closest('.skills-section').querySelector('.skills-counter');
            
            skillItem.remove();
            
            // Update counter
            const currentSkills = skillsList.querySelectorAll('.skill-item').length;
            skillsCounter.textContent = `${currentSkills}/5 skills selected`;
        }

        // Prevent submitting if skills not selected and collect skills for submission
        document.getElementById("skillsForm").addEventListener("submit", function(event) {
            const teachCount = document.querySelectorAll("#teachSkillsList .skill-item").length;
            const learnCount = document.querySelectorAll("#learnSkillsList .skill-item").length;

            if (teachCount < 1 || learnCount < 1) {
                event.preventDefault();
                alert("Please select at least 1 skill you can teach and 1 skill you want to learn.");
            } else {
                // Collect skills and add them as hidden inputs
                const form = document.getElementById("skillsForm");
                
                // Remove any previous hidden inputs
                document.querySelectorAll('input[name="teachSkills[]"]').forEach(el => el.remove());
                document.querySelectorAll('input[name="learnSkills[]"]').forEach(el => el.remove());
                
                // Add teach skills (using SkillId)
                document.querySelectorAll("#teachSkillsList .skill-item").forEach(item => {
                    const skillId = item.getAttribute("data-skill-id");
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "teachSkills[]";
                    input.value = skillId;
                    form.appendChild(input);
                });
                
                // Add learn skills (using SkillId)
                document.querySelectorAll("#learnSkillsList .skill-item").forEach(item => {
                    const skillId = item.getAttribute("data-skill-id");
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "learnSkills[]";
                    input.value = skillId;
                    form.appendChild(input);
                });
            }
        });
    </script>
</body>
</html>