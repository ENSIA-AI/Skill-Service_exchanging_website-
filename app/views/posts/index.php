<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Posts</title>

  <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/posts.css' ?>">
  <link rel="icon" type="image/png" href="<?= '/Skill-Service_exchanging_website-/public/assets/images/favicon.png' ?>">

  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
  <!-- Header -->
  <?php include_once '../app/views/components/header.php'; ?>
  
  <!-- Sidebar -->
  <?php include_once '../app/views/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content" style="margin-top: 80px; margin-left: 240px; padding: 20px;">
    <div class="search-container">
      <input type="text" placeholder="Search for skills or services..." class="search-input">
      <button class="search-btn">
        <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/search.svg' ?>" alt="">
      </button>
    </div>
    
    <button class="create-post-btn" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/posts/create' ?>'">
      <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/plus.svg' ?>" alt="" class="plus-icon">
      <span>create new post</span>
    </button>

    <!-- Browse Categories Section (Static for now, can be dynamic later) -->
    <section class="browse-category"> 
      <h2>Browse Categories</h2>
      <div class="category-scroll">
        <div class="category-card" data-category="Technology & Programming">
           <!-- Icons need absolute paths or public relative -->
          <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/laptop-open-icon.svg' ?>" alt="Technology & Programming">
          <div class="category-info"><h3>Technology & Programming</h3></div>
        </div>
        <!-- Add more categories as needed, skipping for brevity in this first pass -->
      </div>
    </section>

    <section class="browse-posts">
      <h2 id="posts-title">Browse All Posts</h2>
      <button class="show-all-btn">Show All Posts</button>
      <div class="posts-grid">
        
        <?php if (!empty($data['posts'])): ?>
            <?php foreach ($data['posts'] as $post): ?>
                <?php 
                    $skillsHTML = $post['Skills'] ? implode(' ', array_map(function($s){ return "<span>$s</span>"; }, explode(', ', $post['Skills']))) : "<span>No specific skills</span>";
                    // Fallback likely needed for profile pic if null
                    $pfp = $post['ProfilePicture'] ? '/Skill-Service_exchanging_website-/public/assets/images/' . $post['ProfilePicture'] : '/Skill-Service_exchanging_website-/public/assets/images/Default_pfp.svg';
                ?>
                <div class="post-card" data-category="<?= htmlspecialchars($post['CategoryName']) ?>">
                  <div class="post-header">
                    <img src="<?= $pfp ?>" alt="Profile Picture" class="profile-pic">
                    <div class="profile-info">
                      <h3><?= htmlspecialchars($post['UserName']) ?></h3>
                      <div class="stars">
                        <!-- Rating logic here, static for now -->
                         <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/five-star-rating-icon.svg' ?>" alt="" class="star-icon">
                      </div>
                    </div>
                  </div>

                  <p class="post-field"><?= htmlspecialchars($post['Title']) ?></p>
                  <p class="post-description">
                    <?= htmlspecialchars(substr($post['Description'], 0, 100)) ?>...
                  </p>

                  <div class="skills">
                    <?= $skillsHTML ?>
                  </div>
                  <div class="post-actions">
                  <button class="see-details-btn" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/posts/details/' . $post['PostId'] ?>'">See Details</button>
                   <button class="like-btn">
                    <svg viewBox="0 0 24 24">
                    <path d="M12 21s-7.5-4.9-9.3-7.1C1.2 11.9 2.3 7.5 6.3 6.1 8.1 5.5 10 6.1 11 7.6c1-1.5 2.9-2.1 4.7-1.5 4 1.4 5.1 5.8 3.6 7.8C19.5 16.1 12 21 12 21z"></path>
                    </svg>
                    </button>
                  </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts found. Be the first to create one!</p>
        <?php endif; ?>

      </div>
    </section>
  </main>
  
  <script src="<?= '/Skill-Service_exchanging_website-/public/assets/js/posts.js' ?>"></script>
</body>
</html>
