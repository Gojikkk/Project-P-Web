<?php
session_start();
include '../connection/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'];
    $menuId = $input['menuId'];
    $quantity = $input['quantity'];

    // Ambil data menu
    $q = mysqli_query($conn, "SELECT * FROM menu WHERE Id_Menu = '$menuId'");
    $item = mysqli_fetch_assoc($q);

    if (!$item) {
        echo json_encode([
            "success" => false,
            "message" => "Menu tidak ditemukan"
        ]);
        exit;
    }

    // Hitung total
    $total = (int)$item['Harga'] * (int)$quantity;

    // ACTION: CALCULATE
    if ($action === 'calculate') {
        echo json_encode([
            "success" => true,
            "formattedTotal" => "Rp " . number_format($total, 0, ',', '.')
        ]);
        exit;
    }

    // ACTION: ORDER
    if ($action === 'order') {

        // Misalkan tabel order kamu bernama orders
        mysqli_query($conn, "INSERT INTO orders (Id_Menu, quantity, total) 
                             VALUES ('$menuId', '$quantity', '$total')");

        $orderId = mysqli_insert_id($conn);

        echo json_encode([
            "success" => true,
            "menuName" => $item['Nama_Menu'],
            "quantity" => $quantity,
            "formattedTotal" => "Rp " . number_format($total, 0, ',', '.'),
            "orderId" => $orderId
        ]);
        exit;
    }
}


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
    $row['id'] = (int)$row['id'];   
    $row['price'] = (int)$row['price']; 
    
    $menuData[] = $row;
}
echo json_encode($menuData);

?>