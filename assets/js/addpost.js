// Form validation script
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('post-form');
    const description = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    const addSkillBtn = document.querySelector('.add-skill');
    const skillsContainer = document.getElementById('skills-container');
    const skillCategory = document.getElementById('skill-category');
    const skillSelect = document.getElementById('skill-select');

    let skillCount = 0;
    const maxSkills = 5;

    // Add character count for description
    description.addEventListener('input', function () {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/500 characters`;

        if (currentLength > 500) {
            charCount.style.color = 'red';
        } else {
            charCount.style.color = '';
        }
    });

    // Fetch skills when category changes
    skillCategory.addEventListener('change', function () {
        const categoryId = this.value;
        skillSelect.disabled = true;
        skillSelect.innerHTML = '<option value="">Loading...</option>';

        if (!categoryId) {
            skillSelect.innerHTML = '<option value="">Select a category first</option>';
            return;
        }

        fetch(`../events/eventsAPI/getSkills.php?categoryid=${categoryId}`)
            .then(response => response.json())
            .then(skills => {
                skillSelect.innerHTML = '<option value="">Select a skill</option>';
                skills.forEach(skill => {
                    const option = document.createElement('option');
                    option.value = skill.skillid;
                    option.textContent = skill.skillname;
                    skillSelect.appendChild(option);
                });
                skillSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching skills:', error);
                skillSelect.innerHTML = '<option value="">Error loading skills</option>';
            });
    });

    // Add skill functionality
    addSkillBtn.addEventListener('click', function () {
        if (skillCount >= maxSkills) {
            alert('You can only add up to 5 skills');
            return;
        }

        const skillId = skillSelect.value;
        const skillName = skillSelect.options[skillSelect.selectedIndex].text;

        if (!skillId) {
            alert('Please select a skill first');
            return;
        }

        // Avoid duplicate skills
        const existingSkills = skillsContainer.querySelectorAll('input[name="skills[]"]');
        for (let input of existingSkills) {
            if (input.value === skillId) {
                alert('This skill has already been added');
                return;
            }
        }

        const skillDiv = document.createElement('div');
        skillDiv.className = 'skill-tag';
        skillDiv.innerHTML = `
            <span>${skillName}</span>
            <input type="hidden" name="skills[]" value="${skillId}">
            <button type="button" class="remove-skill">×</button>
        `;

        skillsContainer.appendChild(skillDiv);
        skillCount++;

        // Add event listener to remove button
        skillDiv.querySelector('.remove-skill').addEventListener('click', function () {
            skillsContainer.removeChild(skillDiv);
            skillCount--;
        });
    });

    // Form validation
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous error messages
        clearErrors();

        // Validate each field
        const isValid = validateForm();

        if (isValid) {
            const formData = new FormData(form);

            // Collect skills separately if needed, but the hidden inputs in skillsContainer should be included in FormData

            fetch('process_addpost.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Post created successfully!');
                        window.location.href = 'posts.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    alert('An error occurred while creating the post. Please try again.');
                });
        }
    });

    // Real-time validation for required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function () {
            validateField(this);
        });
    });

    // Validate individual field
    function validateField(field) {
        const fieldId = field.id;
        const errorElement = document.getElementById(`${fieldId}-error`);

        // Clear previous error
        errorElement.textContent = '';
        field.classList.remove('error');

        // Check if field is empty
        if (field.value.trim() === '') {
            showError(field, errorElement, 'This field is required');
            return false;
        }

        // Field-specific validation
        switch (fieldId) {
            case 'title':
                if (field.value.length < 5) {
                    showError(field, errorElement, 'Title must be at least 5 characters long');
                    return false;
                }
                break;

            case 'description':
                if (field.value.length < 20) {
                    showError(field, errorElement, 'Description must be at least 20 characters long');
                    return false;
                }
                break;

            case 'credits':
                const credits = parseInt(field.value);
                if (isNaN(credits) || credits < 1 || credits > 1000) {
                    showError(field, errorElement, 'Credits must be between 1 and 1000');
                    return false;
                }
                break;

            case 'exchange':
                if (field.value.length < 3) {
                    showError(field, errorElement, 'Exchange description must be at least 3 characters long');
                    return false;
                }
                break;
        }

        return true;
    }

    // Validate entire form
    function validateForm() {
        let isValid = true;

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        // Validate delivery options
        const onlineCheckbox = document.getElementById('online');
        const inpersonCheckbox = document.getElementById('inperson');
        const deliveryError = document.getElementById('delivery-error');

        if (!onlineCheckbox.checked && !inpersonCheckbox.checked) {
            deliveryError.textContent = 'Please select at least one delivery option';
            isValid = false;
        }

        // Validate skills (optional but with limit)
        if (skillCount > maxSkills) {
            document.getElementById('skills-error').textContent = `You can only add up to ${maxSkills} skills`;
            isValid = false;
        }

        return isValid;
    }

    // Show error message
    function showError(field, errorElement, message) {
        errorElement.textContent = message;
        field.classList.add('error');
    }

    // Clear all error messages
    function clearErrors() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(error => {
            error.textContent = '';
        });

        const errorFields = document.querySelectorAll('.error');
        errorFields.forEach(field => {
            field.classList.remove('error');
        });
    }

    // Cancel button functionality
    document.querySelector('.cancel').addEventListener('click', function () {
        if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
            window.history.back();
        }
    });
});