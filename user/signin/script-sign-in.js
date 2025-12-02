// Tampilkan error/success dari URL
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get("error");
    const success = urlParams.get("success");

    if (error) {
        showError("signupError", decodeURIComponent(error));
        document.getElementById("signupError").style.color = "#ef4444";
    }

    if (success) {
        showError("signupError", decodeURIComponent(success));
        document.getElementById("signupError").style.color = "#22c55e";
        setTimeout(() => {
        window.location.href = "../login/login.html";
        }, 2000);
    }

    // Validasi form
    const signupForm = document.getElementById("signupForm");
    signupForm.addEventListener("submit", function (e) {
        // Clear errors sebelum validasi
        clearErrors();

        if (!validateForm()) {
            e.preventDefault();  // Cegah submit jika tidak valid
            showError("signupError", "Please fix the errors before submitting.");
            return false;
        }
        // Jika valid, biarkan form submit secara normal
    });
});

function validateForm() {
    let isValid = true;

    // Validasi username
    const username = document.getElementById("username").value.trim();
    if (username === "") {
        showFieldError("fullnameError", "Full name is required.");
        isValid = false;
    }

    // Validasi email
    const email = document.getElementById("email").value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === "") {
        showFieldError("emailError", "Email is required.");
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showFieldError("emailError", "Please enter a valid email address.");
        isValid = false;
    }

    // Validasi phone - DIPERBAIKI: terima format lebih fleksibel
    const phone = document.getElementById("phone").value.trim();
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;  // Terima angka, spasi, dash, plus, kurung
    
    if (phone === "") {
        showFieldError("phoneError", "Phone number is required.");
        isValid = false;
    } else if (!phoneRegex.test(phone)) {
        showFieldError("phoneError", "Phone number can only contain numbers and basic symbols.");
        isValid = false;
    } else if (phone.replace(/\D/g, '').length < 10) {  // Minimal 10 digit angka
        showFieldError("phoneError", "Phone number must have at least 10 digits.");
        isValid = false;
    }

    // Validasi password
    const password = document.getElementById("password").value;
    if (password.length < 8) {
        showFieldError("passwordError", "Password must be at least 8 characters.");
        isValid = false;
    }

    // Validasi checkbox
    const agreeTerms = document.getElementById("agreeTerms").checked;
    if (!agreeTerms) {
        showFieldError("agreeTermsError", "You must agree to the terms.");
        isValid = false;
    }

    return isValid;
}

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = "block";
    }
}

function showFieldError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = "block";
    }
}

function clearErrors() {
    const errors = document.querySelectorAll(".error-message, .field-error");
    errors.forEach(error => {
        error.textContent = "";
        error.style.display = "none";
    });
}