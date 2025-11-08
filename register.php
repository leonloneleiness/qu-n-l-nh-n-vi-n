<?php
session_start();
require 'db_connect.php';

$message = '';
$error = '';

// Kiểm tra xem có phải Admin đang tạo tài khoản không
$isAdminCreating = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    
    // Nếu là Admin tạo, cho phép chọn vai trò, nếu không, mặc định là 'employee'
    $role = 'employee'; 
    if ($isAdminCreating && isset($_POST['role'])) {
        $role = $_POST['role'] === 'admin' ? 'admin' : 'employee';
    }

    // 1. Băm (Hash) mật khẩu trước khi lưu vào database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 2. Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "Tên đăng nhập này đã tồn tại. Vui lòng chọn tên khác.";
    } else {
        // 3. Thực hiện chèn dữ liệu
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);

        if ($insert_stmt->execute()) {
            if ($isAdminCreating) {
                 $message = "Đã tạo thành công tài khoản **" . htmlspecialchars($username) . "** với vai trò **" . strtoupper($role) . "**. <a href='dashboard.php'>Trở về Bảng Điều Khiển</a>";
            } else {
                 $message = "Đăng ký thành công! Vui lòng <a href='login.php'>Đăng nhập</a>.";
            }
        } else {
            $error = "Lỗi đăng ký: " . $conn->error;
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isAdminCreating ? "Tạo Tài Khoản Mới" : "Đăng Ký Tài Khoản"; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 400px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 14px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo $isAdminCreating ? "Tạo Tài Khoản Mới" : "Đăng Ký Tài Khoản Nhân Viên"; ?></h2>
    
    <?php if (!empty($error)): ?>
        <p class="error">❌ <?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="success">✅ <?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="full_name">Họ và Tên:</label>
        <input type="text" name="full_name" placeholder="Nhập Họ và Tên" required>

        <label for="username">Tên đăng nhập:</label>
        <input type="text" name="username" placeholder="Nhập Username" required>

        <label for="password">Mật khẩu:</label>
        <input type="password" name="password" placeholder="Nhập Mật khẩu" required>

        <?php if ($isAdminCreating): ?>
            <label for="role">Vai trò:</label>
            <select name="role">
                <option value="employee">Nhân viên</option>
                <option value="admin">Quản trị viên</option>
            </select>
        <?php endif; ?>
            
        <button type="submit"><?php echo $isAdminCreating ? "Tạo Tài Khoản" : "Đăng Ký"; ?></button>
    </form>

    <?php if (!$isAdminCreating): ?>
        <p style="text-align: center; margin-top: 20px;">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </p>
    <?php endif; ?>
    
    <?php if ($isAdminCreating): ?>
        <p style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php">Quay lại Bảng Điều Khiển</a>
        </p>
    <?php endif; ?>
</div>

</body>
</html>