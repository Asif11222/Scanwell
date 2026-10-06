<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In &mdash; ScanWell Intelligence Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-base: #060b08;
            --bg-card: rgba(16, 26, 20, 0.82);
            --border-card: rgba(34, 197, 94, 0.18);
            --primary: #10b981;
            --primary-hover: #059669;
            --primary-glow: rgba(16, 185, 129, 0.25);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --text-dim: #6b7280;
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.12);
            --danger-border: rgba(239, 68, 68, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-base);
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(16, 185, 129, 0.14) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(5, 150, 105, 0.1) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(13, 22, 17, 0.9) 0%, #060b08 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text-main);
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle animated background grid */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 40px var(--primary-glow);
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            border-radius: 14px;
            margin-bottom: 16px;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.5);
            color: #fff;
            font-size: 24px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ffffff 30%, #a7f3d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.45;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-danger {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: #fca5a5;
        }

        .alert-info {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-dim);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            background: rgba(8, 14, 10, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 14px 12px 40px;
            font-size: 14px;
            color: #fff;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            background: rgba(10, 18, 13, 0.9);
        }

        .form-control:focus + .input-icon {
            color: var(--primary);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        .field-error {
            font-size: 11px;
            font-weight: 600;
            color: #ef4444;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
            line-height: 1.3;
        }

        .field-error i {
            font-size: 11px;
            color: #ef4444;
            flex-shrink: 0;
        }

        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }



        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-dim);
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h1 class="brand-title">ScanWell</h1>
                <p class="brand-subtitle">Product Intelligence & Health Governance Portal</p>
            </div>

            <!-- Global Error Banner (Only for special administrative errors like account suspension) -->
            @if ($errors->has('email') && !in_array($errors->first('email'), ['Email and password need must', 'Incorrect email or password', 'Please provide your registered administrator email address.', 'The email provided is not formatted as a valid administrator address.']))
                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-top: 2px;"></i>
                    <div>{{ $errors->first('email') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info">
                    <i class="fa-solid fa-circle-info" style="margin-top: 2px;"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" autocomplete="off" id="loginForm" novalidate>
                @csrf

                <!-- Email Input -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        <span>Email</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}" 
                            value="{{ old('email') }}" 
                            placeholder="admin@scanwell.app" 
                            autofocus
                        >
                    </div>
                    <div class="field-error" id="emailFieldError" style="{{ ($errors->has('email') && $errors->first('email') !== 'Incorrect email or password') ? 'display: flex;' : 'display: none;' }}">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ ($errors->has('email') && $errors->first('email') !== 'Incorrect email or password') ? $errors->first('email') : '' }}</span>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <span>Password</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control {{ ($errors->has('password') || ($errors->has('email') && $errors->first('email') === 'Incorrect email or password')) ? 'is-invalid' : '' }}" 
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                        >
                        <button type="button" class="password-toggle" id="togglePasswordBtn" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @php
                        $passwordErrorMsg = '';
                        if ($errors->has('password')) {
                            $passwordErrorMsg = $errors->first('password');
                        } elseif ($errors->has('email') && $errors->first('email') === 'Incorrect email or password') {
                            $passwordErrorMsg = 'Incorrect email or password';
                        }
                    @endphp
                    <div class="field-error" id="passwordFieldError" style="{{ !empty($passwordErrorMsg) ? 'display: flex;' : 'display: none;' }}">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $passwordErrorMsg }}</span>
                    </div>
                </div>

                <!-- Remember Terminal -->
                <div class="form-meta">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    <span style="color: var(--text-dim); font-size: 12px;">TLS 1.3 Encrypted</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Login</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>


        </div>

        <div class="footer-note">
            &copy; 2026 ScanWell Global Health Inc. High-Security Environment.
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });

        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const emailFieldError = document.getElementById('emailFieldError');
        const passwordFieldError = document.getElementById('passwordFieldError');
        const submitBtn = document.getElementById('submitBtn');

        function setFieldError(inputEl, errorEl, message) {
            if (inputEl) {
                inputEl.classList.add('is-invalid');
            }
            if (errorEl) {
                const span = errorEl.querySelector('span');
                if (span) span.textContent = message;
                errorEl.style.display = 'flex';
            }
        }

        function clearFieldError(inputEl, errorEl) {
            if (inputEl) {
                inputEl.classList.remove('is-invalid');
            }
            if (errorEl) {
                errorEl.style.display = 'none';
                const span = errorEl.querySelector('span');
                if (span) span.textContent = '';
            }
        }

        emailInput.addEventListener('input', function () {
            clearFieldError(emailInput, emailFieldError);
            if (passwordFieldError && passwordFieldError.textContent.includes('Incorrect email or password')) {
                clearFieldError(passwordInput, passwordFieldError);
            }
        });

        passwordInput.addEventListener('input', function () {
            clearFieldError(passwordInput, passwordFieldError);
            if (emailInput.classList.contains('is-invalid') && passwordFieldError && passwordFieldError.textContent.includes('Incorrect email or password')) {
                clearFieldError(emailInput, emailFieldError);
            }
        });

        loginForm.addEventListener('submit', function (e) {
            const emailVal = emailInput.value.trim();
            const passwordVal = passwordInput.value;
            let hasError = false;

            // Clear previous errors
            clearFieldError(emailInput, emailFieldError);
            clearFieldError(passwordInput, passwordFieldError);

            if (!emailVal && !passwordVal) {
                e.preventDefault();
                setFieldError(emailInput, emailFieldError, 'Email and password need must');
                setFieldError(passwordInput, passwordFieldError, 'Email and password need must');
                emailInput.focus();
                hasError = true;
            } else if (!emailVal) {
                e.preventDefault();
                setFieldError(emailInput, emailFieldError, 'Email and password need must');
                emailInput.focus();
                hasError = true;
            } else if (!passwordVal) {
                e.preventDefault();
                setFieldError(passwordInput, passwordFieldError, 'Email and password need must');
                passwordInput.focus();
                hasError = true;
            }

            if (hasError) {
                return false;
            }

            // Valid submission - show loading indicator
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Authenticating...';
            submitBtn.style.opacity = '0.85';
            submitBtn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>
