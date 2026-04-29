<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
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
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
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
            margin-top: 10px;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group input {
            width: auto;
            margin-right: 8px;
        }

        .checkbox-group label {
            display: inline;
            margin: 0;
            font-weight: 400;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Quiz Reviewer</h2>
        <p class="subtitle">Login to your account</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['registered'])): ?>
            <div class="alert" style="background: #e8f8f0; color: #27ae60; border: 1px solid #27ae60;">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <form id="loginForm" action="quiz_game/controllers/AuthController.php?action=login" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
                <div class="error-msg" id="usernameError">Username is required</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div class="error-msg" id="passwordError">Password is required</div>
            </div>

            <button type="submit">Login</button>
        </form>

        <p class="link">New user? <a href="quiz_game/views/register.php">Register here</a></p>
    </div>

    <script>
        function validateForm() {
            let valid = true;
            const username = document.getElementById('username');
            const password = document.getElementById('password');

            // Reset errors
            document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));

            // Username validation
            if(!username.value.trim()) {
                document.getElementById('usernameError').style.display = 'block';
                username.classList.add('error');
                valid = false;
            }

            // Password validation
            if(!password.value) {
                document.getElementById('passwordError').style.display = 'block';
                password.classList.add('error');
                valid = false;
            }

            return valid;
        }

        // Clear error on input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMsg = this.nextElementSibling;
                if(errorMsg && errorMsg.classList.contains('error-msg')) {
                    errorMsg.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>



















