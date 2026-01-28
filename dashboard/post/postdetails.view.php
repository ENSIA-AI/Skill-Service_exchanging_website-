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
                <span class="price-value"><?php echo $requiredCredits ?? 0; ?> credits/hr</span>
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
                    <div class="info-value"><?php echo $postDuration ?? 0; ?> hours</div>
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
                    <div class="info-value"><?php echo htmlspecialchars($postLocation ?? 'N/A'); ?></div>
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
            <button class="btn-primary">Book This Service</button>
        </div>

        <!-- Service Description -->
        <div class="service-description">
            <h2>Service Description</h2>
            <p><?php echo nl2br(htmlspecialchars($postDescription ?? '')); ?></p>

            <h3>Prerequisites</h3>
            <p><?php echo nl2br(htmlspecialchars($prerequisites ?? 'No prerequisites specified.')); ?></p>
        </div>

        <!-- Skills & Expertise -->
        <div class="skills-section">
            <h2>Skills & Expertise</h2>
            <div class="skills-tags">
                    <?php if (!empty($postSkills)): ?>
                        <?php foreach ($postSkills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="skill-tag">No skills specified</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- What I'm Looking For in Exchange -->
            <div class="exchange-section">
            <h2>What I'm Looking For in Exchange</h2>
            
            <div class="payment-options">
                <h3>Payment Options</h3>
                <div class="payment-badges">
                    <span class="payment-badge active"><?php echo $requiredCredits ?? 0; ?> Credits</span>
                    <span class="payment-badge"><?php echo ucfirst($paymentMethod ?? ''); ?></span>
                </div>
            </div>

            <div class="seeking-skills">
                <h3>Skills I'm Seeking</h3>
                <p>If you prefer skill exchange instead of credits, I'm interested in learning:</p>
                <div class="seeking-tags">
                    <?php if (!empty($seekingSkills)): ?>
                        <?php foreach ($seekingSkills as $skill): ?>
                            <span class="seeking-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="seeking-tag">Not specified</span>
                    <?php endif; ?>
                </div>
            </div>
            </div>

            <!-- What You'll Need -->
        <div class="requirements-section">
            <h2>What You'll Need</h2>
            <p><?php echo nl2br(htmlspecialchars($requirements ?? 'No specific requirements.')); ?></p>
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

                    <?php if (!empty($error)): ?>
                        <div class="error-message" style="color: red; margin-bottom: 10px; padding: 10px; background: #ffe6e6; border-radius: 4px; border: 1px solid #ffcccc;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="success-message" style="color: green; margin-bottom: 10px; padding: 10px; background: #e6ffe6; border-radius: 4px; border: 1px solid #ccffcc;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="availability-form">
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
function bookService() {
    const selectedSlot = document.querySelector('.time-slot-range.selected input[type="radio"]');
    if (!selectedSlot) {
        alert('Please select a time slot from the availability schedule first.');
        return;
    }
    
    if (selectedSlot.disabled) {
        alert('This time slot is not available for booking.');
        return;
    }
    
    // Submit the form
    document.getElementById('submitBooking').click();
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