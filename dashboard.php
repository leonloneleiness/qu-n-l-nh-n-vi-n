<?php
session_start();

// Kiểm tra nếu chưa đăng nhập, chuyển hướng về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? $username; // Sử dụng full_name nếu có
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng Điều Khiển</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #333; }
        .admin-panel { background-color: #f0f0ff; border: 1px solid #ccc; padding: 15px; margin-top: 20px; }
        .employee-info { background-color: #fff0f0; border: 1px solid #ccc; padding: 15px; margin-top: 20px; }
        a { color: #007bff; text-decoration: none; margin-right: 15px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>👋 Chào mừng, <?php echo htmlspecialchars($full_name); ?>!</h1>
    <p>Vai trò của bạn: <strong><?php echo strtoupper($role); ?></strong></p>

    <hr>
    
    <?php if ($role === 'admin'): ?>
        <div class="admin-panel">
            <h2>🛠️ Chức năng Quản Trị Viên</h2>
            <ul>
                <li><a href="manage_employees.php">Quản lý (Xem/Sửa/Xóa) Nhân viên</a></li>
                <li><a href="register.php?admin_mode=true">Tạo Tài Khoản/Nhân Viên Mới</a></li>
                <li><a href="admin_edit_profile.php?id=<?php echo $_SESSION['user_id']; ?>">Chỉnh sửa Thông tin Cá nhân</a></li>
            </ul>
        </div>
    <?php else: ?>
        <div class="employee-info">
            <h2>👤 Thông tin Cá nhân</h2>
            <p>Bạn có thể chỉnh sửa thông tin của mình:</p>
            <ul>
                <li><a href="edit_profile.php">Chỉnh sửa Thông tin Bản thân</a></li>
                <li><a href="change_password.php">Đổi Mật khẩu</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <hr>
    <p><a href="logout.php">Đăng Xuất</a></p>
</body>
</html>