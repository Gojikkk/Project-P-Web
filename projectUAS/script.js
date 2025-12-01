// Validation System
class FormValidator {
    constructor() {
        this.registeredEmails = ['existing@email.com', 'test@test.com'];
        this.registeredPhones = ['08123456789', '0876543210'];
    }

    validateLogin(formData) {
        const errors = {};
        
        // Check empty fields
        if (!formData.email && !formData.password) {
            errors.general = "Email dan Password tidak boleh kosong";
            return errors;
        }
        
        if (!formData.email) {
            errors.email = "Email tidak boleh kosong";
        } else if (!this.isValidEmail(formData.email)) {
            errors.email = "Format email tidak valid";
        }
        
        if (!formData.password) {
            errors.password = "Password tidak boleh kosong";
        } else if (formData.password.length < 6) {
            errors.password = "Password harus minimal 6 karakter";
        }
        
        return errors;
    }

    validateSignup(formData) {
        const errors = {};
        
        // Check all empty fields
        if (!formData.fullname && !formData.email && !formData.phone && !formData.password) {
            errors.general = "Semua data harus diisi";
            return errors;
        }
        
        if (!formData.fullname) {
            errors.fullname = "Nama lengkap tidak boleh kosong";
        }
        
        if (!formData.email) {
            errors.email = "Email tidak boleh kosong";
        } else if (!this.isValidEmail(formData.email)) {
            errors.email = "Format email tidak valid";
        } else if (this.registeredEmails.includes(formData.email)) {
            errors.email = "Email sudah terdaftar";
        }
        
        if (!formData.phone) {
            errors.phone = "Nomor telepon tidak boleh kosong";
        } else if (!this.isValidPhone(formData.phone)) {
            errors.phone = "Format nomor telepon tidak valid";
        } else if (!this.isNumeric(formData.phone)) {
            errors.phone = "Nomor telepon harus angka semua";
        } else if (this.registeredPhones.includes(formData.phone)) {
            errors.phone = "Nomor telepon sudah terdaftar";
        }
        
        if (!formData.password) {
            errors.password = "Password tidak boleh kosong";
        } else if (formData.password.length < 8) {
            errors.password = "Password harus minimal 8 karakter";
        }
        
        if (!formData.agreeTerms) {
            errors.agreeTerms = "Anda harus menyetujui Terms of Service dan Privacy Policy";
        }
        
        return errors;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[0-9]{10,13}$/;
        return phoneRegex.test(phone);
    }

    isNumeric(str) {
        return /^\d+$/.test(str);
    }

    displayErrors(formId, errors) {
        // Reset all errors
        document.querySelectorAll(`#${formId} .error-message`).forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.querySelectorAll(`#${formId} .field-error`).forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });

        // Display general error
        if (errors.general) {
            const errorEl = document.querySelector(`#${formId} .error-message`);
            errorEl.textContent = errors.general;
            errorEl.style.display = 'block';
        }

        // Display field errors
        Object.keys(errors).forEach(field => {
            if (field !== 'general') {
                const errorEl = document.querySelector(`#${formId} #${field}Error`);
                if (errorEl) {
                    errorEl.textContent = errors[field];
                    errorEl.style.display = 'block';
                    
                    // Add error class to input
                    const inputEl = document.querySelector(`#${formId} [name="${field}"]`);
                    if (inputEl) {
                        inputEl.classList.add('error');
                    }
                }
            }
        });
    }

    clearErrors(formId) {
        document.querySelectorAll(`#${formId} .error-message`).forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll(`#${formId} .field-error`).forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll(`#${formId} .error`).forEach(el => {
            el.classList.remove('error');
        });
    }
}

// Initialize validator
const validator = new FormValidator();

// Form submission with validation
if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = {
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value.trim()
        };
        
        const errors = validator.validateLogin(formData);
        
        if (Object.keys(errors).length === 0) {
            // Submit form if no errors
            alert('Login berhasil!');
            window.location.href = 'index.html';
        } else {
            validator.displayErrors('loginForm', errors);
        }
    });
}

if (document.getElementById('signupForm')) {
    document.getElementById('signupForm').addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = {
            fullname: document.getElementById('fullname').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            password: document.getElementById('password').value.trim(),
            agreeTerms: document.getElementById('agreeTerms').checked
        };
        
        const errors = validator.validateSignup(formData);
        
        if (Object.keys(errors).length === 0) {
            // Submit form if no errors
            alert('Pendaftaran berhasil!');
            window.location.href = 'login.html';
        } else {
            validator.displayErrors('signupForm', errors);
        }
    });
}

// Clear errors when input changes
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', (e) => {
        const form = e.target.closest('form');
        if (form) {
            validator.clearErrors(form.id);
        }
    });
});