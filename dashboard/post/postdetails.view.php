<div class="page-wrapper">
    <div class="page-container">
        <div class="service-detail-container">
            <!-- Left Section: Service Details -->
            <div class="service-info-section">
        <!-- Provider Header -->
        <div class="provider-header">
            <img src="<?php echo htmlspecialchars($user['ProfilePicture'] ?? '../../assets/images/Default_pfp.svg'); ?>" alt="<?php echo htmlspecialchars($user['FullName'] ?? 'Unknown'); ?>" class="provider-avatar">
            <div class="provider-info">
                <h3 class="provider-name"><?php echo htmlspecialchars($user['FullName'] ?? 'Unknown'); ?></h3>
                <div class="provider-rating">
                    <span class="stars"><?php echo str_repeat('★', round($user['Rating'] ?? 0)) . str_repeat('☆', 5 - round($user['Rating'] ?? 0)); ?></span>
                    <span class="reviews-count">(<?php echo $user['RatingCount'] ?? 0; ?> reviews)</span>
                </div>
            </div>
            <div class="service-price">
                <span class="price-label">Price</span>
                <span class="price-value"><?php echo ($requiredCredits > 0) ? $requiredCredits . ' credits/hr' : 'Free'; ?></span>
            </div>
        </div>

        <!-- Service Title and Tags -->
        <div class="service-header">
            <h1 class="service-title"><?php echo htmlspecialchars($postTitle ?? ''); ?></h1>
            <div class="service-tags">
                <span class="tag tag-btn"><?php echo htmlspecialchars($postType ?? ''); ?></span>
            </div>
        </div>

        <!-- Service Quick Info -->
        <div class="service-quick-info">
            <div class="info-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <div>
                    <div class="info-label">Duration</div>
                    <div class="info-value"><?php 
                        $duration = $postDuration ?? 0;
                        if ($duration >= 60) {
                            $hours = floor($duration / 60);
                            $mins = $duration % 60;
                            if ($mins > 0) {
                                echo $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $mins . ' min';
                            } else {
                                echo $hours . ' hour' . ($hours > 1 ? 's' : '');
                            }
                        } else {
                            echo $duration . ' minutes';
                        }
                    ?></div>
                </div>
            </div>

            <div class="info-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <div>
                    <div class="info-label">Type</div>
                    <div class="info-value"><?php echo ucfirst($postType ?? ''); ?></div>
                </div>
            </div>

            <div class="info-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <div>
                    <div class="info-label">Location</div>
                    <div class="info-value"><?php echo !empty($postLocation) ? htmlspecialchars($postLocation) : 'N/A'; ?></div>
                </div>
            </div>

            <div class="info-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div>
                    <div class="info-label">Next Available</div>
                    <div class="info-value"><?php echo !empty($postDate) ? date('M d, Y', strtotime($postDate)) : 'Not available'; ?></div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php 
                // Determine button state based on user's exchange status
                $buttonText = 'Book This Service';
                $buttonClass = 'btn-primary';
                $buttonDisabled = false;
                $isLoggedIn = isset($_SESSION['user_id']);
                $isPostOwner = ($isLoggedIn && $_SESSION['user_id'] == $postUserId);
                $canCancel = false; // Flag for allowing request cancellation
                $exchangeId = null;
                
                if (!$isLoggedIn) {
                    // Not logged in - show book button but might need login
                    $buttonText = 'Book This Service';
                    $buttonClass = 'btn-primary';
                } elseif ($isPostOwner) {
                    // User is the post owner
                    $buttonText = 'This is Your Service';
                    $buttonClass = 'btn-disabled';
                    $buttonDisabled = true;
                } elseif (!empty($currentUserExchange)) {
                    // User has an existing exchange
                    $status = $currentUserExchange['Status'];
                    $exchangeId = $currentUserExchange['ExchangeId'];
                    
                    if ($status === 'pending') {
                        // Allow cancellation for pending requests
                        $buttonText = 'Cancel Request';
                        $buttonClass = 'btn-cancel';
                        $buttonDisabled = false;
                        $canCancel = true;
                    } else {
                        $buttonState = getButtonState($status);
                        if ($buttonState) {
                            $buttonText = $buttonState['text'];
                            $buttonClass = 'btn-primary ' . $buttonState['class'];
                            $buttonDisabled = $buttonState['disabled'];
                        }
                    }
                }
            ?>
            <button 
                id="bookServiceBtn"
                class="<?php echo $buttonClass; ?>" 
                onclick="<?php echo $buttonDisabled ? 'return false;' : ($canCancel ? 'cancelRequest();' : 'bookService();'); ?>"
                <?php echo $buttonDisabled ? 'disabled' : ''; ?>
                data-exchange-id="<?php echo $exchangeId ?? ''; ?>"
                data-can-cancel="<?php echo $canCancel ? 'true' : 'false'; ?>"
            >
                <?php echo $buttonText; ?>
            </button>
        </div>

        <!-- Service Description -->
        <div class="service-description">
            <h2>Service Description</h2>
            <p><?php echo nl2br(htmlspecialchars($postDescription ?? '')); ?></p>

            <h3>Prerequisites</h3>
            <p><?php echo !empty($prerequisites) ? nl2br(htmlspecialchars($prerequisites)) : 'No prerequisites specified.'; ?></p>
        </div>

        <!-- Skills & Expertise -->
        <div class="skills-section">
            <h2>Skills & Expertise</h2>
            
            <h3>Skills Offered</h3>
            <div class="skills-tags">
                <?php if (!empty($offeredSkills)): ?>
                    <?php foreach ($offeredSkills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="skill-tag no-skill">No skills specified</span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($requestedSkills)): ?>
            <h3 style="margin-top: 1rem;">Skills Seeking in Return</h3>
            <div class="seeking-tags">
                <?php foreach ($requestedSkills as $skill): ?>
                    <span class="seeking-tag"><?php echo htmlspecialchars($skill); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

            <!-- Payment & Exchange Details -->
            <div class="exchange-section">
                <h2>Payment Details</h2>
                
                <div class="payment-options">
                    <form class="payment-form">
                        <div class="payment-badges">
                            <?php if ($requiredCredits > 0): ?>
                            <label class="payment-badge <?php echo ($paymentMethod !== 'exchange') ? 'active' : ''; ?>">
                                <input type="radio" name="paymentMethod" value="credits" <?php echo ($paymentMethod !== 'exchange') ? 'checked' : ''; ?> onchange="selectPaymentMethod('credits', this.closest('label'))">
                                <span><?php echo $requiredCredits; ?> Credits</span>
                            </label>
                            <?php endif; ?>
                            <label class="payment-badge <?php echo ($requiredCredits == 0 || $paymentMethod === 'exchange') ? 'active' : ''; ?>">
                                <input type="radio" name="paymentMethod" value="exchange" <?php echo ($requiredCredits == 0 || $paymentMethod === 'exchange') ? 'checked' : ''; ?> onchange="selectPaymentMethod('exchange', this.closest('label'))">
                                <span>Skill Exchange</span>
                            </label>
                        </div>
                    </form>
                </div>

                <?php if (empty($requestedSkills) && ($paymentMethod === 'exchange' || $requiredCredits == 0)): ?>
                <div class="seeking-skills">
                    <p class="no-skills">Open to various skill exchanges.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

            <!-- Right Section: Availability Schedule -->
            <div class="availability-section">
                <div class="availability-card">
                    <div class="availability-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <div>
                            <h2>Availability Schedule</h2>
                            <p>Select an available date and time for this service.</p>
                        </div>
                    </div>

                    <!-- Status Message Container -->
                    <div id="statusMessage" class="status-message" style="display: none;"></div>
                    
                    <!-- PHP Error Messages -->
                    <?php if (!empty($error)): ?>
                        <div class="status-message status-error" style="display: block; background-color: #f8d7da !important; color: #721c24 !important; border: 1px solid #f5c6cb !important; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="availability-form">
                        <!-- Payment Method Selection -->
                        <input type="hidden" id="selectedPaymentMethod" name="selectedPaymentMethod" value="<?php echo ($requiredCredits == 0) ? 'exchange' : (($paymentMethod === 'exchange') ? 'exchange' : 'credits'); ?>">
                        
                        <!-- Days Schedule -->
                        <div class="schedule-container">
                            <?php if (!empty($weekDays) && is_array($weekDays)): ?>
                                <?php foreach ($weekDays as $day): ?>
                                    <div class="schedule-day" data-day="<?php echo strtolower($day); ?>">
                                        <div class="day-header">
                                            <span class="day-label"><?php echo $day; ?></span>
                                        </div>
                                        <div class="time-slots">
                                            <?php if (!empty($dayRanges[$day]['has_slots'])): 
                                                $range = $dayRanges[$day];
                                                // Create a sample datetime for this day's first available slot
                                                // We need to find an actual date from $dates array for this day
                                                $sampleDate = '';
                                                foreach ($dates as $datetime) {
                                                    if (date('l', strtotime($datetime)) === $day) {
                                                        $sampleDate = date('Y-m-d', strtotime($datetime)) . ' ' . $range['start'] . ':00';
                                                        break;
                                                    }
                                                }
                                                
                                                // Check if this day's slots are booked
                                                $isDayBooked = false;
                                                foreach ($bookedDates as $booked) {
                                                    if (date('l', strtotime($booked)) === $day) {
                                                        $isDayBooked = true;
                                                        break;
                                                    }
                                                }
                                                
                                                $isPast = !empty($sampleDate) && strtotime($sampleDate) < time();
                                                $isDisabled = $isDayBooked || $isPast;
                                            ?>
                                                <label class="time-slot-range <?php echo $isDisabled ? 'disabled' : ''; ?>">
                                                    <input 
                                                        type="radio" 
                                                        name="SelectedDate" 
                                                        value="<?php echo htmlspecialchars($sampleDate); ?>" 
                                                        <?php echo $isDisabled ? 'disabled' : ''; ?>
                                                        data-day="<?php echo $day; ?>"
                                                    >
                                                    <span class="time-range">
                                                        <strong><?php echo htmlspecialchars($range['start']); ?></strong> 
                                                        to 
                                                        <strong><?php echo htmlspecialchars($range['end']); ?></strong>
                                                    </span>
                                                </label>
                                            <?php else: ?>
                                                <span class="no-slots">No available slots</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No availability information available.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hidden submit button -->
                        <input type="submit" id="submitBooking" style="display: none;">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showStatusMessage(message, type = 'success') {
    const statusMsg = document.getElementById('statusMessage');
    statusMsg.textContent = message;
    statusMsg.className = 'status-message status-' + type;
    statusMsg.style.display = 'block';
    
    // Force colors with inline styles to override cache
    if (type === 'success' || type === 'info') {
        statusMsg.style.backgroundColor = '#d4edda';
        statusMsg.style.color = '#155724';
        statusMsg.style.border = '1px solid #c3e6cb';
    } else if (type === 'error') {
        statusMsg.style.backgroundColor = '#f8d7da';
        statusMsg.style.color = '#721c24';
        statusMsg.style.border = '1px solid #f5c6cb';
    }
    statusMsg.style.padding = '12px 15px';
    statusMsg.style.borderRadius = '8px';
    statusMsg.style.marginBottom = '15px';
    statusMsg.style.fontSize = '0.95rem';
    statusMsg.style.fontWeight = '500';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        statusMsg.style.display = 'none';
    }, 3000);
}

