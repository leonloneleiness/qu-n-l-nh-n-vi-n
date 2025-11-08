<?php
session_start();

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu muốn xóa hoàn toàn session, cũng hủy cookie session.
// Đây là bước bổ sung để đảm bảo an toàn.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy phiên làm việc
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>