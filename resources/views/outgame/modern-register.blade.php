@extends('outgame.layouts.main')

@section('content')
<div class="modern-register-container">
    <div class="register-card">
        <div class="register-header">
            <h1>Join the Universe</h1>
            <p class="subtitle">Create your account and conquer the cosmos</p>
        </div>

        <div id="alerts-container">
            @if ($errors->any())
                <div class="alert alert-error">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                        <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                    </svg>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form id="registerForm" method="POST" action="{{ route('register') }}" class="register-form" autocomplete="off">
            @csrf
            <input type="hidden" name="v" value="3"/>
            <input type="hidden" name="step" value="validate"/>
            <input type="hidden" name="errorCodeOn" value="1"/>
            <input type="hidden" name="is_utf8" value="1"/>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-container">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        placeholder="Enter your email"
                        required 
                        autocomplete="email"
                    />
                </div>
                <div class="error-message" id="email-error"></div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                        <circle cx="12" cy="16" r="1" stroke-width="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-width="2"/>
                    </svg>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input @error('password') is-invalid @enderror" 
                        placeholder="Create a secure password"
                        required 
                        autocomplete="new-password"
                        minlength="8"
                    />
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-meter">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                    <div class="strength-text" id="strength-text">Password strength</div>
                </div>
                <div class="error-message" id="password-error"></div>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-container">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/>
                    </svg>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-input" 
                        placeholder="Confirm your password"
                        required 
                        autocomplete="new-password"
                    />
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="password-confirmation-error"></div>
            </div>

            <!-- Universe Selection -->
            <div class="form-group">
                <label for="uni">Select Universe</label>
                <div class="input-container">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path d="M2 12h20" stroke-width="2"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke-width="2"/>
                    </svg>
                    <select 
                        id="uni" 
                        name="uni" 
                        class="form-select @error('uni') is-invalid @enderror" 
                        required
                    >
                        <option value="">Choose your universe</option>
                        <optgroup label="Novice Universes">
                            <option value="s1">1. Universe - Andromeda</option>
                            <option value="s2">2. Universe - Polaris</option>
                            <option value="s3">3. Universe - Vega</option>
                        </optgroup>
                        <optgroup label="Standard Universes">
                            <option value="s4">4. Universe - Betelgeuse</option>
                            <option value="s5">5. Universe - Sirius</option>
                            <option value="s6">6. Universe - Rigel</option>
                        </optgroup>
                        <optgroup label="Advanced Universes">
                            <option value="s7">7. Universe - Aldebaran</option>
                            <option value="s8">8. Universe - Canopus</option>
                            <option value="s9">9. Universe - Antares</option>
                        </optgroup>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6,9 12,15 18,9" stroke-width="2"/>
                    </svg>
                </div>
                <div class="error-message" id="uni-error"></div>
            </div>

            <!-- Terms of Service -->
            <div class="form-group">
                <div class="checkbox-container">
                    <input 
                        type="checkbox" 
                        id="terms" 
                        name="terms" 
                        class="checkbox-input" 
                        required
                    />
                    <label for="terms" class="checkbox-label">
                        <span class="checkbox-box">
                            <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12" stroke-width="2"/>
                            </svg>
                        </span>
                        <span class="checkbox-text">
                            I agree to the <a href="#" target="_blank" class="link">Terms of Service</a> 
                            and <a href="#" target="_blank" class="link">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                <div class="error-message" id="terms-error"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="registerBtn" class="register-btn">
                <span class="btn-text">Create Account</span>
                <div class="loading-spinner" id="loading-spinner"></div>
            </button>

            <!-- Additional Links -->
            <div class="form-footer">
                <p>Already have an account? <a href="{{ route('login') }}" class="link">Sign in here</a></p>
            </div>
        </form>
    </div>

    <!-- Background Effects -->
    <div class="background-effects">
        <div class="stars"></div>
        <div class="nebula nebula-1"></div>
        <div class="nebula nebula-2"></div>
    </div>
</div>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modern-register-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    overflow: hidden;
}

.register-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: 3rem;
    max-width: 480px;
    width: 100%;
    box-shadow: var(--shadow-xl);
    position: relative;
    z-index: 10;
}

.register-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.register-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.875rem;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--error-color);
}

