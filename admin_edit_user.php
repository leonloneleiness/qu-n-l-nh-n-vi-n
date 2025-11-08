<?php
session_start();
require 'db_connect.php';

// 1. KIỂM TRA PHÂN QUYỀN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// 2. LẤY ID TÀI KHOẢN CẦN CHỈNH SỬA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_employees.php");
    exit();
}

$target_user_id = $_GET['id'];

// --- PHẦN 3: XỬ LÝ CẬP NHẬT (POST REQUEST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    
    // Kiểm tra vai trò hợp lệ
    if ($role !== 'admin' && $role !== 'employee') {
        $error = "Vai trò không hợp lệ.";
    } else {
        // Chuẩn bị câu lệnh UPDATE
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $role, $target_user_id);

        if ($stmt->execute()) {
            $message = "✅ Cập nhật thông tin cho tài khoản ID: {$target_user_id} thành công!";
        } else {
            $error = "❌ Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    }
}


// --- PHẦN 4: LẤY THÔNG TIN HIỆN TẠI ĐỂ HIỂN THỊ ---
$current_info_stmt = $conn->prepare("SELECT username, full_name, email, phone, role FROM users WHERE id = ?");
$current_info_stmt->bind_param("i", $target_user_id);
$current_info_stmt->execute();
$result = $current_info_stmt->get_result();
$user_data = $result->fetch_assoc();
$current_info_stmt->close();

// Kiểm tra xem tài khoản có tồn tại không
if (!$user_data) {
    $_SESSION['success_message'] = "❌ Lỗi: Tài khoản không tồn tại.";
    header("Location: manage_employees.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Sửa Thông Tin User: <?php echo htmlspecialchars($user_data['username']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 450px; margin: 50px auto; }
        h2 { color: #333; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #1e7e34; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Chỉnh Sửa Tài Khoản: <?php echo htmlspecialchars($user_data['username']); ?></h2>
    
    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="admin_edit_user.php?id=<?php echo $target_user_id; ?>">
        <label for="username">ID / Tên đăng nhập:</label>
        <input type="text" value="<?php echo htmlspecialchars($target_user_id . ' / ' . $user_data['username']); ?>" disabled>
        
        <label for="full_name">Họ và Tên:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="text" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>">

        <label for="phone">Số điện thoại:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>">

        <label for="role">Vai trò:</label>
        <select name="role" required>
            <option value="employee" <?php if ($user_data['role'] === 'employee') echo 'selected'; ?>>Nhân viên</option>
            <option value="admin" <?php if ($user_data['role'] === 'admin') echo 'selected'; ?>>Quản trị viên</option>
        </select>
            
        <button type="submit">Cập Nhật Tài Khoản</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        <a href="manage_employees.php">← Trở về Danh sách Quản lý</a>
    </p>
</div>

</body>
</html>