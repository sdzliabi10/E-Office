<?php
/******************************************************** 
Halaman ini merupakan halaman logout, dimana kita menghapus session yang ada.
*********************************************************/

session_start();

// Hapus semua data session
session_unset();
session_destroy();

setcookie('user', "", time() + time() - 3600, "/"); // 86400 = 1 day
setcookie('pass', "", time() + time() - 3600, "/"); // You should not store the password like this in a real application


// Redirect ke halaman login.php dengan pesan berhasil logout
header("Location: index.php?action=logout");
?>