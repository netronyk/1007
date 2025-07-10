// File Path: assets/js/register.js

// Current step tracking
let currentStep = 1;
const totalSteps = 3;

// Step navigation
function nextStep(step) {
    if (!validateCurrentStep(step)) {
        return false;
    }
    
    if (step < totalSteps) {
        // Hide current step
        document.getElementById('step' + step).classList.remove('active');
        
        // Show next step
        document.getElementById('step' + (step + 1)).classList.add('active');
        
        // Update step indicator
        updateStepIndicator(step + 1);
        
        currentStep = step + 1;
        
        // Focus on first input of next step
        const nextStepElement = document.getElementById('step' + (step + 1));
        const firstInput = nextStepElement.querySelector('input, select');
        if (firstInput) {
            firstInput.focus();
        }
    }
}

function prevStep(step) {
    if (step > 1) {
        // Hide current step
        document.getElementById('step' + step).classList.remove('active');
        
        // Show previous step
        document.getElementById('step' + (step - 1)).classList.add('active');
        
        // Update step indicator
        updateStepIndicator(step - 1);
        
        currentStep = step - 1;
        
        // Focus on first input of previous step
        const prevStepElement = document.getElementById('step' + (step - 1));
        const firstInput = prevStepElement.querySelector('input, select');
        if (firstInput) {
            firstInput.focus();
        }
    }
}

// Update step indicator
function updateStepIndicator(activeStep) {
    for (let i = 1; i <= totalSteps; i++) {
        const stepElement = document.getElementById('step-indicator-' + i);
        const lineElement = document.getElementById('line-' + i);
        
        if (stepElement) {
            stepElement.classList.remove('active', 'completed');
            
            if (i < activeStep) {
                stepElement.classList.add('completed');
            } else if (i === activeStep) {
                stepElement.classList.add('active');
            }
        }
        
        if (lineElement) {
            lineElement.classList.remove('completed');
            if (i < activeStep) {
                lineElement.classList.add('completed');
            }
        }
    }
}

// Validate current step
function validateCurrentStep(step) {
    const stepElement = document.getElementById('step' + step);
    if (!stepElement) return false;
    
    const requiredFields = stepElement.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        
        // Remove previous error styling
        field.classList.remove('is-invalid');
        
        // Check if field is empty
        if (!value) {
            field.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = field;
            showFieldError(field, 'שדה זה הוא חובה');
            return;
        }
        
        // Step-specific validations
        switch(step) {
            case 1:
                // Personal info validation
                if (field.type === 'email' && !isValidEmail(value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                    showFieldError(field, 'כתובת אימייל לא תקינה');
                    return;
                }
                
                if (field.type === 'tel' && !isValidPhone(value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                    showFieldError(field, 'מספר טלפון לא תקין');
                    return;
                }
                
                if ((field.id === 'first_name' || field.id === 'last_name') && value.length < 2) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                    showFieldError(field, 'שם חייב להכיל לפחות 2 תווים');
                    return;
                }
                break;
                
            case 3:
                // Password validation
                if (field.id === 'password' && value.length < 6) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                    showFieldError(field, 'סיסמה חייבת להכיל לפחות 6 תווים');
                    return;
                }
                
                if (field.id === 'confirm_password') {
                    const passwordField = document.getElementById('password');
                    if (passwordField && value !== passwordField.value) {
                        field.classList.add('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = field;
                        showFieldError(field, 'אישור סיסמה לא תואם');
                        return;
                    }
                }
                break;
        }
        
        // Add valid styling
        field.classList.add('is-valid');
        hideFieldError(field);
    });
    
    // Check checkboxes in step 3
    if (step === 3) {
        const termsCheckbox = document.getElementById('terms_agreed');
        if (termsCheckbox && !termsCheckbox.checked) {
            termsCheckbox.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = termsCheckbox;
            showToast('יש לאשר את תנאי השימוש', 'danger');
        }
    }
    
    // Focus on first invalid field
    if (firstInvalidField) {
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    // Remove existing error message
    hideFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    errorDiv.id = field.id + '_error';
    
    field.parentNode.appendChild(errorDiv);
}

// Hide field error
function hideFieldError(field) {
    const existingError = document.getElementById(field.id + '_error');
    if (existingError) {
        existingError.remove();
    }
}

// Check if email exists (AJAX)
function checkEmailExists(email) {
    return fetch('ajax/check-email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error checking email:', error);
        return { exists: false };
    });
}

// Check if phone exists (AJAX)
function checkPhoneExists(phone) {
    return fetch('ajax/check-phone.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error checking phone:', error);
        return { exists: false };
    });
}

// Initialize register page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize step indicator
    updateStepIndicator(1);
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const activeStep = document.querySelector('.form-section.active');
            if (activeStep) {
                const nextButton = activeStep.querySelector('.btn-next');
                const submitButton = activeStep.querySelector('button[type="submit"]');
                
                if (nextButton) {
                    nextButton.click();
                } else if (submitButton) {
                    submitButton.click();
                }
            }
        }
    });
    
    // Real-time email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        let emailCheckTimeout;
        
        emailInput.addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value.trim();
            
            if (email && isValidEmail(email)) {
                emailCheckTimeout = setTimeout(async () => {
                    const result = await checkEmailExists(email);
                    if (result.exists) {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                        showFieldError(this, 'כתובת אימייל זו כבר קיימת במערכת');
                    } else {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                        hideFieldError(this);
                    }
                }, 500);
            }
        });
    }
    
    // Real-time phone validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        let phoneCheckTimeout;
        
        phoneInput.addEventListener('input', function() {
            // Format phone number as user types
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.startsWith('972')) {
                    // International format
                    value = '+972-' + value.substring(3);
                } else if (value.startsWith('0')) {
                    // Local format
                    if (value.length >= 3) {
                        value = value.substring(0, 3) + '-' + value.substring(3);
                    }
                    if (value.length >= 8) {
                        value = value.substring(0, 7) + '-' + value.substring(7);
                    }
                }
            }
            this.value = value;
            
            clearTimeout(phoneCheckTimeout);
            const phone = this.value.trim();
            
            if (phone && isValidPhone(phone)) {
                phoneCheckTimeout = setTimeout(async () => {
                    const result = await checkPhoneExists(phone);
                    if (result.exists) {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                        showFieldError(this, 'מספר טלפון זה כבר קיים במערכת');
                    } else {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                        hideFieldError(this);
                    }
                }, 500);
            }
        });
    }
    
    // Date of birth validation
    const dobInput = document.getElementById('date_of_birth');
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - selectedDate.getFullYear();
            
            if (age < 13) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                showFieldError(this, 'גיל מינימלי להרשמה הוא 13 שנים');
            } else if (age > 120) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                showFieldError(this, 'תאריך לידה לא תקין');
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
                hideFieldError(this);
            }
        });
    }
    
    // Progress tracking
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });
});

// Update form progress
function updateProgress() {
    const totalRequired = document.querySelectorAll('input[required], select[required]').length;
    const completed = document.querySelectorAll('input[required].is-valid, select[required].is-valid').length;
    const percentage = Math.round((completed / totalRequired) * 100);
    
    // Update progress bar if exists
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage + '%';
    }
}