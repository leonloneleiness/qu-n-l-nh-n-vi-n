<?php
session_start();
require 'db_connect.php';

// 1. KIỂM TRA PHÂN QUYỀN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải Admin hoặc chưa đăng nhập, chuyển hướng về dashboard (hoặc login)
    header("Location: dashboard.php");
    exit();
}

$message = '';
// Lấy thông báo thành công từ các hành động khác (ví dụ: sau khi xóa)
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Xóa message sau khi hiển thị
}


// 2. TRUY VẤN DỮ LIỆU
$sql = "SELECT id, username, full_name, role, email, phone FROM users ORDER BY role DESC, full_name ASC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Nhân Viên - ADMIN</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin: 20px auto; max-width: 90%; }
        h2 { color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .action-link { margin-right: 10px; text-decoration: none; padding: 5px 10px; border-radius: 3px; }
        .edit { background-color: #ffc107; color: #333; }
        .delete { background-color: #dc3545; color: white; }
        .role-admin { font-weight: bold; color: #dc3545; }
        .role-employee { color: #007bff; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠️ Quản Lý Tất Cả Tài Khoản</h2>
    <p><a href="dashboard.php">← Trở về Bảng Điều Khiển</a> | <a href="register.php?admin_mode=true">➕ Tạo Tài Khoản Mới</a></p>
    
    <?php if (!empty($message)): ?>
        <p class="success">✅ <?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ và Tên</th>
                    <th>Vai trò</th>
                    <th>Email</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td>
                            <span class="<?php echo ($row['role'] === 'admin') ? 'role-admin' : 'role-employee'; ?>">
                                <?php echo strtoupper($row['role']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <a href="admin_edit_user.php?id=<?php echo $row['id']; ?>" class="action-link edit">Sửa</a>
                            
                            <?php if ($row['id'] != $_SESSION['user_id']): // Không cho phép Admin tự xóa chính mình ?>
                                <a href="delete_user.php?id=<?php echo $row['id']; ?>" 
                                   class="action-link delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản <?php echo htmlspecialchars($row['username']); ?> không?');">
                                    Xóa
                                </a>
                            <?php else: ?>
                                <span style="color:#aaa;">(Không thể xóa)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có tài khoản nào trong hệ thống.</p>
    <?php endif; ?>

</div>

</body>
</html>