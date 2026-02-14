// Login Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const loginError = document.getElementById('loginError');
    
    // Toggle Password Visibility
    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = togglePassword.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }
    
    // Form Submission
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Hide previous errors
            hideError();
            
            // Validation
            if (!username || !password) {
                showError('لطفا تمام فیلدها را پر کنید');
                return;
            }
            
            if (username.length < 3) {
                showError('نام کاربری باید حداقل 3 کاراکتر باشد');
                return;
            }
            
            if (password.length < 6) {
                showError('رمز عبور باید حداقل 6 کاراکتر باشد');
                return;
            }
            
            // Simulate login process
            const loginButton = loginForm.querySelector('.login-button');
            const originalText = loginButton.innerHTML;
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>در حال ورود...</span>';
            
            // Simulate API call
            setTimeout(() => {
                // در اینجا می‌توانید با API واقعی ارتباط برقرار کنید
                // برای مثال:
                // authenticateUser(username, password)
                //     .then(response => {
                //         if (response.success) {
                //             if (remember) {
                //                 localStorage.setItem('rememberUser', 'true');
                //                 localStorage.setItem('username', username);
                //             }
                //             sessionStorage.setItem('isLoggedIn', 'true');
                //             window.location.href = 'index.html';
                //         } else {
                //             showError(response.message || 'نام کاربری یا رمز عبور اشتباه است');
                //             loginButton.disabled = false;
                //             loginButton.innerHTML = originalText;
                //         }
                //     })
                //     .catch(error => {
                //         showError('خطا در ارتباط با سرور');
                //         loginButton.disabled = false;
                //         loginButton.innerHTML = originalText;
                //     });
                
                // برای تست - در حالت واقعی این بخش را حذف کنید
                if (username === 'admin' && password === 'admin123') {
                    // ذخیره اطلاعات ورود
                    if (remember) {
                        localStorage.setItem('rememberUser', 'true');
                        localStorage.setItem('username', username);
                    }
                    sessionStorage.setItem('isLoggedIn', 'true');
                    sessionStorage.setItem('username', username);
                    
                    // هدایت به صفحه اصلی
                    window.location.href = 'index.html';
                } else {
                    showError('نام کاربری یا رمز عبور اشتباه است');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalText;
                }
            }, 1500);
        });
    }
    
    // Check if user is already logged in
    checkLoginStatus();
    
    // Check remember me
    checkRememberMe();
});

// Show Error Message
function showError(message) {
    const loginError = document.getElementById('loginError');
    if (loginError) {
        loginError.textContent = message;
        loginError.classList.add('show');
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            hideError();
        }, 5000);
    }
}

// Hide Error Message
function hideError() {
    const loginError = document.getElementById('loginError');
    if (loginError) {
        loginError.classList.remove('show');
    }
}

// Check Login Status
function checkLoginStatus() {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    if (isLoggedIn === 'true') {
        // اگر کاربر قبلا وارد شده، به صفحه اصلی هدایت شود
        window.location.href = 'index.html';
    }
}

// Check Remember Me
function checkRememberMe() {
    const rememberUser = localStorage.getItem('rememberUser');
    const savedUsername = localStorage.getItem('username');
    
    if (rememberUser === 'true' && savedUsername) {
        const usernameInput = document.getElementById('username');
        const rememberCheckbox = document.getElementById('remember');
        
        if (usernameInput) {
            usernameInput.value = savedUsername;
        }
        if (rememberCheckbox) {
            rememberCheckbox.checked = true;
        }
    }
}

// Example API function (برای استفاده در آینده)
async function authenticateUser(username, password) {
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Login error:', error);
        return {
            success: false,
            message: 'خطا در ارتباط با سرور'
        };
    }
}