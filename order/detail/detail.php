<?php
session_start();
include '../../connection/connection.php';
header('Content-Type: application/json');

// Function untuk menghitung total
function calculateTotal($price, $quantity) {
    return $price * $quantity;
}

// Cek request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = isset($input['action']) ? $input['action'] : '';
    
    // ACTION: Calculate Total (hanya hitung, tidak insert)
    if ($action === 'calculate') {
        $menuId = isset($input['menuId']) ? (int)$input['menuId'] : 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
        
        if ($menuId <= 0 || $quantity <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid menu ID or quantity'
            ]);
            exit;
        }
        
        $query = "SELECT ID_Menu, Nama_Menu, Harga FROM menu WHERE ID_Menu = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $menuId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $price = (int)$row['Harga'];
            $total = calculateTotal($price, $quantity);
            
            echo json_encode([
                'success' => true,
                'menuId' => $row['ID_Menu'],
                'menuName' => $row['Nama_Menu'],
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
                'formattedPrice' => 'Rp ' . number_format($price, 0, ',', '.'),
                'formattedTotal' => 'Rp ' . number_format($total, 0, ',', '.')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Menu not found'
            ]);
        }
        
        mysqli_stmt_close($stmt);
        exit;
    }
    
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        exit;
    }
}

// GET: Ambil semua menu
else {
    $query = "SELECT ID_Menu as id, Nama_Menu as name, Kategori as category, 
              Deskripsi as description, Harga as price, Gambar as image 
              FROM menu";
    $result = mysqli_query($conn, $query);
    
    $menuData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['image'] = 'Gambar/' . $row['image'];
        $row['price'] = (int)$row['price'];
        
        $menuData[] = $row;
    }
    echo json_encode($menuData);
}
?>