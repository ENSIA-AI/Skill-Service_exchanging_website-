/**
 * Notification Interaction Handler
 * Add this JavaScript to notifications.html or notifications.php to handle accept/reject buttons
 * 
 * Uses event delegation to handle dynamically created buttons
 */

/**
 * Show a toast notification on the page
 * @param {string} message - The message to display
 * @param {string} type - 'success', 'error', or 'info'
 */
function showToast(message, type = 'info') {
    // Remove any existing toast
    const existingToast = document.getElementById('notification-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.id = 'notification-toast';
    toast.className = `toast toast-${type}`;
    
    // Icon based on type
    let icon = '';
    if (type === 'success') {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
    } else if (type === 'error') {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    } else {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
    }
    
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Add styles if not already present
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            #notification-toast {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideDown 0.3s ease;
                max-width: 500px;
            }
            @keyframes slideDown {
                from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                to { transform: translateX(-50%) translateY(0); opacity: 1; }
            }
            .toast-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
            .toast-error { background: linear-gradient(135deg, #dc3545, #e74c3c); color: white; }
            .toast-info { background: linear-gradient(135deg, #17a2b8, #3498db); color: white; }
            .toast-icon { flex-shrink: 0; }
            .toast-message { font-size: 15px; font-weight: 500; line-height: 1.4; }
            .toast-close { 
                background: none; 
                border: none; 
                color: white; 
                font-size: 24px; 
                cursor: pointer; 
                padding: 0 0 0 10px;
                opacity: 0.8;
            }
            .toast-close:hover { opacity: 1; }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds for success, 8 seconds for errors
    const duration = type === 'error' ? 8000 : 5000;
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
}

/**
 * Convert technical error messages to user-friendly messages
 */
function getUserFriendlyError(error) {
    // Map of technical errors to friendly messages
    const errorMap = {
        'Attendee has insufficient credits': 'The person requesting to join doesn\'t have enough credits. They\'ll need to add more credits before you can accept.',
        'insufficient credits': 'Not enough credits available. The requester needs to top up their balance.',
        'already confirmed': 'This request has already been accepted.',
        'already accepted': 'This booking has already been accepted.',
        'not in pending status': 'This request has already been processed.',
        'Not logged in': 'Your session has expired. Please log in again.',
        'Unauthorized': 'You don\'t have permission to perform this action.',
        'not found': 'This request could not be found. It may have been deleted.',
        'Event is full': 'This event has reached its maximum capacity.',
    };
    
    // Check if error matches any known patterns
    for (const [key, friendly] of Object.entries(errorMap)) {
        if (error.toLowerCase().includes(key.toLowerCase())) {
            return friendly;
        }
    }
    
    // Return original if no match
    return error;
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Use event delegation on the notifications container for dynamically created buttons
    const container = document.getElementById('notifications-container') || document.body;
    
    container.addEventListener('click', function(e) {
        const target = e.target;
        
        // Handle Accept Exchange button
        if (target.matches('[data-action="accept-exchange"]')) {
            e.preventDefault();
            const exchangeId = target.getAttribute('data-exchange-id');
            
            if (!exchangeId) {
                showToast('Unable to process request. Please refresh the page and try again.', 'error');
                return;
            }
            
            if (confirm('Accept this booking request?')) {
                acceptExchange(exchangeId, target);
            }
        }
        
        // Handle Reject Exchange button
        if (target.matches('[data-action="reject-exchange"]')) {
            e.preventDefault();
            const exchangeId = target.getAttribute('data-exchange-id');
            
            if (!exchangeId) {
                showToast('Unable to process request. Please refresh the page and try again.', 'error');
                return;
            }
            
            if (confirm('Decline this booking request?')) {
                rejectExchange(exchangeId, target);
            }
        }
        
        // Handle Accept Event Attendee button
        if (target.matches('[data-action="accept-event-attendee"]')) {
            e.preventDefault();
            const eventId = target.getAttribute('data-event-id');
            const attendeeId = target.getAttribute('data-attendee-id');
            
            if (!eventId || !attendeeId) {
                showToast('Unable to process request. Please refresh the page and try again.', 'error');
                return;
            }
            
            if (confirm('Accept this event join request?')) {
                acceptEventAttendee(eventId, attendeeId, target);
            }
        }
        
        // Handle Reject Event Attendee button
        if (target.matches('[data-action="reject-event-attendee"]')) {
            e.preventDefault();
            const eventId = target.getAttribute('data-event-id');
            const attendeeId = target.getAttribute('data-attendee-id');
            
            if (!eventId || !attendeeId) {
                showToast('Unable to process request. Please refresh the page and try again.', 'error');
                return;
            }
            
            if (confirm('Decline this event join request?')) {
                rejectEventAttendee(eventId, attendeeId, target);
            }
        }
    });
});

/**
 * Send accept request to server
 * @param {number} exchangeId - The exchange ID to accept
 * @param {HTMLElement} buttonEl - The button element that was clicked
 */
function acceptExchange(exchangeId, buttonEl) {
    const formData = new FormData();
    formData.append('exchangeId', exchangeId);
    
    // Disable button and show loading state
    if (buttonEl) {
        buttonEl.disabled = true;
        buttonEl.textContent = 'Processing...';
    }
    
    // Use absolute path from site root
    fetch('/Skill-Service_exchanging_website-/dashboard/post/acceptExchange.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Booking accepted successfully! Credits have been transferred.', 'success');
            // Update header credit display if present
            if (typeof data.ownerBalance !== 'undefined' && data.ownerBalance !== null) {
                const bigSpan = document.querySelector('.credit-span');
                const smallSpan = document.querySelector('.credit-span-small');
                if (bigSpan) bigSpan.textContent = String(data.ownerBalance);
                if (smallSpan) smallSpan.textContent = String(data.ownerBalance);
            }
            // Also call global refresh if available
            if (typeof window.refreshCreditsDisplay === 'function') {
                window.refreshCreditsDisplay();
            }
            // Update buttons to show accepted status - find the notification-actions container
            if (buttonEl) {
                const actionsContainer = buttonEl.closest('.notification-actions');
                if (actionsContainer) {
                    // Remove both Accept and Decline buttons, replace with Accepted badge
                    actionsContainer.innerHTML = `
                        <span class="status-badge status-accepted">✓ Accepted</span>
                        <button class="btn-secondary mark-read-btn">Mark as Read</button>
                    `;
                }
            }
            // Reload page after short delay to update notifications list
            setTimeout(() => location.reload(), 1500);
        } else {
            const friendlyError = getUserFriendlyError(data.error || 'Failed to accept booking');
            showToast(friendlyError, 'error');
            // Re-enable button
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.textContent = 'Accept';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong. Please try again later.', 'error');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Accept';
        }
    });
}

