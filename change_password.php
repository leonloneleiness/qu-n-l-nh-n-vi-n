<?php
session_start();
require 'db_connect.php';

// Kiểm tra nếu chưa đăng nhập, chuyển hướng
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Kiểm tra mật khẩu mới và xác nhận mật khẩu
    if ($new_password !== $confirm_password) {
        $error = "❌ Mật khẩu mới và Xác nhận mật khẩu không khớp.";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Mật khẩu mới phải có ít nhất 6 ký tự.";
    } else {
        // 2. Lấy mật khẩu Băm (Hash) hiện tại từ database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            // 3. Xác minh mật khẩu cũ
            if (password_verify($current_password, $user['password'])) {
                
                // 4. Băm mật khẩu mới và cập nhật vào database
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = "✅ Đổi mật khẩu thành công!";
                } else {
                    $error = "❌ Lỗi cập nhật mật khẩu: " . $conn->error;
                }
                $update_stmt->close();

            } else {
                $error = "❌ Mật khẩu cũ không chính xác.";
            }
        } else {
            // Lỗi hiếm gặp: không tìm thấy user đang đăng nhập
            $error = "❌ Lỗi hệ thống. Vui lòng thử đăng nhập lại.";
            session_destroy();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi Mật Khẩu</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 400px; margin: 50px auto; }
        h2 { color: #333; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #ff6f00; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #e65100; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Đổi Mật Khẩu Cá Nhân</h2>
    
    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="change_password.php">
        <label for="current_password">Mật khẩu CŨ:</label>
        <input type="password" name="current_password" required>

        <hr>
        
        <label for="new_password">Mật khẩu MỚI:</label>
        <input type="password" name="new_password" required>

        <label for="confirm_password">Xác nhận Mật khẩu MỚI:</label>
        <input type="password" name="confirm_password" required>
            
        <button type="submit">Đổi Mật Khẩu</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        <a href="dashboard.php">← Trở về Bảng Điều Khiển</a>
    </p>
</div>

</body>
</html>