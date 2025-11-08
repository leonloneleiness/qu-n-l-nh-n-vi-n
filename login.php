<?php
session_start();
// Chuyển hướng nếu người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require 'db_connect.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu và làm sạch
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Dùng Prepared Statement để ngăn chặn SQL Injection
    $stmt = $conn->prepare("SELECT id, password, role, full_name FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role, $full_name);

    if ($stmt->num_rows == 1) {
        $stmt->fetch();
        
        // 2. Xác minh mật khẩu Băm (Hash)
        if (password_verify($password, $hashed_password)) {
            // Đăng nhập thành công, lưu thông tin vào Session
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;

            // Chuyển hướng đến trang quản lý chính
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập - Quản Lý Nhân Viên</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 300px; }
        h2 { text-align: center; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 8px 0 15px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #4CAF50; color: white; padding: 14px 20px; margin: 8px 0; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #45a049; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Đăng Nhập Hệ Thống</h2>
    
    <?php if (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username"><b>Tên đăng nhập</b></label>
        <input type="text" placeholder="Nhập Username" name="username" required>

        <label for="password"><b>Mật khẩu</b></label>
        <input type="password" placeholder="Nhập Mật khẩu" name="password" required>
            
        <button type="submit">Đăng Nhập</button>
    </form>
    <p style="text-align: center; margin-top: 20px;">
        Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a>
    </p>
</div>

</body>
</html>