/**
 * Send reject request to server
 * @param {number} exchangeId - The exchange ID to reject
 * @param {HTMLElement} buttonEl - The button element that was clicked
 */
function rejectExchange(exchangeId, buttonEl) {
    const formData = new FormData();
    formData.append('exchangeId', exchangeId);
    
    // Disable button and show loading state
    if (buttonEl) {
        buttonEl.disabled = true;
        buttonEl.textContent = 'Processing...';
    }
    
    fetch('/Skill-Service_exchanging_website-/dashboard/post/rejectExchange.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Booking request declined.', 'info');
            // Update buttons to show declined status
            if (buttonEl) {
                const actionsContainer = buttonEl.closest('.notification-actions');
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <span class="status-badge status-declined">✗ Declined</span>
                        <button class="btn-secondary mark-read-btn">Mark as Read</button>
                    `;
                }
            }
            // Reload page after short delay
            setTimeout(() => location.reload(), 1500);
        } else {
            const friendlyError = getUserFriendlyError(data.error || 'Failed to decline booking');
            showToast(friendlyError, 'error');
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.textContent = 'Decline';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong. Please try again later.', 'error');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Decline';
        }
    });
}

/**
 * HTML USAGE IN NOTIFICATION CARDS
 * Add these buttons to booking notifications in the HTML:
 * 
 * For Booking Notifications (NotificationType = 'booking'):
 * 
 * <div class="notification-actions">
 *     <button 
 *         class="action-btn accept-btn" 
 *         data-action="accept-exchange" 
 *         data-exchange-id="<?php echo $exchangeId; ?>"
 *     >
 *         Accept
 *     </button>
 *     <button 
 *         class="action-btn reject-btn" 
 *         data-action="reject-exchange" 
 *         data-exchange-id="<?php echo $exchangeId; ?>"
 *     >
 *         Reject
 *     </button>
 * </div>
 * 
 * CSS STYLING:
 * 
 * .notification-actions {
 *     display: flex;
 *     gap: 10px;
 *     margin-top: 15px;
 * }
 * 
 * .action-btn {
 *     padding: 8px 16px;
 *     border: none;
 *     border-radius: 6px;
 *     cursor: pointer;
 *     font-weight: 600;
 *     font-size: 14px;
 *     transition: all 0.3s ease;
 * }
 * 
 * .accept-btn {
 *     background-color: #28a745;
 *     color: white;
 * }
 * 
 * .accept-btn:hover {
 *     background-color: #218838;
 *     transform: translateY(-2px);
 * }
 * 
 * .reject-btn {
 *     background-color: #dc3545;
 *     color: white;
 * }
 * 
 * .reject-btn:hover {
 *     background-color: #c82333;
 *     transform: translateY(-2px);
 * }
 */

/**
 * Send accept event attendee request to server
 * @param {number} eventId - The event ID
 * @param {number} attendeeId - The attendee user ID to accept
 * @param {HTMLElement} buttonEl - The button element that was clicked
 */
function acceptEventAttendee(eventId, attendeeId, buttonEl) {
    const formData = new FormData();
    formData.append('eventId', eventId);
    formData.append('attendeeId', attendeeId);
    
    // Disable button and show loading state
    if (buttonEl) {
        buttonEl.disabled = true;
        buttonEl.textContent = 'Processing...';
    }
    
    fetch('/Skill-Service_exchanging_website-/dashboard/events/eventsAPI/acceptEventAttendee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Event join request accepted! Credits have been transferred.', 'success');
            // Update header credit display if present
            if (typeof data.organizerBalance !== 'undefined' && data.organizerBalance !== null) {
                const bigSpan = document.querySelector('.credit-span');
                const smallSpan = document.querySelector('.credit-span-small');
                if (bigSpan) bigSpan.textContent = String(data.organizerBalance);
                if (smallSpan) smallSpan.textContent = String(data.organizerBalance);
            }
            // Also call global refresh if available
            if (typeof window.refreshCreditsDisplay === 'function') {
                window.refreshCreditsDisplay();
            }
            // Update buttons to show accepted status - find the notification-actions container
            if (buttonEl) {
                const actionsContainer = buttonEl.closest('.notification-actions');
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <span class="status-badge status-accepted">✓ Accepted</span>
                        <button class="btn-secondary mark-read-btn">Mark as Read</button>
                    `;
                }
            }
            // Reload page after short delay
            setTimeout(() => location.reload(), 1500);
        } else {
            const friendlyError = getUserFriendlyError(data.error || 'Failed to accept join request');
            showToast(friendlyError, 'error');
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.textContent = 'Accept';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong. Please try again later.', 'error');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Accept';
        }
    });
}

