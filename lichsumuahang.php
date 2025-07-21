<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php?redirect=lichsumuahang.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];

$sql = "SELECT 
    o.order_id, 
    o.order_created_at AS order_date, 
    o.order_total_price AS total_amount,
    o.discounted_total AS discounted_total,
    o.shipping_fee,
    o.coupon_code,
    o.order_status AS status,
    p.product_name, 
    c.color_name,
    s.size_name,
    oi.order_item_quantity AS quantity, 
    oi.order_item_price AS unit_price
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
LEFT JOIN color c ON oi.color_id = c.color_id
LEFT JOIN sizes s ON oi.size_id = s.size_id
WHERE o.user_id = ?
ORDER BY o.order_created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$order_history = [];

$statusOptions = [
    'pending'         => 'Đang xử lý',
    'processed'       => 'Đã xử lý',
    'cancel_request'  => 'Đang xử lý hủy',
    'confirmed'       => 'Đã xác nhận',
    'packing'         => 'Đang đóng gói',
    'shipping'        => 'Đang vận chuyển',
    'delivered'       => 'Đã giao',
    'cancelled'       => 'Đã hủy',
    'refunded'        => 'Đã hoàn tiền',
    'paid_bank'       => 'Đã thanh toán chuyển khoản'
];

while ($row = mysqli_fetch_assoc($result)) {
    $order_id = $row['order_id'];
    if (!isset($order_history[$order_id])) {
        $coupon_value = 0;
        if (!empty($row['coupon_code'])) {
            $sqlCoupon = "SELECT discount_value FROM coupons WHERE coupon_code = ?";
            $stmtCoupon = mysqli_prepare($conn, $sqlCoupon);
            mysqli_stmt_bind_param($stmtCoupon, "s", $row['coupon_code']);
            mysqli_stmt_execute($stmtCoupon);
            $resultCoupon = mysqli_stmt_get_result($stmtCoupon);
            $coupon = mysqli_fetch_assoc($resultCoupon);
            $coupon_value = $coupon['discount_value'] ?? 0;
        }

        // Tính giảm giá: nếu <=1 là %, >1 là số tiền
        $discount = $coupon_value;
        if ($discount <= 1 && $discount > 0) {
            $discount = $row['total_amount'] * $discount;
        }

        $order_history[$order_id] = [
            'order_date' => $row['order_date'],
            'total_amount' => $row['total_amount'],
            'discounted_total' => $row['discounted_total'] ?? 0,
            'shipping_fee' => $row['shipping_fee'] ?? 0,
            'coupon_code' => $row['coupon_code'] ?? '',
            'discount_value' => $discount,
            'status' => $row['status'],
            'items' => []
        ];
    }

    $order_history[$order_id]['items'][] = [
        'product_name' => $row['product_name'],
        'color_name' => $row['color_name'] ?? '-',
        'size_name' => $row['size_name'] ?? '-',
        'quantity' => $row['quantity'],
        'unit_price' => $row['unit_price']
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Shop quần áo</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12"><?php include_once __DIR__ . '/layouts/header.php'; ?></div>
        </div>
        <hr class="featurette-divider"><br>
        <div class="col-12">
            <div class="container-fluid">
                <h2 class="text-center mb-4">Lịch sử mua hàng</h2>
                <?php if (empty($order_history)): ?>
                <div class="alert alert-warning text-center">Bạn chưa có đơn hàng nào.</div>
                <?php else: ?>
                <?php foreach ($order_history as $order_id => $order): ?>
                <?php
                        $status = strtolower(trim($order['status']));
                        $badgeClass = match ($status) {
                            'pending'         => 'bg-warning text-dark',
                            'processed'       => 'bg-info text-dark',
                            'cancel_request'  => 'bg-warning text-dark',
                            'confirmed'       => 'bg-primary',
                            'packing'         => 'bg-secondary',
                            'shipping'        => 'bg-primary',
                            'delivered'       => 'bg-success',
                            'cancelled'       => 'bg-danger',
                            'refunded'        => 'bg-dark text-white',
                            'paid_bank'       => 'bg-success text-white',
                            default           => 'bg-light text-dark'
                        };
                        $statusLabel = $statusOptions[$status] ?? 'Không xác định';

                        $discount_amount = $order['total_amount'] - $order['discounted_total'] - $order['shipping_fee'];
                    ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Mã đơn hàng:</strong> <?= $order_id ?> |
                        <strong>Ngày đặt:</strong> <?= $order['order_date'] ?> |
                        <strong>Trạng thái:</strong>
                        <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th>Màu</th>
                                    <th>Size</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>

                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= htmlspecialchars($item['color_name']) ?></td>
                                    <td><?= htmlspecialchars($item['size_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['unit_price'], 0, ',', '.') ?> đ</td>
                                    <td><?= number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>

                        <?php
                            $tamtinh = $order['total_amount'];
                            $shipping = $order['shipping_fee'];
                            $discount = $order['discount_value'] ?? 0;
                            $final_total = $tamtinh + $shipping - $discount;
                        ?>

                        <div class="text-end">
                            <p><strong>Tạm tính:</strong> <?= number_format($tamtinh, 0, ',', '.') ?> đ</p>
                            <p><strong>Phí vận chuyển:</strong> <?= number_format($shipping, 0, ',', '.') ?> đ</p>

                            <?php if (!empty($order['coupon_code'])): ?>
                            <p><strong>Mã giảm giá (<?= htmlspecialchars($order['coupon_code']) ?>):</strong>
                                <span class="text-success">-<?= number_format($discount, 0, ',', '.') ?> đ</span>
                            </p>
                            <?php endif; ?>

                            <h5><strong class="fw-bold text-primary">Tổng cộng:</strong>
                                <span class="fw-bold text-primary"><?= number_format($final_total, 0, ',', '.') ?>
                                    đ</span>
                            </h5>
                        </div>


                        <div class="text-start mt-2">
                            <?php if ($status === 'pending'): ?>
                            <a href="huydonhang.php?order_id=<?= urlencode($order_id) ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                                Hủy đơn hàng
                            </a>
                            <?php elseif ($status === 'delivered'): ?>
                            <p><strong>Đánh giá sản phẩm:</strong></p>
                            <?php foreach ($order['items'] as $item): ?>
                            <div class="mb-1">
                                <span><?= htmlspecialchars($item['product_name']) ?></span>
                                <a href="danhgia.php?order_id=<?= urlencode($order_id) ?>&product_name=<?= urlencode($item['product_name']) ?>"
                                    class="btn btn-warning btn-sm ms-2">
                                    Đánh giá
                                </a>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <span class="text-muted">Không thể hủy đơn hàng này</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
        <hr class="featurette-divider">
        <div class="row">
            <div class="col-12"><br><?php include_once __DIR__ . '/layouts/footer.php'; ?></div>
        </div>
        <?php include_once __DIR__ . '/chatbot.php'; ?>
        <?php include_once __DIR__ . '/layouts/scripts.php'; ?>
    </div>
</body>

</html>