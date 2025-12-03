<?php
// ✅ Clear output buffer dulu sebelum return JSON
ob_start();
session_start();
include '../../connection/connection.php';

// ✅ Clear any previous output
ob_clean();

// ✅ Set header JSON
header('Content-Type: application/json');

if (isset($_POST['login'])) {
    // Ambil data dari form
    $email = trim($_POST['Email']);
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
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
        exit();
    }
    
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);
    
    // Query database - AMBIL SEMUA KOLOM
    $query = "SELECT * FROM user WHERE Email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    // Cek apakah query berhasil
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ]);
        exit();
    }
    
    $num_rows = mysqli_num_rows($result);
    
    // Kalau user ditemukan
    if ($num_rows === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password dengan hash di database
        if (password_verify($Password, $user['Password'])) {
            // Cek nama kolom ID (bisa ID_User atau lainnya)
            $userId = isset($user['ID_User']) ? $user['ID_User'] : (isset($user['id']) ? $user['id'] : null);
            
            // Login berhasil - set session
            $_SESSION['ID_User'] = $userId;
            $_SESSION['Email'] = $user['Email'];
            $_SESSION['logged_in'] = true;
            
            // ✅ HAPUS PASSWORD DARI DATA YANG DIKIRIM KE FRONTEND
            unset($user['Password']);
            
            // ✅ Return JSON success dengan DATA USER
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => '../../menu/menu.html',
                'userData' => $user // Kirim data user TANPA password
            ]);
            exit();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email atau Password salah'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email atau Password salah'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}
?>