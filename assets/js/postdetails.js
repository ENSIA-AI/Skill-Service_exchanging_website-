// Service Detail Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Calendar Navigation
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const calendarMonthLabel = document.querySelector('.calendar-month');
    const calendarGrid = document.querySelector('.calendar-grid');
    
    let currentMonth = 10; // November (0-indexed)
    let currentYear = 2025;
    let selectedDate = 19; // Currently selected day
    
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    // Function to generate calendar
    function generateCalendar(month, year) {
        // Clear existing calendar days (keep headers)
        const existingDays = calendarGrid.querySelectorAll('.calendar-day');
        existingDays.forEach(day => day.remove());
        
        // Get first day of month and number of days in month
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        // Add previous month's days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = document.createElement('div');
            day.classList.add('calendar-day', 'prev-month');
            day.textContent = daysInPrevMonth - i;
            calendarGrid.appendChild(day);
        }
        
        // Add current month's days
        for (let i = 1; i <= daysInMonth; i++) {
            const day = document.createElement('div');
            day.classList.add('calendar-day');
            day.textContent = i;
            
            // Select the 19th by default for November 2025
            if (month === 10 && year === 2025 && i === selectedDate) {
                day.classList.add('selected');
            }
            
            // Add click handler
            day.addEventListener('click', function() {
                // Remove selected class from all current month days
                const allDays = calendarGrid.querySelectorAll('.calendar-day:not(.prev-month):not(.next-month)');
                allDays.forEach(d => d.classList.remove('selected'));
                
                // Add selected class to clicked day
                this.classList.add('selected');
                selectedDate = parseInt(this.textContent);
                
                console.log(`Selected date: ${monthNames[currentMonth]} ${selectedDate}, ${currentYear}`);
            });
            
            calendarGrid.appendChild(day);
        }
        
        // Add next month's days to fill the grid
        const totalCells = calendarGrid.children.length - 7; // Subtract headers
        const remainingCells = (Math.ceil((totalCells) / 7) * 7) - totalCells;
        
        for (let i = 1; i <= remainingCells; i++) {
            const day = document.createElement('div');
            day.classList.add('calendar-day', 'next-month');
            day.textContent = i;
            calendarGrid.appendChild(day);
        }
    }
    
    // Function to update calendar
    function updateCalendar() {
        calendarMonthLabel.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        generateCalendar(currentMonth, currentYear);
    }
    
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateCalendar();
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateCalendar();
        });
    }
    
    // Initialize calendar
    updateCalendar();
    
    // Favorite Button Toggle
    const favoriteBtn = document.querySelector('.favorite-btn');
    let isFavorited = false;
    
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            isFavorited = !isFavorited;
            
            if (isFavorited) {
                this.style.backgroundColor = 'var(--clr-btn)';
                this.querySelector('svg').setAttribute('fill', 'currentColor');
                console.log('Service added to favorites');
            } else {
                this.style.backgroundColor = 'var(--clr-main)';
                this.querySelector('svg').setAttribute('fill', 'none');
                console.log('Service removed from favorites');
            }
        });
    }
    
    // Book This Service Button
    const bookBtn = document.querySelector('.btn-primary');
    
    if (bookBtn) {
        bookBtn.addEventListener('click', function() {
            console.log('Book This Service clicked');
            alert('Booking functionality will be implemented here!');
            // In a real implementation, this would open a booking modal or redirect to booking page
        });
    }
    
    // Message Provider Button
    const messageBtn = document.querySelector('.btn-secondary');
    
    if (messageBtn) {
        messageBtn.addEventListener('click', function() {
            console.log('Message Provider clicked');
            alert('Messaging functionality will be implemented here!');
            // In a real implementation, this would open a messaging interface
        });
    }
    
    // Payment Option Selection
    const paymentBadges = document.querySelectorAll('.payment-badge');
    
    paymentBadges.forEach(badge => {
        badge.addEventListener('click', function() {
            // Remove active class from all badges
            paymentBadges.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked badge
            this.classList.add('active');
            
            console.log(`Payment option selected: ${this.textContent}`);
        });
    });
    
    // Smooth scroll for internal links (if any are added)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add hover effects to skill tags
    const skillTags = document.querySelectorAll('.skill-tag, .seeking-tag');
    
    skillTags.forEach(tag => {
        tag.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        });
        
        tag.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Initialize smooth transitions
    const cards = document.querySelectorAll('.info-card, .service-description, .skills-section, .exchange-section, .requirements-section');
    
    cards.forEach(card => {
        card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Initial check
    handleResize();
    
    // Add resize listener
    window.addEventListener('resize', handleResize);

    // Schedule functionality for availability - Single selection mode
    const dayCheckboxes = document.querySelectorAll('.day-check');
    const scheduleDays = document.querySelectorAll('.schedule-day');
    let selectedCheckbox = null; // Track the currently selected checkbox
    
    // Initialize unavailable days (Monday and Sunday)
    const unavailableDays = ['monday', 'sunday'];
    unavailableDays.forEach(dayName => {
        const dayElement = document.querySelector(`[data-day="${dayName}"]`);
        if (dayElement) {
            dayElement.classList.add('unavailable');
            const checkbox = dayElement.querySelector('.day-check');
            if (checkbox) {
                checkbox.disabled = true;
            }
            const timeInputs = dayElement.querySelectorAll('.time-input');
            timeInputs.forEach(input => {
                input.disabled = true;
            });
        }
    });
    
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            // If this checkbox is disabled, prevent the click
            if (this.disabled) {
                e.preventDefault();
                return;
            }
            
            // Prevent unchecking - always keep one checked
            if (!this.checked && selectedCheckbox === this) {
                e.preventDefault();
                this.checked = true;
                return;
            }
            
            // If trying to check this box
            if (this.checked) {
                // Uncheck all other checkboxes (strict single selection)
                dayCheckboxes.forEach(otherCheckbox => {
                    if (otherCheckbox !== this && !otherCheckbox.disabled) {
                        otherCheckbox.checked = false;
                        const otherDay = otherCheckbox.closest('.schedule-day');
                        const otherTimeInputs = otherDay.querySelectorAll('.time-input');
                        otherTimeInputs.forEach(input => {
                            input.disabled = true;
                        });
                    }
                });
                
                // Enable time inputs for selected day
                selectedCheckbox = this;
                const selectedDay = this.closest('.schedule-day');
                const timeInputs = selectedDay.querySelectorAll('.time-input');
                timeInputs.forEach(input => {
                    input.disabled = false;
                });
                
                console.log('Selected day: ' + this.id);
            }
        });
        
        // Also add change listener as backup
        checkbox.addEventListener('change', function(e) {
            // Ensure only this checkbox can be checked
            if (this.checked && !this.disabled) {
                dayCheckboxes.forEach(otherCheckbox => {
                    if (otherCheckbox !== this && !otherCheckbox.disabled) {
                        otherCheckbox.checked = false;
                        const otherDay = otherCheckbox.closest('.schedule-day');
                        const otherTimeInputs = otherDay.querySelectorAll('.time-input');
                        otherTimeInputs.forEach(input => {
                            input.disabled = true;
                        });
                    }
                });
                
                // Enable time inputs
                selectedCheckbox = this;
                const selectedDay = this.closest('.schedule-day');
                const timeInputs = selectedDay.querySelectorAll('.time-input');
                timeInputs.forEach(input => {
                    input.disabled = false;
                });
            }
        });
    });
    
    // Add loading animation
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.style.opacity = '0';
        mainContent.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            mainContent.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            mainContent.style.opacity = '1';
            mainContent.style.transform = 'translateY(0)';
        }, 100);
    }
    
    console.log('Service Detail page initialized successfully');
});