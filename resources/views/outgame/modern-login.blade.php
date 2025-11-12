@extends('outgame.layouts.main')

@section('content')
<div class="modern-login-container">
    <div class="login-background">
        <div class="stars-layer"></div>
        <div class="nebula-layer"></div>
    </div>
    
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">
                    <span class="title-accent">Conquer</span> the Universe
                </h1>
                <p class="login-subtitle">Enter your credentials to access OGameX</p>
            </div>

            <div class="login-form-container">
                <form method="POST" action="{{ route('login') }}" class="login-form" id="modernLoginForm">
                    @csrf
                    
                    <!-- Error Display Area -->
                    @if ($errors->any())
                        <div class="error-alert" role="alert">
                            <div class="error-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <circle cx="10" cy="10" r="8" stroke="#ff4757" stroke-width="2"/>
                                    <path d="M7 7 L13 13 M13 7 L7 13" stroke="#ff4757" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="error-content">
                                <h4>Authentication Failed</h4>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Email Field with Floating Label -->
                    <div class="input-group floating-label">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}"
                            class="form-input @error('email') is-invalid @enderror"
                            placeholder=" "
                            autocomplete="email"
                            required
                        >
                        <label for="email" class="floating-label-text">
                            <span class="label-icon">📧</span>
                            Email Address
                        </label>
                        <div class="input-border"></div>
                        <div class="validation-indicator">
                            <svg class="valid-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="8" stroke="#2ecc71" stroke-width="2"/>
                                <path d="M6 10 L9 13 L14 7" stroke="#2ecc71" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg class="invalid-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="8" stroke="#ff4757" stroke-width="2"/>
                                <path d="M7 7 L13 13 M13 7 L7 13" stroke="#ff4757" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Password Field with Floating Label -->
                    <div class="input-group floating-label">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-input @error('password') is-invalid @enderror"
                            placeholder=" "
                            autocomplete="current-password"
                            required
                        >
                        <label for="password" class="floating-label-text">
                            <span class="label-icon">🔐</span>
                            Password
                        </label>
                        <div class="input-border"></div>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <svg class="eye-off-icon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Universe Selection Dropdown -->
                    <div class="input-group">
                        <label for="universe" class="input-label">
                            <span class="label-icon">🌌</span>
                            Select Universe
                        </label>
                        <div class="select-wrapper">
                            <select 
                                name="uni" 
                                id="universe" 
                                class="form-select @error('uni') is-invalid @enderror"
                                required
                            >
                                <option value="">Choose your universe...</option>
                                <option value="s128" {{ old('uni', request('universe')) == 's128' ? 'selected' : '' }}>
                                    🌟 Betelgeuse (s128) - Experienced Players
                                </option>
                                <option value="s129" {{ old('uni', request('universe')) == 's129' ? 'selected' : '' }}>
                                    🌟 Cygnus (s129) - Advanced Players
                                </option>
                                <option value="s130" {{ old('uni', request('universe')) == 's130' ? 'selected' : '' }}>
                                    🌟 Deimos (s130) - Medium Difficulty
                                </option>
                                <option value="s140" {{ old('uni', request('universe')) == 's140' ? 'selected' : '' }}>
                                    ⭐ Nusakan (s140) - New Player Friendly
                                </option>
                                <option value="s141" {{ old('uni', request('universe')) == 's141' ? 'selected' : '' }}>
                                    ⭐ Oberon (s141) - High Speed Universe
                                </option>
                                <option value="s142" {{ old('uni', request('universe')) == 's142' ? 'selected' : '' }}>
                                    ⭐ Polaris (s142) - Balanced Universe
                                </option>
                                <option value="s143" {{ old('uni', request('universe')) == 's143' ? 'selected' : '' }}>
                                    ⭐ Quaoar (s143) - Balanced Universe
                                </option>
                            </select>
                            <div class="select-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        @error('uni')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <div class="checkbox-group">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember" 
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label for="remember" class="checkbox-label">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">Remember me for future visits</span>
                            </label>
                        </div>
                        
                        <a href="{{ route('password.request') }}" class="forgot-password-link">
                            <span class="link-icon">🔑</span>
                            Forgot your password?
                        </a>
                    </div>

                    <!-- Submit Button with Loading State -->
                    <button 
                        type="submit" 
                        class="login-submit-btn" 
                        id="loginSubmitBtn"
                        disabled
                    >
                        <span class="btn-text">Launch into Space</span>
                        <div class="btn-loading hidden">
                            <div class="loading-spinner"></div>
                            <span>Connecting...</span>
                        </div>
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p class="footer-text">
                    New to OGameX? 
                    <a href="{{ route('register') }}" class="register-link">
                        Join the conquest
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Login Page Styles */
.modern-login-container {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    overflow: hidden;
    background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
}

