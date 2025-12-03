<?php
session_start();
include '../../connection/connection.php';

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        "success" => false,
        "message" => "User belum login"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_menu            = $_POST['ID_Menu'];
    $jumlah_pesanan     = $_POST['Jumlah_Pesanan'];
    $order_type         = $_POST['Order_Type'];
    $Nomor_Meja         = $_POST['Nomor_Meja'] ?? '';
    $alamat_pengantaran = $_POST['Alamat_Pengantaran'] ?? '';
    $notes              = $_POST['Notes'] ?? '';
    $servicefee         = 5000;

    // Ambil data menu
    $query_menu = "SELECT * FROM menu WHERE ID_Menu = '$id_menu'";
    $result = mysqli_query($conn, $query_menu);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(["success" => false, "message" => "Menu tidak ditemukan"]);
        exit;
    }

    $menu = mysqli_fetch_assoc($result);
    $harga_menu = $menu['Harga'];

    // Cek stok
    if ($jumlah_pesanan > $menu['Stok']) {
        echo json_encode(["success" => false, "message" => "Stok tidak mencukupi"]);
        exit;
    }

    // Validasi order type
    if ($order_type === "dinein" && empty($Nomor_Meja)) {
        echo json_encode(["success" => false, "message" => "Nomor Meja wajib diisi"]);
        exit;
    }

    if ($order_type === "delivery" && empty($alamat_pengantaran)) {
        echo json_encode(["success" => false, "message" => "Alamat pengantaran wajib diisi"]);
        exit;
    }

    // Hitung total harga
    $total_harga = ($harga_menu * $jumlah_pesanan) + $servicefee;

    // RETURN TANPA INSERT DB
    echo json_encode([
        "success" => true,
        "message" => "Pesanan diproses tanpa disimpan",
        "menuName" => $menu['Nama_Menu'],
        "subtotal" => $harga_menu * $jumlah_pesanan,
        "serviceFee" => $servicefee,
        "total" => $total_harga
    ]);
}
?>
