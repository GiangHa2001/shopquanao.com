<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_logged'])) {
    header('Location: /shopquanao.com/dangnhap/index.php');
    exit;
}

$user = $_SESSION['user_logged'];
$user_id = $user['user_id'];

// Lấy thông tin người dùng từ CSDL
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userInfo = mysqli_fetch_assoc($result);
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
    <div class="container">
    <h3 class="text-center mb-4">Thông tin cá nhân</h3>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow rounded-4">
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" name="user_name" value="<?= htmlspecialchars($userInfo['user_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="user_email" value="<?= htmlspecialchars($userInfo['user_email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_address" class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" name="user_address" value="<?= htmlspecialchars($userInfo['user_address']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="user_phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="user_phone" value="<?= htmlspecialchars($userInfo['user_phone']) ?>">
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="btnUpdate" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <hr class="featurette-divider">
    <?php include_once __DIR__ . '/chatbot.php'; ?>
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/footer.php'; ?>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>

<?php
// Xử lý cập nhật
if (isset($_POST['btnUpdate'])) {
    $sqlUpdate = "UPDATE users SET user_name=?, user_email=?, user_address=?, user_phone=? WHERE user_id=?";
    $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
    mysqli_stmt_bind_param(
        $stmtUpdate,
        'ssssi',
        $_POST['user_name'],
        $_POST['user_email'],
        $_POST['user_address'],
        $_POST['user_phone'],
        $user_id
    );
    mysqli_stmt_execute($stmtUpdate);

    // Cập nhật session
    $_SESSION['user_logged']['user_name'] = $_POST['user_name'];
    $_SESSION['user_logged']['user_email'] = $_POST['user_email'];

    echo "<script>alert('Cập nhật thành công!'); location.href='thongtin.php';</script>";
}
?>
