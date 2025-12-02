<?php
session_start();
include '../connection/connection.php';

header('Content-Type: application/json');

// ambil menu dari DB
$query = "SELECT Id_Menu as id, Nama_Menu as name, Kategori as category, Deskripsi as description, Harga as price, Gambar as image /*, Popular as popular*/ FROM menu";
$result = mysqli_query($conn, $query);

$menuData = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Generate image path based on name
    $row['image'] = 'Gambar/' . $row['image'];
    // Convert popular ke boolean
    /*$row['popular'] = $row['popular'] == 1 ? true : false;*/

    // Convert price ke integer
    $row['price'] = (int)$row['price'];
    
    $menuData[] = $row;
}
echo json_encode($menuData);

?>