<?php
$host = "localhost";
$user = "root";  // Sesuaikan dengan user database
$pass = "";  // Jika ada password, isi di sini
$dbname = "bps_menu";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
