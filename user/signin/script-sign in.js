// Tampilkan error/success dari URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');
    
    if (error) {
        showError('signupError', decodeURIComponent(error));
        document.getElementById('signupError').style.color = '#ef4444';
    }
    
    if (success) {
        showError('signupError', 'Pendaftaran berhasil! Redirecting...');
        document.getElementById('signupError').style.color = '#22c55e';
        setTimeout(() => {
            window.location.href = '../proses/login/login.html';
        }, 2000);
    }
    
    // Validasi form (sama kayak sebelumnya...)
    const signupForm = document.getElementById('signupForm');
    signupForm.addEventListener('submit', function(e) {
        // ... validasi code ...
    });
});

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}