<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = $_SESSION['user_logged']['user_id'];
$reason = trim($_POST['reason'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật trạng thái sang "Đang xử lý hủy" + lưu lý do
    $sql = "UPDATE orders SET order_status = 'cancel_request', cancel_reason = ? WHERE order_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $reason, $order_id, $user_id);
    mysqli_stmt_execute($stmt);
    header("Location: lichsumuahang.php");
    exit;
}
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
        <div class="container mt-5">
            <h3>Yêu cầu hủy đơn hàng #<?= $order_id ?></h3>
            <form method="post">
                <div class="form-group">
                    <label for="reason">Lý do hủy:</label>
                    <textarea class="form-control" name="reason" id="reason" required rows="4"
                        placeholder="Nhập lý do hủy đơn..."></textarea>
                </div>
                <br>
                <button type="submit" class="btn btn-danger">Gửi yêu cầu hủy</button>
                <a href="lichsumuahang.php" class="btn btn-secondary">Quay lại</a>
            </form>
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