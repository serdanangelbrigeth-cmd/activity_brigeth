// ========== SHOW/HIDE PASSWORD FUNCTIONALITY ==========
// Get all password toggle icons
const passwordToggles = document.querySelectorAll('.toggle-password');

// Add click event to each toggle icon
passwordToggles.forEach(toggle => {
    toggle.addEventListener('click', function() {
        // Find the associated password input
        const passwordInput = this.parentElement.querySelector('input');
        
        // Toggle password visibility
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            this.classList.remove('bxs-lock-alt');
            this.classList.add('bxs-lock-open-alt');
        } else {
            passwordInput.type = 'password';
            this.classList.remove('bxs-lock-open-alt');
            this.classList.add('bxs-lock-alt');
        }
    });
});

// ========== DYNAMIC TEXT CHANGES ==========
// Get text elements to change
const welcomeText = document.getElementById('welcomeText');
const welcomeBackText = document.getElementById('welcomeBackText');

// Array of possible welcome messages
const welcomeMessages = [
    "Hello, Welcome!",
    "Ready to get started?",
    "Join our community!",
    "Create your account today!"
];

const welcomeBackMessages = [
    "Welcome Back!",
    "Good to see you again!",
    "Missed you!",
    "Ready to continue?"
];

// Change text every 5 seconds
let welcomeIndex = 0;
let welcomeBackIndex = 0;

setInterval(() => {
    // Update welcome text
    welcomeText.textContent = welcomeMessages[welcomeIndex];
    welcomeIndex = (welcomeIndex + 1) % welcomeMessages.length;
    
    // Update welcome back text
    welcomeBackText.textContent = welcomeBackMessages[welcomeBackIndex];
    welcomeBackIndex = (welcomeBackIndex + 1) % welcomeBackMessages.length;
}, 5000);

// ========== FORM TOGGLE FUNCTIONALITY ==========
// Get toggle buttons and container
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');
const container = document.querySelector('.container');

// Add event listeners for form toggling
registerBtn.addEventListener('click', () => {
    container.classList.add('active');
});

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
});

// ========== LOGIN VALIDATION AND SUCCESS FUNCTIONALITY ==========
// Get login form and inputs
const loginForm = document.querySelector('.login form');
const loginUsername = document.getElementById('loginUsername');
const loginPassword = document.getElementById('loginPassword');

// Add submit event to login form
loginForm.addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent form submission
    
    // Check if both fields are not empty
    if (loginUsername.value.trim() !== '' && loginPassword.value.trim() !== '') {
        // Send login data to PHP
        fetch('login_handler.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=' + encodeURIComponent(loginUsername.value) + 
                  '&password=' + encodeURIComponent(loginPassword.value)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to mypage.php instead of showing welcome page
                window.location.href = 'mypage.php';
            } else {
                alert('Login failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Login error occurred');
        });
    } else {
        // Show error message if fields are empty
        alert('Please fill in both username and password fields.');
    }
});

// ========== REGISTRATION FORM HANDLING ==========
// Get registration form and inputs
const registerForm = document.getElementById('registerForm');
const registerUsername = document.getElementById('registerUsername');
const registerEmail = document.getElementById('registerEmail');
const registerPassword = document.getElementById('registerPassword');

// Add submit event to registration form
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent form submission
        
        // Check if all fields are not empty
        if (registerUsername.value.trim() !== '' && 
            registerEmail.value.trim() !== '' && 
            registerPassword.value.trim() !== '') {
            // Send registration data to PHP
            fetch('login_handler.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=register&username=' + encodeURIComponent(registerUsername.value) + 
                      '&email=' + encodeURIComponent(registerEmail.value) +
                      '&password=' + encodeURIComponent(registerPassword.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful! You can now login.');
                    // Switch to login form
                    container.classList.remove('active');
                    // Clear form
                    registerForm.reset();
                } else {
                    alert('Registration failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Registration error occurred');
            });
        } else {
            // Show error message if fields are empty
            alert('Please fill in all fields.');
        }
    });
}

// Function to show welcome page after successful login
function showWelcomePage() {
    const container = document.querySelector('.container');
    
    // Create navigation bar
    const navBar = document.createElement('nav');
    navBar.className = 'main-nav';
    navBar.innerHTML = `
        <div class="nav-container">
            <div class="nav-logo">MyPage</div>
            <ul class="nav-menu">
                <li><a href="#" class="nav-link">Home</a></li>
                <li><a href="mypage.php" class="nav-link">My Files</a></li>
                <li><a href="logout.php" class="nav-link logout-btn">Logout</a></li>
            </ul>
        </div>
    `;
    
    // Create welcome section
    const welcomeSection = document.createElement('div');
    welcomeSection.className = 'welcome-section';
    welcomeSection.innerHTML = `
        <div class="welcome-container">
            <h1 class="welcome-title">Hi New User!!</h1>
            <p class="welcome-subtitle">You have successfully logged in to your account</p>
            <div class="welcome-content">
                <p>Start exploring your page.</p>
            </div>
        </div>
    `;
    
    // Clear the container and add new content
    container.innerHTML = '';
    container.appendChild(navBar);
    container.appendChild(welcomeSection);
    
    // Add logout functionality
    const logoutBtn = document.querySelector('.logout-btn');
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        location.reload(); // Reload the page to show login form again
    });
}