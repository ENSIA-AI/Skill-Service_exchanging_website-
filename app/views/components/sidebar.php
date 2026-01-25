<div class="container" id="sidebar-container">
    <div class="sidebar">
      <!-- <h3>swap</h3> Removed as requested -->
      <!-- Hamburger is in Header -->
      
      <style>
          /* Force standard icons to be white */
          .sidebar .icon {
              filter: brightness(0) invert(1);
          }
          /* Force logout icon to be Red to match text */
          .sidebar .logout-icon {
              filter: invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%);
          }
      </style>
      
      <!-- Using divs with specific classes to match sidebar.css expectations -->
      <!-- sidebar.css targets .sidebar > div for styling (height, hover, etc.) -->
      
      <div class="home-section" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/home' ?>'">
          <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/home.svg' ?>" alt="Home Icon" class="icon">
          <span class="name">Home</span>
      </div>
          
      <div class="events-section" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/events' ?>'">
         <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/calendar.svg' ?>" class="icon" alt="Events icon">
          <span class="name">Events</span>
      </div>
        
      <div class="notification-section" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/notifications' ?>'">
        <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/bell.svg' ?>" class="icon" alt="Notifications icon">
        <span class="name">Notifications</span>
      </div>
       
      <div class="profile-section" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/profile' ?>'">
        <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/user.svg' ?>" class="icon" alt="Profile icon">
        <span class="name">Profile</span>
      </div>
       
      <div class="logout-section" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/auth/logout' ?>'">
        <img src="<?= '/Skill-Service_exchanging_website-/public/assets/icons/log-out.svg' ?>" class="logout-icon" alt="Logout icon">
        <span class="name">Logout</span>
      </div>

    </div>
</div>
<script>
    // Ensure clicks on divs work as links
    // (The onclick attributes above handle it, but we can add cursor style if needed, though CSS handles cursor:pointer)
</script>
