<?php
session_start();
include '../../connection/connection.php';
header('Content-Type: application/json');

// Log request method
error_log("=== MENU.PHP REQUEST ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Function untuk menghitung total
function calculateTotal($price, $quantity) {
    return $price * $quantity;
}

// Cek request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    error_log("Raw POST Input: " . $rawInput);
    
    $input = json_decode($rawInput, true);
    error_log("Decoded Input: " . print_r($input, true));
    
    $action = isset($input['action']) ? $input['action'] : '';
    error_log("Action: " . $action);
    
    // ACTION 1: Calculate Total
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
        exit;
    }
    
    // ACTION 2: Insert Order
    elseif ($action === 'order') {
        // Log untuk debugging
        error_log("ORDER ACTION - Session ID: " . session_id());
        error_log("ORDER ACTION - Session Data: " . print_r($_SESSION, true));
        
        $menuId = isset($input['menuId']) ? (int)$input['menuId'] : 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        error_log("ORDER ACTION - User ID: " . ($userId ?? 'NULL'));
        error_log("ORDER ACTION - Menu ID: $menuId");
        error_log("ORDER ACTION - Quantity: $quantity");
        
        if ($menuId <= 0 || $quantity <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid menu ID or quantity',
                'debug' => [
                    'menuId' => $menuId,
                    'quantity' => $quantity
                ]
            ]);
            exit;
        }
        
        // FIXED: Check if user is logged in
        if ($userId === null || $userId === '' || $userId === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User tidak login. Silakan login terlebih dahulu.',
                'needLogin' => true,
                'debug' => [
                    'sessionId' => session_id(),
                    'userId' => $userId,
                    'sessionData' => $_SESSION
                ]
            ]);
            exit;
        }
        
        // Ambil harga menu
        $query = "SELECT Harga, Nama_Menu FROM menu WHERE ID_Menu = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $menuId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $price = (int)$row['Harga'];
            $menuName = $row['Nama_Menu'];
            $total = calculateTotal($price, $quantity);
            
            // Insert ke tabel orders (sesuaikan dengan struktur tabel Anda)
            $insertQuery = "INSERT INTO pesanan 
                (ID_User, ID_Menu, Jumlah_Pesanan, Tanggal_Pesanan, Total_Harga)
                VALUES (?, ?, ?, NOW(), ?)";

            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "iiii", $userId, $menuId, $quantity, $total);

            if (mysqli_stmt_execute($insertStmt)) {
                $orderId = mysqli_insert_id($conn);
                
                error_log("ORDER SUCCESS - Order ID: $orderId");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Order berhasil ditambahkan',
                    'orderId' => $orderId,
                    'menuId' => $menuId,
                    'menuName' => $menuName,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                    'formattedTotal' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'tanggalPesanan' => $currentDate
                ]);
            } else {
                $errorMsg = mysqli_error($conn);
                error_log("ORDER ERROR - MySQL Error: $errorMsg");
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menambahkan order: ' . $errorMsg,
                    'debug' => [
                        'error' => $errorMsg,
                        'userId' => $userId,
                        'menuId' => $menuId,
                        'quantity' => $quantity,
                        'total' => $total
                    ]
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Menu tidak ditemukan'
            ]);
        }
        exit;
    }
    
    else {
        error_log("INVALID ACTION: " . $action);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action',
            'receivedAction' => $action,
            'receivedData' => $input,
            'requestMethod' => $_SERVER['REQUEST_METHOD']
        ]);
        exit;
    }
}

// GET: Ambil semua menu
else {
    $query = "SELECT ID_Menu as id, Nama_Menu as name, Kategori as category, Deskripsi as description, Harga as price, Gambar as image FROM menu";
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