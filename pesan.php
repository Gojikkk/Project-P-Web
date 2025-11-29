<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['Logged In']) || $_SESSION['Logged In'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['pesan'])){
    $id_user = $_SESSION['Id_user'];

    $id_makanan = htmlentities(strip_tags(trim ($_POST['id_makanan'])));
    $id_minuman = htmlentities(strip_tags(trim ($_POST['id_minuman'])));
    $jumlah_pesanan = htmlentities(strip_tags(trim ($_POST['Jumlah_Pesanan'])));
    $tanggal_pesan = date('Y-m-d H:i:s');

    $error_massage = "";
    if (empty($id_makanan) && empty($id_minuman) && empty($jumlah_pesanan)){
        $error_massage = "Semua data harus diisi";
    } elseif (!$id_makanan){
        $error_massage = "ID Makanan tidak boleh kosong";
    } elseif (!$id_minuman){
        $error_massage = "ID Minuman tidak boleh kosong";
    } elseif (!$jumlah_pesanan){
        $error_massage = "Jumlah Pesanan tidak boleh kosong";
    }

    

    if (!is_numeric($jumlah_pesanan)){
        $error_massage = "Jumlah Pesanan harus berupa angka";
    }
}

    $query_makanan = "Select * from makanan where id_makanan = '$id_makanan'";
    $result_makanan = mysqli_query($conn, $query_makanan);

    if (my sqli_num_rows($result_makanan) > 0) {
        $data_makanan = mysqli_fetch_assoc($result_makanan);
        $harga_makanan = $data_makanan['Harga'];
        $stok_makanan = $data_makanan['Stok'];

        if ($jumlah_pesanan > $stok_makanan) {
            $error_massage = "Stok makanan tidak mencukupi";
        }else{
            $error_massage = "Makanan tidak terdapat pada menu"
        }
    }


    $query_minuman = "Select * from minuman where id_minuman = '$id_minuman'";
    $result_minuman = mysqli_query($conn, $query_minuman);  

    if (mysqli_num_rows($result_minuman) > 0) {
        $data_minuman = mysqli_fetch_assoc($result_minuman);
        $harga_minuman = $data_minuman['Harga'];
        $stok_minuman = $data_minuman['Stok'];
        if ($jumlah_pesanan > $stok_minuman) {
            $error_massage = "Stok minuman tidak mencukupi";
        }else{
            $error_massage = "Minuman tidak terdapat pada menu"
        }
}

?>