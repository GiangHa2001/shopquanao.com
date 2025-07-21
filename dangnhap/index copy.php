<?php
session_start();
include_once __DIR__ . '/../dbconnect.php';

if (isset($_POST['btnDangnhap'])) {
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

    $sql = "SELECT * FROM Users WHERE user_email = '$user_email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // So sánh mật khẩu đã mã hóa
        if (password_verify($user_password, $row['user_password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_logged'] = $row;

            // Phân quyền và chuyển hướng
            if ($row['role'] === 'admin') {
                header("Location: /shopquanao.com/admin/");
            } else {
                header("Location: /shopquanao.com/");
            }
            exit;
        } else {
            $_SESSION['error'] = "Sai mật khẩu!";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Email không tồn tại!";
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
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
        input[type="email"],
        input[type="password"] {
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

<div class="form-container">
    <div class="row">
        <div class="col-3">
            <img src="/shopquanao.com/admin/assets/img/login.PNG" alt="Login Image">
        </div>
        <div class="col-9">
            <form class="login-form" method="POST" action="">
                <h2>Đăng Nhập</h2>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-msg alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php elseif (isset($_SESSION['register_success'])): ?>
                    <div class="alert-msg alert-success"><?= $_SESSION['register_success']; unset($_SESSION['register_success']); ?></div>
                <?php endif; ?>
                <input type="email" name="user_email" placeholder="Email" required>
                <input type="password" name="user_password" placeholder="Mật khẩu" required>
                <button type="submit" name="btnDangnhap">ĐĂNG NHẬP</button>
                <div class="link-right">
                    <a href="../dangky/index.php">Đăng ký</a> <a href="capnhat.php">Quên mật khẩu?</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../admin/layouts/scripts.php'; ?>
</body>
</html>
