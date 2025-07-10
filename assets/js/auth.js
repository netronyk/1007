// File Path: assets/js/auth.js

// Password toggle functionality
function togglePassword(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation for Israeli numbers
function isValidPhone(phone) {
    const phoneRegex = /^[0-9\-\+\(\)\ ]{9,15}$/;
    return phoneRegex.test(phone);
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 6) strength += 1;
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    switch(strength) {
        case 0:
        case 1:
            return { level: 'weak', text: 'סיסמה חלשה', class: 'strength-weak' };
        case 2:
        case 3:
            return { level: 'fair', text: 'סיסמה בינונית', class: 'strength-fair' };
        case 4:
        case 5:
            return { level: 'good', text: 'סיסמה חזקה', class: 'strength-good' };
        case 6:
            return { level: 'strong', text: 'סיסמה מעולה', class: 'strength-strong' };
        default:
            return { level: 'weak', text: 'סיסמה חלשה', class: 'strength-weak' };
    }
}

// Update password strength indicator
function updatePasswordStrength(password, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const strength = checkPasswordStrength(password);
    const strengthBar = container.querySelector('.strength-bar');
    const strengthText = container.querySelector('.strength-text');
    
    if (strengthBar) {
        strengthBar.className = 'strength-bar ' + strength.class;
    }
    
    if (strengthText) {
        strengthText.textContent = strength.text;
        strengthText.className = 'strength-text ' + strength.class;
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
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
            return;
        }
        
        // Email validation
        if (field.type === 'email' && !isValidEmail(value)) {
            field.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = field;
            return;
        }
        
        // Phone validation
        if (field.type === 'tel' && !isValidPhone(value)) {
            field.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = field;
            return;
        }
        
        // Password validation
        if (field.type === 'password' && field.id === 'password' && value.length < 6) {
            field.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = field;
            return;
        }
        
        // Confirm password validation
        if (field.id === 'confirm_password') {
            const passwordField = document.getElementById('password');
            if (passwordField && value !== passwordField.value) {
                field.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = field;
                return;
            }
        }
        
        // Add valid styling
        field.classList.add('is-valid');
    });
    
    // Focus on first invalid field
    if (firstInvalidField) {
        firstInvalidField.focus();
    }
    
    return isValid;
}

// Show loading state on form submission
function showLoadingState(buttonElement, originalText) {
    buttonElement.disabled = true;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>טוען...';
    
    // Store original text for restoration
    buttonElement.setAttribute('data-original-text', originalText);
}

// Hide loading state
function hideLoadingState(buttonElement) {
    const originalText = buttonElement.getAttribute('data-original-text');
    buttonElement.disabled = false;
    buttonElement.innerHTML = originalText;
}

// Initialize auth page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add floating animation to shapes
    const shapes = document.querySelectorAll('.shape');
    shapes.forEach((shape, index) => {
        shape.style.animationDelay = (index * 1.5) + 's';
    });
    
    // Auto-focus first input field
    const firstInput = document.querySelector('input[type="email"], input[type="text"]');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Add real-time validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (this.value) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters except +, -, (, ), space
            this.value = this.value.replace(/[^\d\+\-\(\)\ ]/g, '');
        });
        
        input.addEventListener('blur', function() {
            if (this.value && !isValidPhone(this.value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (this.value) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Password strength checking
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value, 'passwordStrength');
        });
    }
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (this.value) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    }
    
    // Form submission handling
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (!validateForm(this.id)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            if (submitButton) {
                showLoadingState(submitButton, submitButton.innerHTML);
            }
            
            // Form will submit normally
        });
    });
    
    // Clear validation classes on input
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
        });
    });
});

// Show toast notification
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}