// Check for booking success message
<?php 
if (isset($_SESSION['booking_success']) && $_SESSION['booking_success'] === true): 
    unset($_SESSION['booking_success']); // Clear the flag
?>
window.addEventListener('DOMContentLoaded', () => {
    showStatusMessage('Your booking request has been submitted!', 'info');
});
<?php endif; ?>

function bookService() {
    const selectedSlot = document.querySelector('.time-slot-range.selected input[type="radio"]');
    if (!selectedSlot) {
        showStatusMessage('Please select a time slot from the availability schedule first.', 'error');
        return;
    }
    
    if (selectedSlot.disabled) {
        showStatusMessage('This time slot is not available for booking.', 'error');
        return;
    }
    
    // Debug: Log payment method being submitted
    const paymentMethodInput = document.getElementById('selectedPaymentMethod');
    const paymentValue = paymentMethodInput ? paymentMethodInput.value : 'NOT FOUND';
    console.log('=== BOOKING SUBMISSION DEBUG ===');
    console.log('Payment method input found:', paymentMethodInput !== null);
    console.log('Payment method value:', paymentValue);
    console.log('Selected date:', selectedSlot.value);
    console.log('Selected date input name:', selectedSlot.name);
    console.log('Form will submit now...');
    
    // Make sure the radio is checked before submission
    selectedSlot.checked = true;
    
    // Submit the form directly instead of clicking hidden button
    const form = document.querySelector('.availability-form');
    if (form) {
        console.log('Submitting form...');
        form.submit();
    } else {
        console.error('Form not found!');
        showStatusMessage('Error: Form not found', 'error');
    }
}

