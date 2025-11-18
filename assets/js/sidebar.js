// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarContainer = document.querySelector('.container');
    const sidebar = document.querySelector('.sidebar');
    const logoutSection = document.querySelector('.logout-section');
    
    console.log('Sidebar script loaded, container found:', !!sidebarContainer);

    let isSidebarVisible = false;

    // Handle sidebar toggle from header
    document.addEventListener('toggleSidebar', function() {
        if (window.innerWidth <= 768) {
            isSidebarVisible = !isSidebarVisible;
            
            if (isSidebarVisible) {
                // Show sidebar
                sidebarContainer.style.display = 'flex';
                sidebarContainer.style.animation = 'slideIn 0.3s ease forwards';
            } else {
                // Hide sidebar with animation
                sidebarContainer.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => {
                    sidebarContainer.style.display = 'none';
                }, 300);
            }
            
            console.log('Sidebar toggled, visible:', isSidebarVisible);
        }
    });

    // Handle logout click
    if (logoutSection) {
        logoutSection.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                console.log('User logged out');
                window.location.href = '/login.html';
            }
        });
    }

    // Make all sidebar sections clickable and navigate to their links
    const sidebarSections = document.querySelectorAll('.sidebar > div');
    
    sidebarSections.forEach(section => {
        section.style.cursor = 'pointer';
        
        section.addEventListener('click', function(e) {
            // Don't trigger if clicking directly on a link
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            
            const link = this.querySelector('a');
            if (link && link.href) {
                if (this.classList.contains('logout-section')) {
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = '/login.html';
                    }
                } else {
                    window.location.href = link.href;
                }
            }
        });
    });

    // Close sidebar when clicking on a link (mobile)
    sidebar.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && window.innerWidth <= 768) {
            setTimeout(() => {
                sidebarContainer.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => {
                    sidebarContainer.style.display = 'none';
                    isSidebarVisible = false;
                }, 300);
            }, 100);
        }
    });

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            isSidebarVisible &&
            !e.target.closest('.container') && 
            !e.target.closest('.humberger')) {
            
            sidebarContainer.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                sidebarContainer.style.display = 'none';
                isSidebarVisible = false;
            }, 300);
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebarContainer.style.display = 'flex';
            sidebarContainer.style.animation = '';
            isSidebarVisible = false;
        } else {
            sidebarContainer.style.display = 'none';
            isSidebarVisible = false;
        }
    });

    // Set initial state based on screen size
    if (window.innerWidth <= 768) {
        sidebarContainer.style.display = 'none';
        isSidebarVisible = false;
    }
});