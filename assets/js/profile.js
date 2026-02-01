document.addEventListener('DOMContentLoaded', function () {
    console.log('Profile page loaded');
    
    // 1. Initialize the Tabs
    setupTabNavigation();
    
    // 2. Optional: Add a check for empty availability
    const container = document.getElementById('availability-list-container');
    if (container && container.children.length === 0) {
        container.innerHTML = '<p style="color: #d4dedd; margin: 20px;">No availability set yet.</p>';
    }
});

function setupTabNavigation() {
    const navSpans = document.querySelectorAll('.nav-item span');
    const tabContents = document.querySelectorAll('.tab-content');

    navSpans.forEach(span => {
        span.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            
            console.log('Attempting to open tab:', targetId);

            navSpans.forEach(s => s.classList.remove('active'));
            
            tabContents.forEach(tab => tab.classList.remove('active'));

            this.classList.add('active');

            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            } else {
                console.error('Could not find element with ID:', targetId);
            }
        });
    });
}