<?php
session_start();
include 'connection.php';


// Cek apakah user sudah login
if (!isset($_SESSION['Logged In']) || $_SESSION['Logged In'] !== true) {
    header("Location: login.php");
    exit;
}


// Proses pemesanan
if (isset($_POST['pesan'])){
    $id_user = $_SESSION['Id_user'];

    $id_makanan = htmlentities(strip_tags(trim ($_POST['id_makanan'])));
    $id_minuman = htmlentities(strip_tags(trim ($_POST['id_minuman'])));
    $jumlah_pesanan = htmlentities(strip_tags(trim ($_POST['Jumlah_Pesanan'])));
    $tanggal_pesan = date('Y-m-d H:i:s');

    // Validasi input
    $error_massage = "";
    if (empty($id_makanan)){
        $error_massage = "Pilih makanan terlebih dahulu";
    } elseif (empty($id_minuman)){
        $error_massage = "Pilih minuman terlebih dahulu";
    } elseif (empty($jumlah_pesanan)){
        $error_massage = "Jumlah Pesanan tidak boleh kosong";
    } elseif ($jumlah_pesanan <= 0){
        $error_massage = "Jumlah Pesanan harus lebih dari 0";
    } elseif (!is_numeric($jumlah_pesanan)){
        $error_massage = "Jumlah Pesanan harus berupa angka";
    }

    // Cek stok dan harga makanan
if (empty($error_massage)){
    $query_makanan = "Select * from makanan where id_makanan = '$id_makanan'";
    $result_makanan = mysqli_query($conn, $query_makanan);
    if (mysqli_num_rows($result_makanan) > 0) {
        $data_makanan = mysqli_fetch_assoc($result_makanan);
        $harga_makanan = $data_makanan['Harga'];
        $stok_makanan = $data_makanan['Stok'];

        if ($jumlah_pesanan > $stok_makanan) {
            $error_massage = "Stok makanan tidak mencukupi";
        }
    } else {
        $error_massage = "Makanan tidak terdapat pada menu";
    }
}

// Cek stok dan harga minuman
    if(empty($error_massage)){
    $query_minuman = "Select * from minuman where id_minuman = '$id_minuman'";
    $result_minuman = mysqli_query($conn, $query_minuman);

    if (mysqli_num_rows($result_minuman) > 0) {
        $data_minuman = mysqli_fetch_assoc($result_minuman);
        $harga_minuman = $data_minuman['Harga'];
        $stok_minuman = $data_minuman['Stok'];

        if ($jumlah_pesanan > $stok_minuman) {
            $error_massage = "Stok minuman tidak mencukupi";
        }
        
    }else{
            $error_massage = "Minuman tidak terdapat pada menu";
        }
    }


    //fungsi hitung total harga dan simpan ke database
    if (empty ($error_massage)){
        $total_harga = ($harga_makanan + $harga_minuman) * $jumlah_pesanan;

        $query_pesan = "Insert into pesan (Id_user, Id_makanan, Id_minuman, Jumlah_Pesanan, Tanggal_Pesanan, Total_Harga) values ('$id_user', '$id_makanan', '$id_minuman', '$jumlah_pesanan', '$tanggal_pesan', '$total_harga')";
    }



    if(mysqli_query($conn, $query_pesan)){
        //update stok makanan
        $new_stok_makanan = $stok_makanan - $jumlah_pesanan;
        $query_update_makanan = "Update makanan set Stok = '$new_stok_makanan' where id_makanan = '$id_makanan'";
        mysqli_query($conn, $query_update_makanan);

        //update stok minuman
        $new_stok_minuman = $stok_minuman - $jumlah_pesanan;
        $query_update_minuman = "Update minuman set Stok = '$new_stok_minuman' where id_minuman = '$id_minuman'";
        mysqli_query($conn, $query_update_minuman);
}

    if ($error_massage == ""){
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='menu.php';</script>";
    } else {
        echo "<script>alert('Error: $error_massage'); window.location.href='menu.php';</script>";
    }

}


?>