<?php
session_start();
include_once __DIR__ . '/../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_email'])) {
    $email = trim($_POST['user_email']);

    $sql = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Tạo mật khẩu mới ngẫu nhiên
        $newPasswordPlain = substr(md5(rand()), 0, 10);
        $newPasswordHashed = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

        // Cập nhật vào CSDL
        $updateSql = "UPDATE users SET user_password = '$newPasswordHashed' WHERE user_email = '$email'";
        mysqli_query($conn, $updateSql);

        // Giả lập gửi email (ở môi trường thật thì dùng PHPMailer)
        $_SESSION['success'] = "Mật khẩu mới: <strong>$newPasswordPlain</strong><br>Vui lòng đổi lại sau khi đăng nhập.";
    } else {
        $_SESSION['error'] = "Email không tồn tại trong hệ thống.";
    }

    header("Location: capnhat.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Shop quần áo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once __DIR__ . '/../layouts/styles.php'; ?>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 500px;
        }
        h2 {
            text-align: center;
            color: #e44d26;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #e44d26;
            color: white;
            border: none;
            margin-top: 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #cc3d1f;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .error {
            background-color: #ffe5e5;
            color: #d8000c;
            border: 1px solid #d8000c;
        }
        .success {
            background-color: #e5ffe5;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Quên mật khẩu</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php elseif (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="email" name="user_email" placeholder="Nhập email của bạn" required>
        <button type="submit">Lấy lại mật khẩu</button>
    </form>
    <div style="margin-top: 10px; text-align: right;">
        <a href="index.php" style="text-decoration: none; color: #e44d26; font-weight: bold;">Quay lại đăng nhập</a>
    </div>
</div>

<?php include_once __DIR__ . '/../admin/layouts/scripts.php'; ?>
</body>
</html>
