<?php
session_start();

if (!isset($_SESSION['user_logged']) || !isset($_SESSION['last_order_id']) || !isset($_SESSION['last_final_amount'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['last_order_id'];
$final_amount = $_SESSION['last_final_amount'];

// Xóa session đơn hàng sau khi hiển thị
unset($_SESSION['last_order_id']);
unset($_SESSION['last_final_amount']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Quần Áo - Cảm ơn</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success text-center shadow p-4 rounded">
            <h4>Cảm ơn bạn đã đặt hàng!</h4>
            <p>Mã đơn hàng của bạn là <strong>#<?= htmlspecialchars($order_id) ?></strong></p>
            <p>Tổng thanh toán: <strong class="text-danger"><?= number_format($final_amount, 0, ',', '.') ?> đ</strong></p>
            <a href="lichsumuahang.php" class="btn btn-outline-primary mt-3">Xem lịch sử mua hàng</a>
            <a href="index.php" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
        </div>
    </div>
    <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
