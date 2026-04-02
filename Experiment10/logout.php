<?php
// 1. Always start the session first to access the data you want to delete
session_start();

// 2. Clear all session variables from RAM
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// Note: This is the "Advanced" way to ensure the browser forgets the Session ID.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Specifically kill the custom "user_theme" cookie we set earlier
// We do this by setting the expiration date to 1 hour ago
setcookie("user_theme", "", time() - 3600, "/");

// 5. Completely destroy the session on the server
session_destroy();

// 6. Redirect the user back to the high-level home page with a clean slate
header("Location: index.php");
exit();
?>