.alert-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.form-group {
    margin-bottom: 1.75rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.input-container {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: var(--text-secondary);
    pointer-events: none;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-input.is-invalid {
    border-color: var(--error-color);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0.25rem;
    cursor: pointer;
    color: var(--text-secondary);
    transition: color 0.2s;
}

.password-toggle:hover {
    color: var(--text-primary);
}

.eye-icon {
    width: 20px;
    height: 20px;
}

.form-select {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.select-arrow {
    position: absolute;
    right: 3rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: var(--text-secondary);
    pointer-events: none;
}

.password-strength {
    margin-top: 0.75rem;
}

.strength-meter {
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-bar.weak {
    width: 25%;
    background: var(--error-color);
}

.strength-bar.fair {
    width: 50%;
    background: var(--warning-color);
}

.strength-bar.good {
    width: 75%;
    background: #3b82f6;
}

.strength-bar.strong {
    width: 100%;
    background: var(--success-color);
}

.strength-text {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.checkbox-container {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.checkbox-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
    line-height: 1.5;
}

.checkbox-box {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    margin-top: 2px;
}

.checkbox-input:checked + .checkbox-label .checkbox-box {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.checkbox-input:checked + .checkbox-label .check-icon {
    color: white;
}

.check-icon {
    width: 12px;
    height: 12px;
    opacity: 0;
    transition: opacity 0.2s;
}

.checkbox-input:checked + .checkbox-label .check-icon {
    opacity: 1;
}

.link {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.2s;
}

.link:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.register-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    position: relative;
    overflow: hidden;
}

.register-btn:hover {
    background: linear-gradient(135deg, var(--primary-hover), #7c3aed);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.register-btn:active {
    transform: translateY(0);
}

.register-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: none;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.form-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.error-message {
    color: var(--error-color);
    font-size: 0.75rem;
    margin-top: 0.5rem;
    min-height: 1rem;
}

.is-invalid {
    border-color: var(--error-color) !important;
}

.background-effects {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.stars {
    position: absolute;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(2px 2px at 20px 30px, white, transparent),
        radial-gradient(2px 2px at 40px 70px, white, transparent),
        radial-gradient(1px 1px at 90px 40px, white, transparent),
        radial-gradient(1px 1px at 130px 80px, white, transparent),
        radial-gradient(2px 2px at 160px 30px, white, transparent);
    background-repeat: repeat;
    background-size: 200px 100px;
    animation: twinkle 8s linear infinite;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.nebula {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: float 20s ease-in-out infinite;
}

.nebula-1 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.3), rgba(139, 92, 246, 0.3));
    top: 10%;
    left: 10%;
}

.nebula-2 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(59, 130, 246, 0.3));
    bottom: 10%;
    right: 10%;
    animation-delay: -10s;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    33% { transform: translateY(-20px) rotate(120deg); }
    66% { transform: translateY(10px) rotate(240deg); }
}

@media (max-width: 640px) {
    .modern-register-container {
        padding: 1rem;
    }
    
    .register-card {
        padding: 2rem 1.5rem;
    }
    
    .register-header h1 {
        font-size: 1.75rem;
    }
    
    .form-input,
    .form-select {
        padding: 0.75rem 1rem 0.75rem 3rem;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentNode.querySelector('.eye-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>';
    } else {
        field.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>';
    }
}

function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (!password) {
        strengthBar.className = 'strength-bar';
        strengthText.textContent = 'Password strength';
        return;
    }
    
    let score = 0;
    const checks = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    score = Object.values(checks).filter(Boolean).length;
    
    if (score < 3) {
        strengthBar.className = 'strength-bar weak';
        strengthText.textContent = 'Weak password';
    } else if (score < 4) {
        strengthBar.className = 'strength-bar fair';
        strengthText.textContent = 'Fair password';
    } else if (score < 5) {
        strengthBar.className = 'strength-bar good';
        strengthText.textContent = 'Good password';
    } else {
        strengthBar.className = 'strength-bar strong';
        strengthText.textContent = 'Strong password';
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateField(fieldId, message = '') {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + '-error');
    
    if (!field || !errorDiv) return true;
    
    if (!field.value.trim()) {
        field.classList.add('is-invalid');
        errorDiv.textContent = 'This field is required';
        return false;
    }
    
    if (fieldId === 'email' && !validateEmail(field.value)) {
        field.classList.add('is-invalid');
        errorDiv.textContent = 'Please enter a valid email address';
        return false;
    }
    
    if (fieldId === 'password' && field.value.length < 8) {
        field.classList.add('is-invalid');
        errorDiv.textContent = 'Password must be at least 8 characters';
        return false;
    }
    
    if (fieldId === 'password_confirmation') {
        const password = document.getElementById('password').value;
        if (field.value !== password) {
            field.classList.add('is-invalid');
            errorDiv.textContent = 'Passwords do not match';
            return false;
        }
    }
    
    if (fieldId === 'uni' && !field.value) {
        field.classList.add('is-invalid');
        errorDiv.textContent = 'Please select a universe';
        return false;
    }
    
    if (fieldId === 'terms' && !field.checked) {
        field.classList.add('is-invalid');
        errorDiv.textContent = 'You must agree to the Terms of Service';
        return false;
    }
    
    field.classList.remove('is-invalid');
    errorDiv.textContent = '';
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordField = document.getElementById('password');
    const passwordConfirmationField = document.getElementById('password_confirmation');
    const emailField = document.getElementById('email');
    const registerBtn = document.getElementById('registerBtn');
    const loadingSpinner = document.getElementById('loading-spinner');
    const btnText = registerBtn.querySelector('.btn-text');
    
    // Password strength indicator
    passwordField.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        
        // Real-time confirmation validation
        if (passwordConfirmationField.value) {
            validateField('password_confirmation');
        }
    });
    
    // Form validation on input
    [emailField, passwordField, passwordConfirmationField].forEach(field => {
        field.addEventListener('input', function() {
            validateField(this.id);
        });
        
        field.addEventListener('blur', function() {
            validateField(this.id);
        });
    });
    
    // Universe selection validation
    document.getElementById('uni').addEventListener('change', function() {
        validateField('uni');
    });
    
    // Terms checkbox validation
    document.getElementById('terms').addEventListener('change', function() {
        validateField('terms');
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const fieldsToValidate = ['email', 'password', 'password_confirmation', 'uni', 'terms'];
        let isValid = true;
        
        fieldsToValidate.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const isFieldValid = fieldId === 'terms' ? validateField(fieldId) : validateField(fieldId);
            if (!isFieldValid) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            // Scroll to first error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        // Show loading state
        registerBtn.disabled = true;
        loadingSpinner.style.display = 'block';
        btnText.textContent = 'Creating Account...';
        
        // Submit form
        form.submit();
    });
    
    // Real-time password confirmation check
    passwordConfirmationField.addEventListener('input', function() {
        const password = passwordField.value;
        const confirmation = this.value;
        const errorDiv = document.getElementById('password-confirmation-error');
        
        if (confirmation && password !== confirmation) {
            this.classList.add('is-invalid');
            errorDiv.textContent = 'Passwords do not match';
        } else {
            this.classList.remove('is-invalid');
            errorDiv.textContent = '';
        }
    });
});
</script>
@endsection
