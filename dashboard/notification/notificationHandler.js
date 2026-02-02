/**
 * Notification Interaction Handler
 * Add this JavaScript to notifications.html or notifications.php to handle accept/reject buttons
 * 
 * This code should be added to the notifications page to enable accept/reject functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Handle Accept Button Click
     * Sends exchange ID to acceptExchange.php
     */
    const acceptButtons = document.querySelectorAll('[data-action="accept-exchange"]');
    acceptButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const exchangeId = this.getAttribute('data-exchange-id');
            
            if (!exchangeId) {
                alert('Error: Exchange ID not found');
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Accept this exchange request?')) {
                acceptExchange(exchangeId);
            }
        });
    });
    
    /**
     * Handle Reject Button Click
     * Sends exchange ID to rejectExchange.php
     */
    const rejectButtons = document.querySelectorAll('[data-action="reject-exchange"]');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const exchangeId = this.getAttribute('data-exchange-id');
            
            if (!exchangeId) {
                alert('Error: Exchange ID not found');
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Reject this exchange request?')) {
                rejectExchange(exchangeId);
            }
        });
    });
});

/**
 * Send accept request to server
 * @param {number} exchangeId - The exchange ID to accept
 */
function acceptExchange(exchangeId) {
    const formData = new FormData();
    formData.append('exchangeId', exchangeId);
    
    fetch('../post/acceptExchange.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Exchange accepted successfully!');
            // Reload the page to see updated notifications
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to accept exchange'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request');
    });
}

/**
 * Send reject request to server
 * @param {number} exchangeId - The exchange ID to reject
 */
function rejectExchange(exchangeId) {
    const formData = new FormData();
    formData.append('exchangeId', exchangeId);
    
    fetch('../post/rejectExchange.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Exchange rejected successfully!');
            // Reload the page to see updated notifications
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to reject exchange'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request');
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
