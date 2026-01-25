<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Post</title>
    <!-- CSS path update -->
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/addpost.css' ?>"> 
</head>
<body>
    <main class="content">
        <form class="form-container" id="post-form" action="<?= '/Skill-Service_exchanging_website-/public/posts/create' ?>" method="POST">
            <h2>Create New Post</h2>
            <p class="subtitle">Share your skills with the community</p>
            
            <!-- Basic Information -->
            <section class="form-section">
                <h3>Basic Information</h3>
                <div class="form-group">
                    <label for="title">Service Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Professional Web Development Services" 
                           required minlength="5" maxlength="100">
                    <span class="error-message" id="title-error"></span>
                </div>
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Describe your service in detail. What will you teach? What experience do you have?"
                              required minlength="20" maxlength="500"></textarea>
                    <small id="char-count">0/500 characters</small>
                    <span class="error-message" id="description-error"></span>
                </div>
            </section>
            
            <!-- Exchange Details -->
            <section class="form-section">
                <h3>Exchange Details</h3>
                <div class="form-group">
                    <label for="exchange">What do you want in exchange? * (Currently textual description)</label>
                    <input type="text" id="exchange" name="exchange" placeholder="e.g., Spanish lessons, graphic design, etc."
                           required minlength="3" maxlength="100">
                    <span class="error-message" id="exchange-error"></span>
                </div>
                <div class="form-inline">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <!-- In a real app, populate dynamically from Category table -->
                            <option value="1">Technology & Programming</option>
                            <option value="2">Design & Creative</option>
                            <option value="3">Photography & Video</option>
                            <option value="4">Writing & Content</option>
                            <option value="5">Music & Performance</option>
                            <option value="6">Cooking & Culinary</option>
                            <option value="7">Health & Fitness</option>
                            <option value="8">Languages</option>
                            <!-- Add remaining categories mapping IDs correctly -->
                        </select>
                        <span class="error-message" id="category-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="service-type">Service Type *</label>
                        <select id="service-type" name="post_type" required>
                            <option value="">Select service type</option>
                            <option value="online">Online</option>
                            <option value="in-person">In-person</option>
                        </select>
                        <span class="error-message" id="service-type-error"></span>
                    </div>
                </div>
            </section>
            
            <!-- Skills/Tags (Skipping complex JS for now, just a text input or TODO) -->
            <!-- <section class="form-section">
                <h3>Skills/Tags (Max 5)</h3>
               
            </section> -->
            
            <!-- Pricing & Duration -->
            <section class="form-section">
                <h3>Pricing & Duration</h3>
                <div class="form-inline">
                    <div class="form-group">
                        <label for="credits">Credits per Hour *</label>
                        <input type="number" id="credits" name="credits" placeholder="e.g., 50"
                               required min="1" max="1000">
                        <span class="error-message" id="credits-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="duration">Typical Session Duration (Minutes)</label>
                        <select id="duration" name="duration" required>
                            <option value="">Select duration</option>
                            <option value="30">30 minutes</option>
                            <option value="60">60 minutes</option>
                            <option value="90">90 minutes</option>
                            <option value="120">120 minutes</option>
                        </select>
                        <span class="error-message" id="duration-error"></span>
                    </div>
                </div>
            </section>

            <!-- Location & Availability -->
            <section class="form-section">
                <h3>Location & Availability</h3>
                <div class="form-inline">
                    <div class="form-group">
                        <label for="location">Location (City, State/Country)</label>
                        <input type="text" id="location" name="location" placeholder="e.g., San Francisco, CA"
                               maxlength="100">
                        <span class="error-message" id="location-error"></span>
                    </div>
                </div>
                <!-- Availability Date Picked -->
                 <div class="form-group">
                        <label for="available_date">Available From</label>
                        <input type="datetime-local" id="available_date" name="available_date" required>
                 </div>
            </section>
            
            <!-- Buttons -->
            <div class="form-actions">
                <button class="btn cancel" type="button" onclick="window.history.back()">Cancel</button>
                <button class="btn submit" type="submit">Post</button>
            </div>
        </form>
    </main>
    
    <script src="<?= '/Skill-Service_exchanging_website-/public/assets/js/addpost.js' ?>"></script>
</body>
</html>
