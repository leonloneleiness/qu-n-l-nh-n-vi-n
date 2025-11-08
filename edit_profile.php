<?php
session_start();
// Chuyển hướng về trang login nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// --- PHẦN 1: XỬ LÝ CẬP NHẬT THÔNG TIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Chỉ cho phép cập nhật các trường thông tin cơ bản
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);

    if ($stmt->execute()) {
        $message = "✅ Cập nhật thông tin cá nhân thành công!";
        // Cập nhật lại tên trong session nếu cần
        $_SESSION['full_name'] = $full_name;
    } else {
        $error = "❌ Lỗi khi cập nhật: " . $conn->error;
    }
    $stmt->close();
}


// --- PHẦN 2: LẤY THÔNG TIN HIỆN TẠI ĐỂ HIỂN THỊ TRONG FORM ---
$current_info_stmt = $conn->prepare("SELECT username, full_name, email, phone FROM users WHERE id = ?");
$current_info_stmt->bind_param("i", $user_id);
$current_info_stmt->execute();
$result = $current_info_stmt->get_result();
$user_data = $result->fetch_assoc();
$current_info_stmt->close();

// Nếu không tìm thấy dữ liệu (lỗi nghiêm trọng)
if (!$user_data) {
    header("Location: logout.php"); // Đăng xuất để đảm bảo an toàn
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Thông Tin Cá Nhân</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 400px; margin: 50px auto; }
        h2 { color: #333; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Chỉnh Sửa Thông Tin Cá Nhân</h2>
    
    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_profile.php">
        <label for="username">Tên đăng nhập (Không thể thay đổi):</label>
        <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
        
        <label for="full_name">Họ và Tên:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="text" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>">

        <label for="phone">Số điện thoại:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
            
        <button type="submit">Lưu Thay Đổi</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        <a href="dashboard.php">← Trở về Bảng Điều Khiển</a>
    </p>
</div>

</body>
</html>