<?php
session_start();
include '../../connection/connection.php';
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success"=>false,"message"=>"User belum login"]);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false,"message"=>"Invalid request method"]);
    exit;
}

// Ambil data dari POST
$user_id = $_SESSION['ID_User'] ?? 0;
$menu_id = intval($_POST['menu_id'] ?? 0);
$jumlah_pesanan = intval($_POST['quantity'] ?? 1);
$total_harga = intval($_POST['total'] ?? 0);
$order_type = $_POST['order_type'] ?? 'takeaway';
$nomor_meja = $_POST['table_number'] ?? null;
$alamat = $_POST['address'] ?? null;
$payment_method = $_POST['payment_methode'] ?? '';
$notes = $_POST['notes'] ?? '';
$tanggal_pesanan = date('Y-m-d H:i:s');

// Validasi minimal
if ($user_id <= 0 || $menu_id <= 0 || $total_harga <= 0 || empty($payment_method)) {
    echo json_encode(["success"=>false,"message"=>"Data tidak lengkap"]);
    exit;
}

try {
    // ===== Simpan pesanan =====
    $stmt = $conn->prepare("INSERT INTO pesanan 
        (ID_User, ID_Menu, Jumlah_Pesanan, Tanggal_Pesanan, Total_Harga, Order_Type, Nomor_Meja, Alamat, Payment_Methode, Notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iiisisssss",
        $user_id, $menu_id, $jumlah_pesanan, $tanggal_pesanan, $total_harga, $order_type, $nomor_meja, $alamat, $payment_method, $notes
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan pesanan");
    }

    $pesanan_id = $conn->insert_id;
    $stmt->close();

    // ===== Update stok menu =====
    $updateStmt = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE ID_Menu = ? AND stok >= ?");
    $updateStmt->bind_param("iii", $jumlah_pesanan, $menu_id, $jumlah_pesanan);
    if (!$updateStmt->execute()) {
        throw new Exception("Gagal update stok");
    }

    // Cek apakah stok cukup
    if ($updateStmt->affected_rows === 0) {
        // rollback pesanan jika stok tidak cukup
        $conn->query("DELETE FROM pesanan WHERE ID_Pesanan = $pesanan_id");
        throw new Exception("Stok tidak cukup");
    }

    $updateStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Pesanan berhasil dibuat",
        "pesanan_id" => $pesanan_id,
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan: ".$e->getMessage()]);
}

$conn->close();
?>
