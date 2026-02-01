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
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    //$teachSkills and $learnSkills contain arrays of Skill IDs that the user selected.
    $teachSkills = isset($_POST['teachSkills']) ? (array) $_POST['teachSkills'] : [];
    $learnSkills = isset($_POST['learnSkills']) ? (array) $_POST['learnSkills'] : [];
    
    // Validate that at least one skill is selected for each
    if (empty($teachSkills) || empty($learnSkills)) {
        $error = "Please select at least 1 skill you can teach and 1 skill you want to learn.";
    } else {

        // Default proficiency level (no form input)
        $proficiency = 'beginner';
        


        // Save teach skills in the database
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
        
        // Save learn skills in the database
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
            
            <form action="signup2.php" method="post" id="skillsForm">


           <div id="hiddenTeachSkills" style="display: none;"></div>
           <div id="hiddenLearnSkills" style="display: none;"></div>







            <!--Skills i can teach section -->
                <div class="skills-section">
                    <div class="section-header">
                        <h3>Skills I Can Teach</h3>
                        <span class="skills-counter">0/5 skills selected</span>
                    </div>

                    <div class="form-group">
                        <label for="teachCategory">Category</label>
                        <select id="teachCategory" name="teachCategory" class="form-input form-select">
                            <option value="">-- Select a category first --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['CategoryId']) ?>">
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="teachSkill">Skill</label>
                        <select id="teachSkill" name="teachSkill" class="form-input form-select" disabled>
                            <option value="">Select a category first</option>
                        </select>
                    </div>

                    <div class="add-skill">
                        <button type="button" class="btn-add-skill" onclick="addSkill('teach')">+ Add Skill</button>
                    </div>

                    <div class="selected-skills" id="teachSkillsList">
                        <!-- Selected skills will appear here -->
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
                        <select id="learnCategory" name="learnCategory" class="form-input form-select">
                            <option value="">-- Select a category first --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['CategoryId']) ?>">
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="learnSkill">Skill</label>
                        <select id="learnSkill" name="learnSkill" class="form-input form-select" disabled>
                            <option value="">Select a category first</option>
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
        // Get DOM elements for teach section
        const teachCategorySelect = document.getElementById('teachCategory');
        const teachSkillSelect = document.getElementById('teachSkill');
        
        // Get DOM elements for learn section
        const learnCategorySelect = document.getElementById('learnCategory');
        const learnSkillSelect = document.getElementById('learnSkill');

        // Update skill options when teach category changes
        teachCategorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            teachSkillSelect.disabled = true;

            if(!selectedCategory){
                teachSkillSelect.innerHTML = '<option value="">Select a category first</option>';
                return;
            }
            
            // Fetch skills from the API endpoint
            fetch(`../dashboard/events/eventsAPI/getSkills.php?categoryid=${selectedCategory}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(skills => { 
                    teachSkillSelect.innerHTML = '<option value="">Select skill(s)</option>';

                    skills.forEach(skill => {
                        const option = document.createElement('option');
                        option.value = skill.skillid; 
                        option.textContent = skill.skillname;
                        teachSkillSelect.appendChild(option);
                    });
                    teachSkillSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching skills:', error);
                    teachSkillSelect.innerHTML = '<option value="">Error loading skills</option>';
                });
        });

        // Update skill options when learn category changes
        learnCategorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            learnSkillSelect.disabled = true;

            if(!selectedCategory){
                learnSkillSelect.innerHTML = '<option value="">Select a category first</option>';
                return;
            }
            
            // Fetch skills from the API endpoint
            fetch(`../dashboard/events/eventsAPI/getSkills.php?categoryid=${selectedCategory}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(skills => { 
                    learnSkillSelect.innerHTML = '<option value="">Select skill(s)</option>';

                    skills.forEach(skill => {
                        const option = document.createElement('option');
                        option.value = skill.skillid; 
                        option.textContent = skill.skillname;
                        learnSkillSelect.appendChild(option);
                    });
                    learnSkillSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching skills:', error);
                    learnSkillSelect.innerHTML = '<option value="">Error loading skills</option>';
                });
        });

      function addSkill(type) {
    const categorySelect = document.getElementById(`${type}Category`);
    const skillSelect = document.getElementById(`${type}Skill`);
    const skillsList = document.getElementById(`${type}SkillsList`);
    const skillsCounter = document.querySelector(`#${type}SkillsList`).closest('.skills-section').querySelector('.skills-counter');
    
    const category = categorySelect.options[categorySelect.selectedIndex].text;
    const skill = skillSelect.options[skillSelect.selectedIndex].text;
    const skillId = skillSelect.value;
    
    if (!skillId || skill === 'Select skill(s)' || skill === 'Select a category first') {
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
    
    // Create skill item for UI
    const skillItem = document.createElement('div');
    skillItem.className = 'skill-item';
    skillItem.setAttribute('data-skill-id', skillId);
    skillItem.innerHTML = `
        <span class="skill-name">${skill}</span>
        <button type="button" class="remove-skill" onclick="removeSkill(this, '${type}')">×</button>
    `;
    
    skillsList.appendChild(skillItem);
    
    // Create hidden input for form submission
    const hiddenContainer = document.getElementById(`hidden${type.charAt(0).toUpperCase() + type.slice(1)}Skills`);
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = `${type}Skills[]`;
    hiddenInput.value = skillId;
    hiddenInput.id = `${type}Skill_${skillId}`;
    hiddenContainer.appendChild(hiddenInput);
    
    // Update counter
    const newCount = currentSkills + 1;
    skillsCounter.textContent = `${newCount}/5 skills selected`;
    
    // Reset skill select
    skillSelect.selectedIndex = 0;
}

function removeSkill(button, type) {
    const skillItem = button.parentElement;
    const skillId = skillItem.getAttribute('data-skill-id');
    const skillsList = document.getElementById(`${type}SkillsList`);
    const skillsCounter = skillsList.closest('.skills-section').querySelector('.skills-counter');
    
    // Remove the hidden input
    const hiddenInput = document.getElementById(`${type}Skill_${skillId}`);
    if (hiddenInput) {
        hiddenInput.remove();
    }
    
    // Remove the UI element
    skillItem.remove();
    
    // Update counter
    const currentSkills = skillsList.querySelectorAll('.skill-item').length;
    skillsCounter.textContent = `${currentSkills}/5 skills selected`;
}


// Handle form submission
document.getElementById('skillsForm').addEventListener('submit', function(e) {
    // Count selected skills
    const teachSkills = document.querySelectorAll('#hiddenTeachSkills input').length;
    const learnSkills = document.querySelectorAll('#hiddenLearnSkills input').length;
    
    if (teachSkills === 0 || learnSkills === 0) {
        e.preventDefault(); // Prevent form submission
        
        // Show error message
        const errorDiv = document.createElement('div');
        errorDiv.style.color = 'red';
        errorDiv.style.marginBottom = '15px';
        errorDiv.style.padding = '10px';
        errorDiv.style.border = '1px solid red';
        errorDiv.style.borderRadius = '5px';
        errorDiv.textContent = 'Please select at least 1 skill you can teach and 1 skill you want to learn.';
        
        // Insert error message at the top of the form
        const formTop = document.querySelector('.signup-right h2').nextElementSibling;
        if (formTop && formTop.classList.contains('error-message')) {
            formTop.remove();
        }
        document.querySelector('.signup-right h2').after(errorDiv);
        
        return false;
    }
    
    return true;
});


    </script>



</body>
</html>