function cancelRequest() {
    const btn = document.getElementById('bookServiceBtn');
    const exchangeId = btn.dataset.exchangeId;
    
    if (!exchangeId) {
        showStatusMessage('No request to cancel.', 'error');
        return;
    }
    
    // Send AJAX request to cancel the exchange
    const formData = new FormData();
    formData.append('exchangeId', exchangeId);
    formData.append('action', 'cancel');
    
    fetch('cancelExchange.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showStatusMessage('Request has been cancelled successfully!', 'info');
            // Update button state to 'Book This Service'
            btn.textContent = 'Book This Service';
            btn.className = 'btn-primary';
            btn.dataset.canCancel = 'false';
            btn.dataset.exchangeId = '';
            btn.disabled = false;
            btn.onclick = function() { bookService(); };
        } else {
            showStatusMessage('Error cancelling request: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showStatusMessage('An error occurred while cancelling the request.', 'error');
    });
}

// Add click handlers for time slots
document.addEventListener('DOMContentLoaded', function() {
    const timeSlots = document.querySelectorAll('.time-slot-range:not(.disabled)');
    
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            // Remove selected class from all slots
            document.querySelectorAll('.time-slot-range').forEach(s => {
                s.classList.remove('selected');
            });
            
            // Add selected class to clicked slot
            this.classList.add('selected');
        });
    });
});
</script>
<script>
    // Initialize payment method based on post requirements
    let selectedPaymentMethod = <?php echo ($requiredCredits == 0) ? "'exchange'" : (($paymentMethod === 'exchange') ? "'exchange'" : "'credits'"); ?>;
    
    // Set the hidden input on page load
    document.addEventListener('DOMContentLoaded', function() {
        const hiddenInput = document.getElementById('selectedPaymentMethod');
        if (hiddenInput) {
            hiddenInput.value = selectedPaymentMethod;
            console.log('Initial payment method set to:', selectedPaymentMethod);
        }
    });
    
    function selectPaymentMethod(method, labelElement) {
        selectedPaymentMethod = method;
        console.log('Payment method changed to:', method);
        
        // Update all hidden inputs with this name
        document.querySelectorAll('[name="selectedPaymentMethod"], #selectedPaymentMethod').forEach(input => {
            input.value = method;
            console.log('Updated input:', input.id || input.name, '=', input.value);
        });
        
        // Update label styling
        document.querySelectorAll('.payment-badge').forEach(label => {
            label.classList.remove('active');
        });
        if (labelElement) {
            labelElement.classList.add('active');
        }
    }
</script>