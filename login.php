<?php
session_start();
include 'connection.php';

if (isset($_POST['login'])) {
    
    //ambil data dari form
    $username = htmlentities(strip_tags(trim ($_POST['username'])));
    $Password = htmlentities(strip_tags(trim ($_POST['Password'])));

    $error_massage = "";

    //cek data di database
    $username = mysqli_real_escape_string($conn, $username);
    $Password = mysqli_real_escape_string($conn, $Password);
    $query = "SELECT * FROM user WHERE username='$username' AND Password='$Password' LIMIT 1";
    $result = mysqli_query($conn, $query);

    $num_rows = mysqli_num_rows($result);

    //validasi input
    if (empty($username) && empty($Password)){
        $error_massage = "Username dan Password tidak boleh kosong";
    }

    if (!$username){
        $error_massage = "Username tidak boleh kosong";
    }

    if (!$Password){
        $error_massage = "Password tidak boleh kosong";
    }

    //kalo data nya ada
    if ($num_rows >= 1){
        $user = mysqli_fetch_assoc($result);

        if (password_verify($Password, $user['Password'])) {
            //set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
        
        header("Location: home.php");
        exit ();
    } else {
        $error_massage = "Username atau Password salah atau sudah ada";
    }
    
}
}
?>

