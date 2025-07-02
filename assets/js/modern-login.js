// Modern Login Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeFormToggle();
    initializePasswordValidation();
    initializePasswordToggle();
    initializeFormSubmission();
});

// Form Toggle Functionality
function initializeFormToggle() {
    const loginToggle = document.getElementById('loginToggle');
    const signupToggle = document.getElementById('signupToggle');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    loginToggle.addEventListener('click', () => {
        switchToLogin();
    });

    signupToggle.addEventListener('click', () => {
        switchToSignup();
    });

    function switchToLogin() {
        loginToggle.classList.add('active');
        signupToggle.classList.remove('active');
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
        
        // Clear any error messages
        clearMessages();
    }

    function switchToSignup() {
        signupToggle.classList.add('active');
        loginToggle.classList.remove('active');
        signupForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
        
        // Clear any error messages
        clearMessages();
    }

    // Auto-switch to signup if there are signup errors
    const errorMessages = document.querySelectorAll('.message.error');
    const urlParams = new URLSearchParams(window.location.search);
    if (errorMessages.length > 0 && !urlParams.get('login')) {
        // Check if error is related to signup
        const errorText = errorMessages[0].textContent.toLowerCase();
        if (errorText.includes('password') || errorText.includes('email already') || errorText.includes('match')) {
            switchToSignup();
        }
    }
}

// Password Validation
function initializePasswordValidation() {
    const signupPassword = document.getElementById('signup-password');
    const confirmPassword = document.getElementById('confirm-password');
    
    if (signupPassword) {
        signupPassword.addEventListener('input', validatePassword);
        signupPassword.addEventListener('input', updatePasswordStrength);
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', validatePasswordMatch);
    }
}

function validatePassword() {
    const password = document.getElementById('signup-password').value;
    const requirements = {
        length: password.length >= 6,
        uppercase: /[A-Z]/.test(password),
        number: /\d/.test(password),
        special: /[@$!%*?&]/.test(password)
    };

    // Update requirement indicators
    updateRequirement('length-req', requirements.length);
    updateRequirement('uppercase-req', requirements.uppercase);
    updateRequirement('number-req', requirements.number);
    updateRequirement('special-req', requirements.special);

    return Object.values(requirements).every(req => req);
}

function updateRequirement(elementId, isValid) {
    const element = document.getElementById(elementId);
    if (element) {
        const icon = element.querySelector('i');
        if (isValid) {
            element.classList.add('valid');
            icon.className = 'fas fa-check';
        } else {
            element.classList.remove('valid');
            icon.className = 'fas fa-times';
        }
    }
}

function updatePasswordStrength() {
    const password = document.getElementById('signup-password').value;
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    if (!strengthFill || !strengthText) return;

    let strength = 0;
    let strengthLabel = 'Weak';
    let strengthColor = '#dc2626';

    // Calculate strength
    if (password.length >= 6) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/\d/.test(password)) strength += 25;
    if (/[@$!%*?&]/.test(password)) strength += 25;

    // Determine label and color
    if (strength >= 75) {
        strengthLabel = 'Strong';
        strengthColor = '#16a34a';
    } else if (strength >= 50) {
        strengthLabel = 'Medium';
        strengthColor = '#ea580c';
    } else if (strength >= 25) {
        strengthLabel = 'Fair';
        strengthColor = '#eab308';
    }

    // Update UI
    strengthFill.style.width = strength + '%';
    strengthFill.style.background = strengthColor;
    strengthText.textContent = `Password strength: ${strengthLabel}`;
    strengthText.style.color = strengthColor;
}

function validatePasswordMatch() {
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const confirmInput = document.getElementById('confirm-password');

    if (confirmPassword && password !== confirmPassword) {
        confirmInput.style.borderColor = '#dc2626';
        return false;
    } else if (confirmPassword) {
        confirmInput.style.borderColor = '#16a34a';
        return true;
    }
    return true;
}

// Password Toggle Functionality
function initializePasswordToggle() {
    const toggleButtons = document.querySelectorAll('.password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
}

// Global password toggle function for backward compatibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Form Submission
function initializeFormSubmission() {
    const loginForm = document.querySelector('#loginForm form');
    const signupForm = document.querySelector('#signupForm form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            if (!validateSignupForm()) {
                e.preventDefault();
                return false;
            }
            
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }
}

// Signup Form Validation
function validateSignupForm() {
    const email = document.getElementById('signup-email').value;
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    // Clear previous error messages
    clearMessages();

    // Email validation
    if (!email || !isValidEmail(email)) {
        showError('Please enter a valid email address.');
        return false;
    }

    // Password validation
    if (!validatePassword()) {
        showError('Password does not meet the requirements.');
        return false;
    }

    // Password match validation
    if (password !== confirmPassword) {
        showError('Passwords do not match.');
        return false;
    }

    return true;
}

// Utility Functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showError(message) {
    const activeForm = document.querySelector('.form-wrapper:not(.hidden)');
    const existingMessage = activeForm.querySelector('.message');
    
    if (existingMessage) {
        existingMessage.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'message error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i>${message}`;
    
    const form = activeForm.querySelector('.auth-form');
    const firstInputGroup = form.querySelector('.input-group');
    form.insertBefore(errorDiv, firstInputGroup);
}

function clearMessages() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        if (!message.textContent.includes('successfully') && !message.textContent.includes('sent')) {
            message.remove();
        }
    });
}

// Auto-hide success messages
setTimeout(() => {
    const successMessages = document.querySelectorAll('.message.success');
    successMessages.forEach(message => {
        message.style.transition = 'opacity 0.5s ease';
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 500);
    });
}, 5000);

// Smooth animations for form elements
function addFocusAnimations() {
    const inputs = document.querySelectorAll('input');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
}

// Initialize focus animations
addFocusAnimations();

// Handle browser back/forward navigation
window.addEventListener('popstate', function() {
    clearMessages();
});