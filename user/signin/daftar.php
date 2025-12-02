<?php
session_start();
include '../../connection/connection.php';  // Pastikan path ini benar di XAMPP Anda

if (isset($_POST['daftar'])) {
    $username = htmlentities(strip_tags(trim($_POST['username'])));
    $Password = htmlentities(strip_tags(trim($_POST['Password'])));
    $Telp = htmlentities(strip_tags(trim($_POST['No_Telp'])));
    $email = htmlentities(strip_tags(trim($_POST['Email'])));

    $error_message = "";  // Perbaiki nama variabel (sebelumnya $error_massage)

    // Validasi input kosong
    if (empty($username) && empty($Password) && empty($Telp) && empty($email)) {
        $error_message = "Semua data harus diisi";
    } elseif (empty($username)) {
        $error_message = "Username tidak boleh kosong";
    } elseif (empty($Password)) {
        $error_message = "Password tidak boleh kosong";
    } elseif (empty($Telp)) {
        $error_message = "No Telp tidak boleh kosong";
    } elseif (empty($email)) {
        $error_message = "Email tidak boleh kosong";
    }

    // Validasi panjang password (minimal 8, sesuai HTML)
    if (empty($error_message) && strlen($Password) < 8) {
        $error_message = "Password minimal 8 karakter";
    }

    // Validasi format input (hanya jika tidak ada error sebelumnya)
    if (empty($error_message)) {
        if (!is_numeric($Telp)) {
            $error_message = "Nomor Telepon harus berupa angka";
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $error_message = "Format email tidak valid";
        }
    }

    // Cek username, telp, email sudah ada (hanya jika tidak ada error sebelumnya)
    if (empty($error_message)) {
        $username = mysqli_real_escape_string($conn, $username);
        $query_username = "SELECT * FROM user WHERE Username='$username' LIMIT 1";
        $result_username = mysqli_query($conn, $query_username);
        $num_rows_username = mysqli_num_rows($result_username);

        $Telp = mysqli_real_escape_string($conn, $Telp);
        $query_telp = "SELECT * FROM user WHERE No_Telp='$Telp' LIMIT 1";
        $result_telp = mysqli_query($conn, $query_telp);
        $num_rows_telp = mysqli_num_rows($result_telp);

        $email = mysqli_real_escape_string($conn, $email);
        $query_email = "SELECT * FROM user WHERE Email='$email' LIMIT 1";
        $result_email = mysqli_query($conn, $query_email);
        $num_rows_email = mysqli_num_rows($result_email);

        if ($num_rows_username >= 1) {
            $error_message = "Username sudah terdaftar";
        } elseif ($num_rows_telp >= 1) {
            $error_message = "Nomor Telepon sudah terdaftar";
        } elseif ($num_rows_email >= 1) {
            $error_message = "Email sudah terdaftar";
        }
    }

    // Jika tidak ada error, insert data
    if (empty($error_message)) {
        $hashed_Password = password_hash($Password, PASSWORD_DEFAULT);  // Hash password
        $query = "INSERT INTO user (Username, Password, No_Telp, Email) VALUES ('$username', '$hashed_Password', '$Telp', '$email')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: ../login/login.html");  // Sesuaikan path jika perlu
            exit();
        } else {
            $error_message = "Pendaftaran gagal: " . mysqli_error($conn);
        }
    }

    // Jika ada error, redirect kembali ke register.html dengan error
    if (!empty($error_message)) {
        $encoded_error = urlencode($error_message);
        header("Location: register.html?error=" . $encoded_error);  // Redirect ke register.html
        exit();
    }
}

?>