<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: mypage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form</title>
    <!-- Use project CSS -->
    <link rel="stylesheet" href="ACT_CSS.SERDAN.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="form-box login">
            <form id="loginForm">
                <h1>Login</h1>
                <div id="loginMessage" style="display:none;margin-bottom:10px;padding:8px;border-radius:6px;"></div>
                <div class="input-box">
                    <input type="text" name="username" id="loginUsername" placeholder="Username" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" id="loginPassword" required>
                    <i class='bx bxs-lock-alt toggle-password'></i>
                </div>
                <div class="forgot-link">
                    <a href="#" id="forgotPasswordLink">Forgot Password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
                <p>or login with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-google' ></i></a>
                    <a href="#"><i class='bx bxl-facebook' ></i></a>
                    <a href="#"><i class='bx bxl-github' ></i></a>
                    <a href="#"><i class='bx bxl-linkedin' ></i></a>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <form action="#" id="registerForm">
                <h1>Registration</h1>
                <div class="input-box">
                    <input type="text" name="username" id="registerUsername" placeholder="Username" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" id="registerEmail" placeholder="Email" required>
                    <i class='bx bxs-envelope' ></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" id="registerPassword" required>
                    <i class='bx bxs-lock-alt toggle-password'></i>
                </div>
                <button type="submit" class="btn">Register</button>
                <p>or register with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-google' ></i></a>
                    <a href="#"><i class='bx bxl-facebook' ></i></a>
                    <a href="#"><i class='bx bxl-github' ></i></a>
                    <a href="#"><i class='bx bxl-linkedin' ></i></a>
                </div>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1 id="welcomeText">Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Register</button>
            </div>

            <div class="toggle-panel toggle-right">
                <h1 id="welcomeBackText">Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <!-- Project JavaScript -->
    <script src="ACT3_JAVA.SERDAN.js"></script>
    <script>
    (function(){
        var form = document.getElementById('loginForm');
        var msg = document.getElementById('loginMessage');

        function showMessage(text, type){
            msg.style.display = 'block';
            msg.textContent = text;
            msg.style.background = (type === 'success') ? 'rgba(40,167,69,0.08)' : 'rgba(220,53,69,0.08)';
            msg.style.color = (type === 'success') ? '#28a745' : '#b02a37';
            if(type === 'success'){
                setTimeout(function(){ msg.style.display='none'; }, 2500);
            }
        }

        if(form){
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var username = document.getElementById('loginUsername').value.trim();
                var password = document.getElementById('loginPassword').value;

                if(!username || !password){
                    showMessage('Please enter both username and password.', 'error');
                    return;
                }

                var fd = new FormData();
                fd.append('username', username);
                fd.append('password', password);

                fetch('login_handler.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: fd
                }).then(function(res){
                    return res.json();
                }).then(function(data){
                    if(data.success){
                        showMessage('Login successful — redirecting...', 'success');
                        setTimeout(function(){ window.location.href = 'index.php'; }, 800);
                    } else {
                        showMessage(data.message || 'Invalid username or password.', 'error');
                    }
                }).catch(function(){
                    showMessage('Network error. Please try again.', 'error');
                });
            });
        }
    })();
    </script>
</body>
</html>