<?php
// Header Component
$user_credits = 50; // TODO: Fetch from DB/Session
$user_name = $_SESSION['user_name'] ?? 'User';
?>
<link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/header.css' ?>">
<div class="header">
    <div class="header-left">
        <div class="humberger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu h-6 w-6" aria-hidden="true">
                <path d="M4 5h16"></path>
                <path d="M4 12h16"></path>
                <path d="M4 19h16"></path>
            </svg>
        </div>
        <a href="<?= '/Skill-Service_exchanging_website-/public/home' ?>" class="header-logo">
            <img src="<?= '/Skill-Service_exchanging_website-/public/assets/images/homeinp/Swaplogo.png' ?>">
        </a>
    </div>

    <div class="header-right">
        <span class="credit-span-small"><?= $user_credits ?></span>
        <div class="header-credit">
            Credits:
            <span class="credit-span"><?= $user_credits ?></span>
        </div>
        <a href="<?= '/Skill-Service_exchanging_website-/public/notifications' ?>"
            class="header-notification">

            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-5 w-5"
                aria-hidden="true">
                <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                <path
                    d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326">
                </path>
            </svg>

        </a>
        <a href="<?= '/Skill-Service_exchanging_website-/public/profile' ?>"
            class="header-profile">
            <img src="<?= '/Skill-Service_exchanging_website-/public/assets/images/Default_pfp.svg' ?>">
        </a>
    </div>
</div>
<style>
    /* Force sidebar display when active on mobile */
    #sidebar-container.sidebar-open {
        display: flex !important;
        animation: slideIn 0.3s ease-out forwards;
    }
</style>
<script>
    // Header functionality
    document.addEventListener('DOMContentLoaded', function () {
        const hamburger = document.querySelector('.humberger');
        
        if (hamburger) {
            hamburger.addEventListener('click', function (e) {
                e.preventDefault(); 
                e.stopPropagation();
                
                const sidebarContainer = document.getElementById('sidebar-container');
                if(sidebarContainer) {
                    sidebarContainer.classList.toggle('sidebar-open');
                    console.log('Sidebar toggled. Classes:', sidebarContainer.className);
                }
            });
            
            // Close sidebar when clicking outside (optional but good UX)
            document.addEventListener('click', function(e) {
                const sidebarContainer = document.getElementById('sidebar-container');
                if (sidebarContainer && sidebarContainer.classList.contains('sidebar-open')) {
                    if (!sidebarContainer.contains(e.target) && !hamburger.contains(e.target)) {
                        sidebarContainer.classList.remove('sidebar-open');
                    }
                }
            });
        }
    });
</script>
