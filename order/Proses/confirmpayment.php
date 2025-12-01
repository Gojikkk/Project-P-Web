<?php
include '/connection/connection.php';

$query = "SELECT nama_menu, order_type, jumlah_pesanan, harga FROM pesanan";
$result = mysqli_query($conn, $query);

?>