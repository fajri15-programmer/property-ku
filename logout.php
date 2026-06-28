<?php
session_start();

// KEAMANAN: Bersihkan semua data session dengan benar
$_SESSION = array();

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// KEAMANAN: Redirect dengan header, bukan <script> yang bisa di-block browser
header("Location: login.php");
exit();
?>