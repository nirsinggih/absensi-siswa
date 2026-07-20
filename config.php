<?php
$host = "localhost";
$user = "batukandik";
$pass = "batukandik";
$db   = "batukandik";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
