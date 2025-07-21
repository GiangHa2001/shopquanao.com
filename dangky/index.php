<?php
session_start();
include_once __DIR__ . '/../dbconnect.php';

if (isset($_POST['btnDangky'])) {
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
    $user_phone = $_POST['user_phone'];
    $user_address = $_POST['user_address'];
    $role = 'customer';
    $user_created_at = date('Y-m-d H:i:s');

    // Kiểm tra email tồn tại
    $sqlSelectkhachhang = "SELECT * FROM Users WHERE user_email = '$user_email'";
    $resultkhachhang = mysqli_query($conn, $sqlSelectkhachhang);

    if (mysqli_num_rows($resultkhachhang) > 0) {
        $_SESSION['register_error'] = "Email đã tồn tại!";
    } else {
        $sqlInsertKhachhang = "INSERT INTO Users (user_name, user_email, user_password, user_phone, user_address, role, user_created_at)
                      VALUES ('$user_name', '$user_email', '$user_password', '$user_phone', '$user_address', '$role', '$user_created_at')";
        if (mysqli_query($conn, $sqlInsertKhachhang)) {
            $_SESSION['register_success'] = "Đăng ký thành công! Mời bạn đăng nhập.";
            header("Location: ../dangnhap/index.php");
            exit;
        } else {
            $_SESSION['register_error'] = "Đăng ký thất bại. Vui lòng thử lại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop quần áo</title>
    <?php include_once __DIR__ . '/../layouts/styles.php'; ?>
    <style>
        body {
            background-color: #dedfda;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-form {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 600px;
            text-align: center;
        }
        .login-form h2 {
            color: #e44d26;
            margin-bottom: 20px;
        }
        .row {
            display: flex;
            align-items: center;
        }
        .col-3 {
            flex: 1;
            padding: 10px;
        }
        .col-3 img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .col-9 {
            flex: 2;
            padding: 10px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #e44d26;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
            cursor: pointer;
        }
        button:hover {
            background: #cc3d1f;
        }
        .alert-msg {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            box-sizing: border-box;
        }
        .alert-error {
            background-color: #ffe5e5;
            color: #d8000c;
            border: 1px solid #d8000c;
        }
        .alert-success {
            background-color: #e5ffe5;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }
        .link-right {
            text-align: right;
            margin-top: 10px;
        }
        .link-right a {
            color: #e44d26;
            text-decoration: none;
            font-weight: bold;
            margin-left: 15px;
        }
        .link-right a:first-child {
            margin-left: 0; 
        }
        .link-right a:hover {
            text-decoration: underline;
            color: #cc3d1f;
        }
    </style>
</head>
<body>
<div style="color: green; text-align: center;">
  <div class="row">
    <div class="col-3">
        <img src="/shopquanao.com/admin/assets/img/login.PNG" alt="" style="width:100%; height:310px;">
    </div>
    <div class="col-9">
        <form class="login-form" method="POST" action="">
          <h2>Đăng ký tài khoản</h2>
          <?php if (isset($_SESSION['register_success'])): ?>
              <div class="alert-msg alert-success"><?= $_SESSION['register_success']; unset($_SESSION['register_success']); ?></div>
          <?php elseif (isset($_SESSION['register_error'])): ?>
              <div class="alert-msg alert-error"><?= $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
          <?php endif; ?>
          <input type="text" name="user_name" placeholder="Tên người dùng" required>
          <input type="email" name="user_email" placeholder="Email" required>
          <input type="password" name="user_password" placeholder="Mật khẩu" required>
          <input type="tel" name="user_phone" placeholder="Số điện thoại" required>
          <input type="text" name="user_address" placeholder="Địa chỉ" required>
          <button type="submit" name="btnDangky">ĐĂNG KÝ</button> 
          <div style="margin: 15px 0;">
            <a href="google-login.php">
                <img src="/shopquanao.com/admin/assets/img/icon/gg.png" alt="Google" style="height:30px;">
                Đăng ký Google
            </a>
            <a href="<?= $fb_login_url ?>">
                <img src="/shopquanao.com/admin/assets/img/icon/fb.png" alt="Facebook" style="height:30px; margin-left:10px;"> 
                Đăng ký Facebook
            </a>
        </div>

          <div class="link-right">
            <a href="../dangnhap/index.php">Đăng nhập</a> <a href="#">Quên mật khẩu?</a>
          </div>
        </form>
    </div>
  </div>
</div>
</body>
</html>
