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
    // Ambil data dari session dan form
    $id_user = $_SESSION['Id_user'];
    $name = $_SESSION['Username'];
    $no_telepon = $_SESSION['No_Telepon'];


// Ambil data dari form
    $id_menu = htmlentities(strip_tags(trim ($_POST['Id_Menu'])));
    $jumlah_pesanan = htmlentities(strip_tags(trim ($_POST['Jumlah_Pesanan'])));
    $tanggal_pesan = date('Y-m-d H:i:s');
    $order_type = htmlentities(strip_tags(trim ($_POST['Order_Type']))); // Dine-in atau Takeaway
    $Nomor_Meja = htmlentities(strip_tags(trim ($_POST['Nomor_Meja']))); // Jika Dine-in
    $alamat_pengantaran = htmlentities(strip_tags(trim ($_POST['Alamat_Pengantaran']))); // Jika Takeaway
    $notes = htmlentities(strip_tags(trim ($_POST['Notes'])));

    $servicefee = 5000; // Biaya layanan tetap
    $harga_sementara = 0;

    // Validasi input
    $error_massage = "";

    // Cek stok dan harga makanan
if (empty($error_massage)){
    $query_menu = "Select * from menu where Id_Menu = '$id_menu'";
    $result_menu = mysqli_query($conn, $query_menu);

    if (mysqli_num_rows($result_menu) > 0){
        $menu = mysqli_fetch_assoc($result_menu);
        $stok_menu = $menu['Stok'];
        $harga_menu = $menu['Harga'];
    } else {
        $error_massage = "Menu tidak ditemukan";
    }
}

    
    if (empty($error_massage)){
        if ($jumlah_pesanan > $stok_menu){
            $error_massage = "Stok tidak mencukupi.";
        }
    }


    if (empty($error_massage)){
        if ($order_type === 'Dine-in' ){
        if (empty($Nomor_Meja)){
            $error_massage = "Nomor Meja harus diisi";
        }
    } elseif ($order_type === 'Takeaway' ){
        if (empty($alamat_pengantaran)){
            $error_massage = "Silahkan isi alamat pengantaran terlebih dahulu";
        }
    }
    } 

    if (empty ($error_massage)){
        $harga_sementara = $harga_menu * $jumlah_pesanan;
    }
    

    //fungsi hitung total harga dan simpan ke database
    if (empty ($error_massage)){
        $total_harga = $harga_sementara + $servicefee;
        $query_pesan = "Insert into pesan (Id_user, Id_Menu, Jumlah_Pesanan, Tanggal_Pesan, Order_Type, Nomor_Meja, Alamat_Pengantaran, Notes, Total_Harga) values ('$id_user', '$id_menu', '$jumlah_pesanan', '$tanggal_pesan', '$order_type', '$Nomor_Meja', '$alamat_pengantaran', '$notes', '$total_harga')";
    

    
    if(mysqli_query($conn, $query_pesan)){
        //update stok mmenu
        $new_stok_menu = $stok_menu - $jumlah_pesanan;
        $query_update_menu = "Update menu set Stok = '$new_stok_menu' where Id_Menu = '$id_menu'";
        mysqli_query($conn, $query_update_menu);
}
    }


    if ($error_massage == ""){
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='menu.php';</script>";
    } else {
        echo "<script>alert('Error: $error_massage'); window.location.href='menu.php';</script>";
    }

}


?>