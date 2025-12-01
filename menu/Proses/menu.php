<?php
session_start();
include 'connection.php';

$query = "SELECT nama, deskripsi, harga * FROM menu";
$result = mysqli_query($conn, $query);

?>