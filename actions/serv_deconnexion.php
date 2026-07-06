<?php
session_start();

// Famafana ny session rehetra
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirection mankany amin'ny pejy fidirana (na dashboard izay tiana hiverenana)
header("Location: ../pages/index.php");
exit();
?>