/**
 * Send reject event attendee request to server
 * @param {number} eventId - The event ID
 * @param {number} attendeeId - The attendee user ID to reject
 * @param {HTMLElement} buttonEl - The button element that was clicked
 */
function rejectEventAttendee(eventId, attendeeId, buttonEl) {
    const formData = new FormData();
    formData.append('eventId', eventId);
    formData.append('attendeeId', attendeeId);
    
    // Disable button and show loading state
    if (buttonEl) {
        buttonEl.disabled = true;
        buttonEl.textContent = 'Processing...';
    }
    
    fetch('/Skill-Service_exchanging_website-/dashboard/events/eventsAPI/rejectEventAttendee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Event join request declined.', 'info');
            // Update buttons to show declined status
            if (buttonEl) {
                const actionsContainer = buttonEl.closest('.notification-actions');
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <span class="status-badge status-declined">✗ Declined</span>
                        <button class="btn-secondary mark-read-btn">Mark as Read</button>
                    `;
                }
            }
            // Reload page after short delay
            setTimeout(() => location.reload(), 1500);
        } else {
            const friendlyError = getUserFriendlyError(data.error || 'Failed to decline join request');
            showToast(friendlyError, 'error');
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.textContent = 'Decline';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong. Please try again later.', 'error');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Decline';
        }
    });
}
