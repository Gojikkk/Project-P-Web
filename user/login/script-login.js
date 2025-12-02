// script-login.js
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const errorMessage = document.getElementById("errorMessage");

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // Clear previous errors
        clearErrors();

        // Get form data
        const formData = new FormData(loginForm);
         formData.append('login', '1'); //
        
        // Validasi frontend dulu
        if (!validateForm()) {
            return;
        }

        // Tampilkan loading (optional)
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Loading...';
        submitBtn.disabled = true;

        // Send request ke PHP
        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Cek kalau ada redirect
            if (response.redirected) {
                window.location.href = response.url;
                return null;
            }
            return response.text();
        })
        .then(data => {
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;

            // Kalau ada response text = ada error dari PHP
            if (data && data.trim() !== '') {
                showError(data.trim());
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            showError("Terjadi kesalahan. Silakan coba lagi.");
        });
    });

    function validateForm() {
        let isValid = true;

        // Validate email
        const email = document.getElementById("email").value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email === "") {
            showFieldError("emailError", "Email tidak boleh kosong");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showFieldError("emailError", "Format email tidak valid");
            isValid = false;
        }

        // Validate password
        const password = document.getElementById("Password").value;
        if (password === "") {
            showFieldError("passwordError", "Password tidak boleh kosong");
            isValid = false;
        }

        return isValid;
    }


    // Show error
    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = "block";
        }
    }

    // Show field error
    function showFieldError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = "block";
        }
    }

    // Clear all errors
    function clearErrors() {
        const errors = document.querySelectorAll(".error-message, .field-error");
        errors.forEach(error => {
            error.textContent = "";
            error.style.display = "none";
        });
    }
});