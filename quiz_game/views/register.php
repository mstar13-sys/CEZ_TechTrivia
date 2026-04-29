<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            margin: 0;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        input.error {
            border-color: #e74c3c;
        }

        .error-msg {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .link {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fde8e8;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .alert-success {
            background: #e8f8f0;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Create Account</h2>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Registration successful! Redirecting to login...</div>
        <?php endif; ?>

        <form id="registerForm" action="../controllers/AuthController.php?action=register" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username (3-20 characters)" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only">
                <div class="error-msg" id="usernameError">Username must be 3-20 characters</div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                <div class="error-msg" id="emailError">Please enter a valid email address</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)" required minlength="6">
                <div class="error-msg" id="passwordError">Password must be at least 6 characters</div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                <div class="error-msg" id="confirmError">Passwords do not match</div>
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="link">Already have an account? <a href="../index.php">Login here</a></p>
    </div>

    <script>
        function validateForm() {
            let valid = true;
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');

            // Reset errors
            document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));

            // Email validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!email.value || !emailRegex.test(email.value)) {
                document.getElementById('emailError').style.display = 'block';
                email.classList.add('error');
                valid = false;
            }

            // Username validation
            if(username.value.length < 3 || username.value.length > 20) {
                document.getElementById('usernameError').style.display = 'block';
                username.classList.add('error');
                valid = false;
            }

            // Password validation
            if(password.value.length < 6) {
                document.getElementById('passwordError').style.display = 'block';
                password.classList.add('error');
                valid = false;
            }

            // Confirm password validation
            if(password.value !== confirmPassword.value) {
                document.getElementById('confirmError').style.display = 'block';
                confirmPassword.classList.add('error');
                valid = false;
            }

            return valid;
        }

        // Real-time validation feedback
        document.getElementById('email').addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const emailError = document.getElementById('emailError');
            if(this.value && !emailRegex.test(this.value)) {
                emailError.style.display = 'block';
            } else {
                emailError.style.display = 'none';
            }
        });

        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmError = document.getElementById('confirmError');
            if(this.value && this.value !== password) {
                confirmError.style.display = 'block';
            } else {
                confirmError.style.display = 'none';
            }
        });
    </script>

</body>

</html>