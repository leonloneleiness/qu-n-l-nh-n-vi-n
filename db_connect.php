<?php
$servername = "localhost";
$username = "root"; // <--- THAY ĐỔI NẾU KHÁC
$password = "";     // <--- THAY ĐỔI NẾU KHÁC
$dbname = "employee_management"; // ĐÃ CHẠY THÀNH CÔNG

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu kết nối thất bại, dừng chương trình và in ra lỗi
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Đặt charset UTF8 để hiển thị tiếng Việt
$conn->set_charset("utf8mb4");

// Không cần đóng kết nối ở đây, nó sẽ được đóng tự động khi script kết thúc
?>