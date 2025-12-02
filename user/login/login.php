<?php
session_start();
include '../../connection/connection.php'; 

if (isset($_POST['login'])) {
    // Ambil data dari form
    $email = trim($_POST['email']);
    $Password = trim($_POST['Password']);
    
    $error_message = "";
    
    // VALIDASI INPUT DULU (SEBELUM QUERY!)
    if (empty($email) && empty($Password)) {
        $error_message = "Email dan Password tidak boleh kosong";
    } elseif (empty($email)) {
        $error_message = "Email tidak boleh kosong";
    } elseif (empty($Password)) {
        $error_message = "Password tidak boleh kosong";
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $error_message = "Format email tidak valid";
    }
    
    // Kalau ada error, kirim ke JavaScript
    if (!empty($error_message)) {
        echo $error_message;
        exit();
    }
    
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);
    
    // Query database - CUMA CARI EMAIL (bukan password!)
    $query = "SELECT * FROM user WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    // Cek apakah query berhasil
    if (!$result) {
        echo "Terjadi kesalahan database";
        exit();
    }
    
    $num_rows = mysqli_num_rows($result);
    
    // Kalau user ditemukan
    if ($num_rows === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password dengan hash di database
        if (password_verify($Password, $user['Password'])) {
            // Login berhasil - set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            // Redirect ke home
            header("Location: ../../menu/menu.html");
            exit();
        } else {
            echo "Email atau Password salah";
            exit();
        }
    } else {
        echo "Email atau Password salah";
        exit();
    }
}
?>