<?php
include 'connection.php';

if (isset($_POST['daftar'])){
    $username = htmlentities(strip_tags(trim ($_POST['username'])));
    $Password = htmlentities(strip_tags(trim ($_POST['Password'])));
    $Telp = htmlentities(strip_tags(trim ($_POST['No_Telp'])));
    $email = htmlentities(strip_tags(trim ($_POST['Email'])));

    $error_massage = "";


    //validasi input kosong
    if (empty($username) && empty($Password) && empty($Telp) && empty($email)){
        $error_massage = "Semua data harus diisi";
    } elseif (!$username){
        $error_massage = "Username tidak boleh kosong";
    } elseif (!$Password){
        $error_massage = "Password tidak boleh kosong";
    } elseif (!$Telp){
        $error_massage = "No Telp tidak boleh kosong";
    } elseif (!$email){
        $error_massage = "Email tidak boleh kosong";
}

    //validasi format input
    if (is_numeric($Telp)){
        $error_massage = "Nomor Telepon harus berupa angka";
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
        $error_massage = "Format email tidak valid";
    }

    //cek username sudah ada atau belum
    $username = mysqli_real_escape_string($conn, $username);
    $query_username = "SELECT * FROM user WHERE Username='$username' LIMIT 1";
    $result_username = mysqli_query($conn, $query_username);
    $num_rows_username = mysqli_num_rows($result_username);


    //cek No_Telp sudah ada atau belum
    $Telp = mysqli_real_escape_string($conn, $Telp);
    $query_telp = "SELECT * FROM user WHERE No_Telp='$Telp' LIMIT 1";
    $result_telp = mysqli_query($conn, $query_telp);
    $num_rows_telp = mysqli_num_rows($result_telp);


    //cek Email sudah ada atau belum
    $email = mysqli_real_escape_string($conn, $email);
    $query_email = "SELECT * FROM user WHERE Email='$email' LIMIT 1";
    $result_email = mysqli_query($conn, $query_email);  
    $num_rows_email = mysqli_num_rows($result_email);

    if ($num_rows_username >= 1){
        $error_massage = "Username sudah terdaftar";
    }

    if ($num_rows_telp >= 1){
        $error_massage = "Nomor Telepon sudah terdaftar";
    }

    if ($num_rows_email >= 1){
        $error_massage = "Email sudah terdaftar";
    }

    if ($error_massage == ""){
        //insert data ke database
        $username  = mysqli_real_escape_string($conn, $username);
        $hashed_Password = password_hash($Password, PASSWORD_DEFAULT);
        $Telp      = mysqli_real_escape_string($conn, $Telp);
        $email     = mysqli_real_escape_string($conn, $email);
    

    $query = "INSERT INTO user (Username, Password, No_Telp, Email) VALUES ('$username', '$hashed_Password', '$Telp', '$email')";
    $result = mysqli_query($conn, $query);

    if ($result){
        $massage = "Pendaftaran berhasil!";
        $massage = urlencode($massage);
        header ("Location: index.php?massage=$massage");
    } else {
        die ("Pendaftaran gagal: " . mysqli_error($conn));
    }
}
}
?>