/* Animated Background */
.login-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.stars-layer {
    position: absolute;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(2px 2px at 20px 30px, #eee, transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.8), transparent),
        radial-gradient(1px 1px at 90px 40px, #fff, transparent),
        radial-gradient(1px 1px at 130px 80px, rgba(255,255,255,0.6), transparent),
        radial-gradient(2px 2px at 160px 30px, #ddd, transparent);
    background-repeat: repeat;
    background-size: 200px 100px;
    animation: twinkle 4s ease-in-out infinite alternate;
}

@keyframes twinkle {
    0% { opacity: 0.3; }
    100% { opacity: 1; }
}

.nebula-layer {
    position: absolute;
    width: 100%;
    height: 100%;
    background: radial-gradient(ellipse at center, rgba(65, 105, 225, 0.1) 0%, transparent 70%);
    animation: nebula-pulse 8s ease-in-out infinite alternate;
}

@keyframes nebula-pulse {
    0% { opacity: 0.3; transform: scale(1); }
    100% { opacity: 0.7; transform: scale(1.1); }
}

/* Login Card */
.login-wrapper {
    width: 100%;
    max-width: 480px;
    z-index: 1;
}

.login-card {
    background: rgba(30, 43, 57, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(97, 159, 200, 0.2);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.05) inset;
    animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideInUp {
    0% { 
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    100% { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header */
.login-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.login-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.title-accent {
    background: linear-gradient(45deg, #619fc8, #91b0c4);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.login-subtitle {
    color: #8d9aa7;
    font-size: 1rem;
    margin: 0;
}

/* Error Alert */
.error-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(255, 71, 87, 0.1);
    border: 1px solid rgba(255, 71, 87, 0.3);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    0% { 
        opacity: 0;
        transform: translateY(-20px);
    }
    100% { 
        opacity: 1;
        transform: translateY(0);
    }
}

.error-icon {
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.error-content h4 {
    color: #ff4757;
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}

.error-content p {
    color: #ff6b7a;
    font-size: 0.75rem;
    margin: 0;
}

/* Input Groups */
.input-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.input-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #91b0c4;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.label-icon {
    font-size: 1rem;
}

/* Floating Label Inputs */
.floating-label {
    position: relative;
    margin-bottom: 2rem;
}

.form-input {
    width: 100%;
    padding: 1rem 3rem 1rem 1rem;
    background: rgba(13, 51, 76, 0.5);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    outline: none;
}

.form-input::placeholder {
    color: transparent;
}

.form-input:focus {
    background: rgba(13, 51, 76, 0.8);
    border-color: #619fc8;
    box-shadow: 0 0 0 4px rgba(97, 159, 200, 0.1);
}

.form-input:valid {
    border-color: #2ecc71;
}

.form-input.is-invalid {
    border-color: #ff4757;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.floating-label-text {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #8d9aa7;
    font-size: 1rem;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-input:focus + .floating-label-text,
.form-input:not(:placeholder-shown) + .floating-label-text {
    top: -0.5rem;
    left: 0.75rem;
    font-size: 0.75rem;
    color: #619fc8;
    background: #1e2b39;
    padding: 0 0.5rem;
    border-radius: 4px;
}

.input-border {
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #619fc8, #91b0c4);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform: translateX(-50%);
}

.form-input:focus ~ .input-border {
    width: 100%;
}

.validation-indicator {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.form-input:valid ~ .validation-indicator .valid-icon {
    display: block;
}

.form-input:invalid:not(:placeholder-shown) ~ .validation-indicator .invalid-icon {
    display: block;
}

.valid-icon,
.invalid-icon {
    display: none;
}

/* Password Toggle */
.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #8d9aa7;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    color: #619fc8;
    background: rgba(97, 159, 200, 0.1);
}

.hidden {
    display: none !important;
}

/* Select Dropdown */
.select-wrapper {
    position: relative;
}

.form-select {
    width: 100%;
    padding: 1rem 3rem 1rem 1rem;
    background: rgba(13, 51, 76, 0.5);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #ffffff;
    font-size: 1rem;
    outline: none;
    appearance: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.form-select:focus {
    background: rgba(13, 51, 76, 0.8);
    border-color: #619fc8;
    box-shadow: 0 0 0 4px rgba(97, 159, 200, 0.1);
}

.form-select option {
    background: #1e2b39;
    color: #ffffff;
}

.select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #8d9aa7;
    pointer-events: none;
    transition: transform 0.3s ease;
}

.form-select:focus + .select-arrow {
    transform: translateY(-50%) rotate(180deg);
    color: #619fc8;
}

/* Field Error */
.field-error {
    display: block;
    color: #ff4757;
    font-size: 0.75rem;
    margin-top: 0.5rem;
    animation: slideInDown 0.3s ease-out;
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.checkbox-group input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    position: relative;
    width: 20px;
    height: 20px;
    background: rgba(13, 51, 76, 0.5);
    border: 2px solid #8d9aa7;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.checkbox-group input[type="checkbox"]:checked + .checkbox-label .checkbox-custom {
    background: #619fc8;
    border-color: #619fc8;
}

.checkbox-group input[type="checkbox"]:checked + .checkbox-label .checkbox-custom::after {
    content: '';
    position: absolute;
    left: 6px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    color: #8d9aa7;
    font-size: 0.875rem;
}

.checkbox-text {
    user-select: none;
}

.forgot-password-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #619fc8;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 0.5rem;
    border-radius: 6px;
}

.forgot-password-link:hover {
    color: #91b0c4;
    background: rgba(97, 159, 200, 0.1);
}

.link-icon {
    font-size: 1rem;
}

/* Submit Button */
.login-submit-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #619fc8 0%, #91b0c4 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    min-height: 56px;
}

.login-submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.login-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(97, 159, 200, 0.3);
}

.login-submit-btn:hover:not(:disabled)::before {
    left: 100%;
}

.login-submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

.login-submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-loading {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Footer */
.login-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(97, 159, 200, 0.2);
}

.footer-text {
    color: #8d9aa7;
    font-size: 0.875rem;
    margin: 0;
}

.register-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #619fc8;
    text-decoration: none;
    font-weight: 600;
    margin-left: 0.5rem;
    transition: all 0.3s ease;
}

.register-link:hover {
    color: #91b0c4;
    transform: translateX(2px);
}

/* Responsive Design */
@media (max-width: 640px) {
    .modern-login-container {
        padding: 1rem;
    }
    
    .login-card {
        padding: 2rem 1.5rem;
    }
    
    .login-title {
        font-size: 1.875rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .checkbox-group {
        order: 1;
    }
    
    .forgot-password-link {
        order: 2;
        align-self: flex-end;
    }
}

@media (max-width: 480px) {
    .login-title {
        font-size: 1.5rem;
    }
    
    .login-subtitle {
        font-size: 0.875rem;
    }
    
    .login-card {
        padding: 1.5rem 1rem;
    }
    
    .form-input,
    .form-select {
        padding: 0.875rem 2.5rem 0.875rem 0.875rem;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus styles for better accessibility */
.form-input:focus,
.form-select:focus,
.login-submit-btn:focus,
.forgot-password-link:focus,
.register-link:focus {
    outline: 2px solid #619fc8;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .login-card {
        border: 2px solid #619fc8;
    }
    
    .form-input,
    .form-select {
        border: 2px solid #619fc8;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('modernLoginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const inputs = form.querySelectorAll('.form-input, .form-select');

    // Form validation
    function validateForm() {
        let isValid = true;
        
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
            }
            
            // Email validation
            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                }
            }
        });
        
        submitBtn.disabled = !isValid;
        return isValid;
    }

    // Real-time validation
    inputs.forEach(input => {
        input.addEventListener('input', validateForm);
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        input.addEventListener('focus', function() {
            this.classList.remove('is-invalid');
        });
    });

    // Initial validation
    validateForm();

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }

        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        submitBtn.disabled = true;
        
        // Add a timeout fallback in case the form doesn't submit
        setTimeout(() => {
            if (submitBtn.disabled) {
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                submitBtn.disabled = false;
            }
        }, 10000);
    });

    // Auto-focus first input
    const firstInput = form.querySelector('.form-input');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 500);
    }
});

// Password toggle function
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.querySelector('.eye-icon');
    const eyeOffIcon = document.querySelector('.eye-off-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    }
}
</script>
@endsection