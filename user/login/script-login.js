document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const errorMessage = document.getElementById("errorMessage");

    // Kalau halaman BUKAN login, jangan jalankan kode login
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            clearErrors();  // Sekarang ini akan bekerja

            const formData = new FormData(loginForm);
            formData.append('login', '1');

            if (!validateForm()) return;

            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Loading...';
            submitBtn.disabled = true;

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;

                if (data.success) {
                    localStorage.setItem('loggedInUser', JSON.stringify(data.userData));
                    localStorage.setItem('isLoggedIn', 'true');

                    window.location.href = data.redirect;
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                showError("Terjadi kesalahan. Silakan coba lagi.");
            });
        });
    }

    // ===================================================
    // VALIDASI FORM
    // ===================================================
    function validateForm() {
        let isValid = true;

        const emailInput = document.getElementById("Email");
        const PasswordInput = document.getElementById("Password");

        // Cek element HTML ADA (supaya tidak null)
        if (!emailInput || !PasswordInput) {
            console.error("Element Email atau Password tidak ditemukan di HTML!");
            return false;
        }

        const email = emailInput.value.trim();
        const Password = PasswordInput.value;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === "") {
            showFieldError("emailError", "Email tidak boleh kosong");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showFieldError("emailError", "Format email tidak valid");
            isValid = false;
        }

        if (Password === "") {
            showFieldError("passwordError", "Password tidak boleh kosong");
            isValid = false;
        }

        return isValid;
    }

    // ===================================================
    // ERROR HANDLERS
    // ===================================================
    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = "block";
        }
    }

    // Perbaikan: Ubah nama fungsi agar sesuai dengan yang dipanggil
    function showFieldError(elementId, message) {
        const fieldErrorElement = document.getElementById(elementId);
        if (fieldErrorElement) {
            fieldErrorElement.textContent = message;
            fieldErrorElement.style.display = 'block';
        }
    }

    // Perbaikan: Ubah nama fungsi agar sesuai dengan yang dipanggil
    function clearErrors() {
        const errorElements = document.querySelectorAll(".error-message, .field-error");
        errorElements.forEach(errorElement => {
            errorElement.textContent = "";
            errorElement.style.display = "none";
        });
    }
});

// =======================================================
// LOGIN STATUS GLOBAL FUNCTIONS
// =======================================================
function isUserLoggedIn() {
    return localStorage.getItem('isLoggedIn') === 'true';
}

function getLoggedInUser() {
    const userData = localStorage.getItem('loggedInUser');
    return userData ? JSON.parse(userData) : null;
}

function logoutUser() {
    if (confirm('Yakin ingin logout?')) {
        localStorage.removeItem('loggedInUser');
        localStorage.removeItem('isLoggedIn');
        sessionStorage.clear();
        window.location.href = '../../login/login.html';
    }
}

window.isUserLoggedIn = isUserLoggedIn;
window.getLoggedInUser = getLoggedInUser;
window.logoutUser = logoutUser;
