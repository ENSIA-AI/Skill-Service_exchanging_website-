<?php
session_start();
require_once 'includes/dbh.inc.php';


$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

try {
    $stmt = $connection->prepare("SELECT * FROM Users WHERE UserId = :id");
    $stmt->execute([':id' => $userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $currentName     = !empty($user['FullName']) ? $user['FullName'] : 'Your Full hhhhh Name';
        $currentUsername = !empty($user['UserName']) ? $user['UserName'] : 'username';
        $currentPhoto    = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : '../../assets/images/Default_pfp.svg';
        $currentProfessionalTitle = !empty($user['ProfessionalTitle']) ? $user['ProfessionalTitle'] : 'Your Professional Title';
        $currentLocation = !empty($user['Location']) ? $user['Location'] : 'Your Location';
        $Datestring     = !empty($user['UserSince']) ? $user['UserSince'] : 'Year';
        $UserSince      = date("Y", strtotime($Datestring));
        $currentRating  = !empty($user['Rating']) ? $user['Rating'] : '0';
        $ExchangesCount = !empty($user['ExchangeCount']) ? $user['ExchangeCount'] : '0';
        $Discription    = !empty($user['Description']) ? $user['Description'] : 'This is your profile description. Tell people more about yourself!';
        $currentEmail    = !empty($user['Email']) ? $user['Email'] : '';
        $currentDescription = htmlspecialchars($Discription);
        $currentPhone    = !empty($user['PhoneNumber']) ? $user['PhoneNumber'] : '';
    } else {
        $currentPhoto = '../../assets/images/Default_pfp.svg';
        $currentName = 'User Not Found';
        $currentUsername = 'unknown';
        $currentProfessionalTitle = 'Your Professional Title';
        $currentLocation = 'Your Location';
        $UserSince = 'Year';
        $currentRating = '0';
        $ExchangesCount = '0';
    }
    //***************************************************************************************************************** */
    $currentRating = 50; // For testing purposes only
    $rating = ($currentRating / 20);
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
}
try {
    $query = "SELECT uss.SkillId, s.SkillName 
              FROM user_seeking_skills uss
              JOIN Skills s ON uss.SkillId = s.SkillId
              WHERE uss.UserId = :id";
    $stmt = $connection->prepare($query);
    $stmt->execute(['id' => $userId]);
    $seekingSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="icon" href="../../assets/icons/favicon_io/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/editpersonalprofile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
</head>

<body>
    <?php include '../../components/header_t.php'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content">

        <form id="update-profile-picture" method="POST" action="upload_picture.php" enctype="multipart/form-data">
            <div class="section profile-picture-section">
                <div class="profile-picture-section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25"
                        role="img" aria-label="Camera icon" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: white;">
                        <title>Camera</title>
                        <path
                            d="M3 7.5h3l1.2-1.8A1 1 0 0 1 8.1 5h7.8a1 1 0 0 1 .9.7L18 7.5h3A1.5 1.5 0 0 1 22.5 9V19.5A1.5 1.5 0 0 1 21 21H3A1.5 1.5 0 0 1 1.5 19.5V9A1.5 1.5 0 0 1 3 7.5z" />
                        <circle cx="12" cy="14" r="3.2" />
                    </svg>
                    <span class="section-title">Profile Picture</span>
                </div>
                <div class="profile-picture-section-content">
                    <figure style="margin: 0;">
                        <div class="edit-profile-avatar">
                            <!-- img src="../../assets/icons/favicon_io/android-chrome-512x512.png" alt=""> -->

                            <img id="profile-preview" src="<?= $currentPhoto ?>" alt="Profile">
                        </div>
                    </figure>
                    <div class="profile-picture-section-second-column">
                        <h4><span class="section-subtitle">Upload New Photo</span></h4>
                        <span class="muted-text picture-instruction-text">JPG, PNG or GIF. Max size 2MB.
                            Recommended:
                            400x400px</span>

                        <div class="profile-btn-1">
                            <input type="file"
                                id="upload-picture-desktop"
                                name="profile_picture_desktop"
                                title="Upload new profile picture"
                                accept="image/*" hidden>
                            <label for="upload-picture-desktop" class="upload-new-picture">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20" width="20"
                                    height="20" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                                Upload
                            </label>
                            <button type="submit" class="save-picture-btn" name="save-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Save
                            </button>
                            <button type="button" class="remove-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20" width="16"
                                    height="16" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <div class="profile-btn-2">
                    <input type="file"
                        id="upload-picture-mobile"
                        name="profile_picture_mobile"
                        title="Upload new profile picture"
                        accept="image/*" hidden>
                    <label for="upload-picture-mobile" class="upload-new-picture">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20" width="20" height="20"
                            fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Upload
                    </label>
                    <button type="submit" class="save-picture-btn" name="save-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save
                    </button>
                    <button type="button" class="remove-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 21" width="16" height="16"
                            fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                        </svg>
                        REMOVE
                    </button>
                </div>
            </div>
        </form>
        <form id="update-profile-form" method="POST" enctype="multipart/form-data">
            <div class="section">
                <div class="profile-picture-section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25"
                        role="img" aria-label="User icon" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="color: white;">
                        <title>User</title>
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="section-title">Basic Information</span>
                </div>
                <div class="">
                    <div class="label">
                        <label for="username">username*</label>
                    </div>
                    <input class="textbox user-name-icon" id="username" name="username" type="text" onkeyup="checkUser(this.value)"
                        value="<?= $currentUsername ?>" pattern="^(?![_.])(?!.*[_.]{2})[a-z0-9._]{3,20}(?<![_.])$"
                        title="3–20 chars, lowercase letters, numbers, underscores (_) and dots (.) only. Cannot start or end with _ or ., or contain consecutive _ or ."
                        required />
                    <span id="username-status"></span>
                </div>
                <div class="">
                    <div class="label">
                        <label for="fullname">Full Name*</label>
                    </div>
                    <input class="textbox full-name-icon" id="fullname" name="fullname" type="text"
                        value="<?= $currentName ?>" required />
                </div>
                <div class="">
                    <div class="label">
                        <label for="professionaltitle">Professional Title*</label>
                    </div>
                    <input class="textbox professional-title-icon" id="professional_title" name="professional_title" type="text"
                        value="<?= $currentProfessionalTitle ?>" required />
                </div>
                <div class="">
                    <div class="label">
                        <label for="location">Location*</label>
                    </div>
                    <input class="textbox location-icon" id="location" name="location" type="text"
                        value="<?= $currentLocation ?>" required />
                </div>
                <div class="">
                    <div class="label">
                        <label for="aboutme">About Me</label>
                    </div>
                    <textarea class="textbox" id="about_me" name="about_me"
                        style="height: 15rem; padding-left: 10px;"><?= $currentDescription ?></textarea>
                </div>
            </div>
            <div class="section">
                <div class="profile-picture-section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25"
                        role="img" aria-label="Settings icon" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: white;">
                        <title>Settings</title>
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33
                                        1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51
                                        1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06
                                        a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3
                                        a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1
                                        1.65 1.65 0 0 0-.33-1.82l-.06-.06
                                        a2 2 0 1 1 2.83-2.83l.06.06
                                        a1.65 1.65 0 0 0 1.82.33H9
                                        a1.65 1.65 0 0 0 1-1.51V3
                                        a2 2 0 0 1 4 0v.09
                                        a1.65 1.65 0 0 0 1 1.51
                                        1.65 1.65 0 0 0 1.82-.33l.06-.06
                                        a2 2 0 1 1 2.83 2.83l-.06.06
                                        a1.65 1.65 0 0 0-.33 1.82V9
                                        a1.65 1.65 0 0 0 1.51 1H21
                                        a2 2 0 0 1 0 4h-.09
                                        a1.65 1.65 0 0 0-1.51 1z" />
                    </svg>
                    <span class="section-title">Contact Information & Security</span>
                </div>
                <div class="">
                    <div class="label">
                        <label for="email">Email*</label>
                    </div>
                    <input class="textbox email-icon" id="email" name="email" type="email"
                        value="<?= $currentEmail ?>" required />
                </div>
                <div class="">
                    <div class="label">
                        <label for="phone">Phone*</label>
                    </div>
                    <input class="textbox phone-icon" id="phone" name="phone" type="tel" value="<?= $currentPhone ?>"
                        required />
                </div>
                <div class="form-group">
                    <div class="label">
                        <label for="password">Password</label>
                    </div>
                    <button type="button" id="openPasswordBtn" class="password-btn">
                        Change Security Password
                    </button>
                </div>
            </div>
            <div class="section">
                <div class="profile-picture-section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25"
                        role="img" aria-label="Award icon" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: white;">
                        <title>Award</title>
                        <circle cx="12" cy="8" r="7" />
                        <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.11" />
                    </svg>

                    <span class="section-title">Skills You're Offering</span>
                </div>
                <div class="cards-section">
                    <?php
                    $stmt = $connection->prepare("SELECT us.*, s.SkillName 
                                  FROM UserSkills us 
                                  JOIN Skills s ON us.SkillId = s.SkillId 
                                  WHERE us.UserId = :userId AND us.SkillType = 'teach'");
                    $stmt->execute(['userId' => $userId]);
                    $userSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($userSkills as $skill): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><?= htmlspecialchars($skill['SkillName']) ?></h3>
                                <input type="hidden" name="skill_ids[]" value="<?= $skill['SkillId'] ?>">
                                <button type="button" class="delete-skill">×</button>
                            </div>

                            <div class="proficiency-section">
                                <div class="section-header">
                                    <span class="label">Proficiency Level</span>
                                    <span class="proficiency-value"><?= $skill['ProficiencyLevel'] ?>%</span>
                                </div>
                                <div class="slider-container">
                                    <input type="range" class="proficiency-slider"
                                        name="skill_proficiency[<?= $skill['SkillId'] ?>]"
                                        min="0" max="100" value="<?= $skill['ProficiencyLevel'] ?>"
                                        oninput="updateSlider(this)">
                                </div>
                            </div>

                            <div class="rate-section">
                                <div class="label"><label>Rate (credits/hour)*</label></div>
                                <input type="number" name="skill_rate[<?= $skill['SkillId'] ?>]"
                                    class="textbox" value="50" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div>


                    <div class="title">Add New Skill</div>
                    <div class="select-wrap">
                        <label for="category"><span class="label">Choose a
                                Category:</span></label><br>
                        <select class="select dropdown" id="offering-category">
                            <option value="">Choose a Category</option>
                            <?php
                            $stmt = $connection->prepare("SELECT * FROM Category");
                            $stmt->execute();
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $CategoryIDv = $categories['CategoryId'];
                            foreach ($categories as $category) {
                                echo "<option value=\"{$category['CategoryId']}\">{$category['CategoryName']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="select-wrap">
                        <label for="skills"><span class="label">Choose a Skill:</span></label><br>
                        <select class="select dropdown" id="offering-skills" disabled>
                            <option value="">Choose a Skill</option>
                            <?php
                            $stmt = $connection->prepare("SELECT SkillId, SkillName, CategoryId FROM Skills");
                            $stmt->execute();
                            $allSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($allSkills as $skill) {
                                echo "<option value=\"{$skill['SkillId']}\" data-category=\"{$skill['CategoryId']}\" style=\"display:none;\">{$skill['SkillName']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="proficiency-section">
                        <div class="section-header">
                            <span class="label">Proficiency Level</span>
                            <span class="proficiency-value">80%</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" class="proficiency-slider" min="0" max="100" value="80" oninput="updateSlider(this)">
                        </div>
                    </div>

                    <div class="rate-section">
                        <div class="label">
                            <label for="offering-rate">Rate (credits/hour)*</label>
                        </div>
                        <input type="number" id="offering-rate" name="offering_rate" class="textbox"
                            placeholder="50" min="1" disabled />
                    </div>

                    <div>
                        <button type="button" class="btn btn-1" id="add-offering-skill-btn" disabled>
                            Add Skill
                        </button>
                    </div>
                </div>
            </div>
            <div class="section">
                <div class="profile-picture-section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25"
                        role="img" aria-label="Award icon" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: white;">
                        <title>Award</title>
                        <circle cx="12" cy="8" r="7" />
                        <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.11" />
                    </svg>

                    <span class="section-title">Skills You're Seeking</span>
                </div>
                <div>
                    <ul class="list" id="seeking-list">
                        <?php if (!empty($seekingSkills)): ?>
                            <?php foreach ($seekingSkills as $skill): ?>
                                <li>
                                    <?php echo htmlspecialchars($skill['SkillName']); ?>
                                    <input type="hidden" name="seeking_skills[]" value="<?php echo $skill['SkillId']; ?>">
                                    <span class="delete-from-list">&times;</span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div>
                        <h3>
                            <span class="muted-text">
                                <p id="skill-counter" style="color:white; margin-top:8px;"></p>
                            </span>
                        </h3>
                    </div>
                </div>
                <div class="section sub-section">
                    <div class="select-wrap">
                        <label for="category"><span class="label">Choose a
                                Category:</span></label><br>
                        <select class="select dropdown" id="seeking-category">
                            <option value="">Choose a Category</option>
                            <?php
                            $stmt = $connection->prepare("SELECT * FROM Category");
                            $stmt->execute();
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $CategoryIDv = $categories['CategoryId'];
                            foreach ($categories as $category) {
                                echo "<option value=\"{$category['CategoryId']}\">{$category['CategoryName']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="select-wrap">
                        <label for="skills"><span class="label">Choose a Skill:</span></label><br>
                        <select id="seeking-skills" class="select dropdown" disabled>
                            <option value="">Choose a Skill</option>
                            <?php foreach ($allSkills as $skill): ?>
                                <option value="<?= $skill['SkillId'] ?>" data-category="<?= $skill['CategoryId'] ?>">
                                    <?= htmlspecialchars($skill['SkillName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn" id="add-seeking-skill-btn" style="margin-top:10px;" disabled>Add
                            Skill</button>
                    </div>

                </div>
                <div>
                    <span class="muted-text">Add skills you're interested in learning to help us find better
                        matches for you.</span>
                </div>
            </div>

            <div style="display: flex; justify-content: center; align-items: flex-end;">
                <button class="btn btn-2-save" type="submit">
                    Save Changes
                </button>
                <div class="">
                    <button class="btn cancel-btn">
                        <a name="Cancelbutton" href="profile.php">Cancel</a>
                    </button>
                </div>
            </div>
        </form>
    </main>
    <script src="../../assets/js/editProfile.js"></script>
    <div id="passwordModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Password</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label>Enter New Password</label>
                    <input type="password" name="new_password" required class="password-textbox" style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required class="password-textbox" style="width: 100%;">
                </div>
                <div id="password-error"></div>
                <button type="submit" class="btn btn-2-save" style="width: 100%; margin-top: 15px;">Update Password</button>
            </form>
        </div>
    </div>
</body>

</html>