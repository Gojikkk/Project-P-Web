<?php
session_start();
include '../../connection/connection.php';

// ambil menu dari DB
$query = "SELECT Id_Menu as id, Nama as name, Kategori as category, Deskripsi as description, Harga as price, Popular as popular FROM menu";
$result = mysqli_query($conn, $query);

$menuData = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Generate image path based on name
    $imageName = strtolower(str_replace(' ', '', $row['name'])) . '.jpg';
    $row['image'] = '/menu/Gambar/' . $imageName;
    $menuData[] = $row;
}
?>