// Header functionality
document.addEventListener('DOMContentLoaded', function() {
  const hamburger = document.querySelector('.humberger');
  
  console.log('Header script loaded, hamburger found:', !!hamburger);

  // Hamburger menu click event
  if (hamburger) {
      hamburger.addEventListener('click', function(e) {
          e.stopPropagation();
          console.log('Hamburger clicked');
          
          // Dispatch custom event to toggle sidebar
          const toggleEvent = new CustomEvent('toggleSidebar');
          document.dispatchEvent(toggleEvent);
      });
  }

  // Add hover effects to header elements
  const headerElements = document.querySelectorAll('.header-credit, .header-notification, .header-profile');
  headerElements.forEach(element => {
      element.addEventListener('mouseenter', function() {
          this.style.opacity = '0.8';
      });
      element.addEventListener('mouseleave', function() {
          this.style.opacity = '1';
      });
  });
});