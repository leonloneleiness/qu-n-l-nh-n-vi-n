<?php
session_start();
require 'db_connect.php';

// 1. KIỂM TRA PHÂN QUYỀN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// 2. KIỂM TRA ID CẦN XÓA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_employees.php");
    exit();
}

$user_to_delete_id = $_GET['id'];

// Không cho phép Admin tự xóa tài khoản của chính mình
if ($user_to_delete_id == $_SESSION['user_id']) {
    $_SESSION['success_message'] = "❌ Lỗi: Bạn không thể tự xóa tài khoản của chính mình!";
    header("Location: manage_employees.php");
    exit();
}

// 3. THỰC HIỆN XÓA TỪ DATABASE
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_to_delete_id);

if ($stmt->execute()) {
    // Lưu thông báo thành công vào session để hiển thị trên trang manage_employees.php
    $_SESSION['success_message'] = "✅ Xóa tài khoản có ID " . htmlspecialchars($user_to_delete_id) . " thành công!";
} else {
    $_SESSION['success_message'] = "❌ Lỗi khi xóa tài khoản: " . $conn->error;
}

$stmt->close();

// 4. CHUYỂN HƯỚNG
header("Location: manage_employees.php");
exit();
?>