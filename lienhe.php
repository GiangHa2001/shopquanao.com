<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['user_phone']);
    $message = mysqli_real_escape_string($conn, $_POST['message_content']);
    $currentDate = date("Y-m-d H:i:s");

    $sqlInsert = "
        INSERT INTO Contact_Messages (user_name, user_email, user_phone, message_content, message_created_at)
        VALUES ('$name', '$email', '$phone', '$message', '$currentDate')
    ";


    if (mysqli_query($conn, $sqlInsert)) {
        $success = "Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất!";
    } else {
        $error = "Đã xảy ra lỗi: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop quần áo</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/header.php'; ?>
        </div>
    </div>
    <hr class="featurette-divider"><br>
    <div class="container-fluid">
        <h2 class="text-center mb-4">LIÊN HỆ VỚI CHÚNG TÔI</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="p-4 bg-light rounded shadow-sm">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_name" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" name="user_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="user_email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="user_phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="user_phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="message_content" class="form-label">Nội dung</label>
                            <textarea class="form-control" name="message_content" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Thông tin liên hệ</h4>
                <p><strong>Địa chỉ:</strong> 123 Nguyễn Việt Hồng, Ninh Kiều, Cần Thơ</p>
                <p><strong>Điện thoại:</strong> 0123 456 789</p>
                <p><strong>Email:</strong> info@example.com</p>
                <p><strong>Giờ làm việc:</strong> Thứ 2 - Thứ 7, 8:00 - 17:00</p>
            </div>
            <div class="col-12 mt-3">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3929.1034348591034!2d105.77675557503774!3d10.030949972054763!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0897f6487982d%3A0x7c1870f3df989ecd!2zMTIzIMSQLiBOZ3V54buFbiBWaeG7h3QgSOG7k25nLCBQaMaw4budbmcgMiwgQ8OibiBUaMOgbw!5e0!3m2!1svi!2s!4v1718181502621!5m2!1svi!2s"
                        width="100%" 
                        height="300" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
        </div>
    </div>

    <hr class="featurette-divider">
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/footer.php'; ?>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/chatbot.php'; ?>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
