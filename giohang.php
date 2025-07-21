<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php?redirect=giohang.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];

$sqlUser = "SELECT user_name, user_email, user_phone, user_address FROM users WHERE user_id = ?";
$stmtUser = mysqli_prepare($conn, $sqlUser);
mysqli_stmt_bind_param($stmtUser, "i", $user_id);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

$sqlCart = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmtCart = mysqli_prepare($conn, $sqlCart);
mysqli_stmt_bind_param($stmtCart, "i", $user_id);
mysqli_stmt_execute($stmtCart);
mysqli_stmt_bind_result($stmtCart, $cart_id);
mysqli_stmt_fetch($stmtCart);
mysqli_stmt_close($stmtCart);

if (!$cart_id) {
    $sqlCreate = "INSERT INTO cart (user_id, cart_created_at) VALUES (?, NOW())";
    $stmt = mysqli_prepare($conn, $sqlCreate);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $cart_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
}

$sql = "
    SELECT 
        ci.cart_item_id,
        p.product_name,
        p.product_price,
        c.color_name,
        s.size_name,
        ci.cart_item_quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    LEFT JOIN color c ON ci.color_id = c.color_id
    LEFT JOIN sizes s ON ci.size_id = s.size_id
    WHERE ci.cart_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $cart_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cartItems = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['subtotal'] = $row['product_price'] * $row['cart_item_quantity'];
    $cartItems[] = $row;
}

$subtotal = array_sum(array_column($cartItems, 'subtotal'));
$shipping_fee = 30000;

$coupon_code = $_SESSION['applied_coupon']['code'] ?? '';
$coupon_discount = 0;

if ($coupon_code) {
    $now = date('Y-m-d H:i:s');
    $sqlCoupon = "SELECT discount_value FROM coupons WHERE coupon_code = ? AND start_date <= ? AND end_date >= ?";
    $stmtCoupon = mysqli_prepare($conn, $sqlCoupon);
    mysqli_stmt_bind_param($stmtCoupon, "sss", $coupon_code, $now, $now);
    mysqli_stmt_execute($stmtCoupon);
    $resultCoupon = mysqli_stmt_get_result($stmtCoupon);
    $coupon = mysqli_fetch_assoc($resultCoupon);

    if ($coupon) {
        $discount = $coupon['discount_value'];
        $coupon_discount = ($discount <= 1) ? ($subtotal * $discount) : $discount;
    }
}

$subtotal_after_discount = max(0, $subtotal - $coupon_discount);
$total_after_coupon = $subtotal_after_discount + $shipping_fee;
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const discount = <?= json_encode($coupon_discount) ?>;

    document.querySelectorAll('.btn-plus').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const input = row.querySelector('input');
            input.value = parseInt(input.value) + 1;
            updateSubtotal(row);
        });
    });

    document.querySelectorAll('.btn-minus').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const input = row.querySelector('input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateSubtotal(row);
            }
        });
    });

    document.getElementById('shipping-method')?.addEventListener('change', updateTotal);

    document.querySelectorAll('input.qty-input').forEach(input => {
        input.addEventListener('change', () => {
            const row = input.closest('tr');
            updateSubtotal(row);
        });
    });

    function updateSubtotal(row) {
        const price = parseFloat(row.querySelector('.price').dataset.price);
        const qty = parseInt(row.querySelector('input').value);
        const subtotal = price * qty;
        row.querySelector('.subtotal').innerText = subtotal.toLocaleString('vi-VN') + ' đ';
        row.querySelector('.subtotal').dataset.value = subtotal;
        updateTotal();
    }

    function updateTotal() {
        let subtotal = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            const price = parseFloat(row.querySelector('.price').dataset.price);
            const qty = parseInt(row.querySelector('input').value);
            subtotal += price * qty;
        });

        const shipping = parseInt(document.getElementById('shipping-method')?.value || 30000);
        const subtotal_after_discount = Math.max(0, subtotal - discount);
        const total_after_coupon = subtotal_after_discount + shipping;

        document.getElementById('subtotal').innerText = subtotal.toLocaleString('vi-VN') + ' đ';
        document.getElementById('shipping-amount').innerText = shipping.toLocaleString('vi-VN') + ' đ';
        if (document.getElementById('discount-amount')) {
            document.getElementById('discount-amount').innerText = '-' + discount.toLocaleString('vi-VN') + ' đ';
        }
        if (document.getElementById('total-after-discount')) {
            document.getElementById('total-after-discount').innerText = total_after_coupon.toLocaleString('vi-VN') + ' đ';
        }
        document.getElementById('total').innerText = total_after_coupon.toLocaleString('vi-VN') + ' đ';
    }
});
</script>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop quần áo</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
    <style>
        .qty-input { width: 60px; text-align: center; }
    </style>
</head>
<body>
<div class="container-fluid mt-5">
    <h2 class="text-center mb-4">Giỏ hàng của bạn</h2>

    <!-- Thông tin người dùng -->
    <div class="bg-light p-3 mb-4 border rounded">
        <h5>Thông tin người dùng</h5>
        <form action="update_user.php" method="POST" class="row g-3">
            <p><strong>Họ tên:</strong> <?= htmlspecialchars($user['user_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['user_email']) ?></p>
            <div class="col-md-12">
                <label class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" name="user_phone" value="<?= htmlspecialchars($user['user_phone']) ?>" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" name="user_address" value="<?= htmlspecialchars($user['user_address']) ?>" required>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-outline-primary btn-sm">Cập nhật thông tin</button>
            </div>
        </form>
    </div>

    <?php if (!empty($cartItems)): ?>
    <form method="POST" action="thanhtoan.php" id="cart-form">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th>Màu</th>
                    <th>Size</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $index => $item): ?>
                <tr data-id="<?= $item['cart_item_id'] ?>">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['color_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['size_name'] ?? '-') ?></td>
                    <td class="price" data-price="<?= $item['product_price'] ?>"><?= number_format($item['product_price'], 0, ',', '.') ?> đ</td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-minus">−</button>
                            <input type="number" name="qty[<?= $item['cart_item_id'] ?>]" value="<?= $item['cart_item_quantity'] ?>" min="1" class="form-control qty-input">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-plus">+</button>
                        </div>
                    </td>
                    <td class="subtotal text-danger fw-bold"><?= number_format($item['subtotal'], 0, ',', '.') ?> đ</td>
                    <td><a href="delete_cart.php?id=<?= $item['cart_item_id'] ?>" class="btn btn-sm btn-danger">Xóa</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
            $subtotal = array_sum(array_column($cartItems, 'subtotal'));
            $shipping_fee = 30000;
            $total = $subtotal + $shipping_fee;
        ?>
        <p class="text-end">
            <a href="khuyenmai.php" class="btn btn-outline-info btn-sm">Xem danh sách mã giảm giá</a>
        </p>
        <div class="text-end mt-3">
            <p><strong>Tạm tính:</strong> <span id="subtotal"><?= number_format($subtotal, 0, ',', '.') ?> đ</span></p>

            <p><strong>Phí vận chuyển:</strong>
                <select id="shipping-method" name="shipping_method" class="form-select d-inline w-auto">
                    <option value="30000" selected>Giao hàng thường (30.000đ)</option>
                    <option value="50000">Giao hàng nhanh (50.000đ)</option>
                </select>
                <span class="ms-2" id="shipping-amount"><?= number_format($shipping_fee, 0, ',', '.') ?> đ</span>
            </p>

            <?php if ($coupon_code): ?>
                <p><strong>Giảm giá (<?= htmlspecialchars($coupon_code) ?>):</strong> 
                    <span id="discount-amount">-<?= number_format($coupon_discount, 0, ',', '.') ?> đ</span>
                </p>
            <?php endif; ?>

            <h4><strong>Thành tiền sau giảm:</strong> 
                <span class="text-danger fw-bold" id="total-after-discount"><?= number_format($total_after_coupon, 0, ',', '.') ?> đ</span>
            </h4>
            <h4><strong>Tổng cộng:</strong> 
                <span class="text-danger fw-bold" id="total"><?= number_format($total_after_coupon, 0, ',', '.') ?> đ</span>
            </h4>
        </div>
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" formaction="sanpham.php" class="btn btn-secondary">Tiếp tục mua hàng</button>
            <button type="submit" name="action" value="checkout" class="btn btn-primary">Thanh toán</button>
            

        </div>
    </form>
    <form action="thanhtoan1.php" method="post">
    <button type="submit" class="btn btn-success">Thanh toán qua VNPAY</button>
    </form>
    <?php else: ?>
        <div class="alert alert-warning text-center">Giỏ hàng của bạn đang trống.</div>
        <div class="text-center mt-3">
            <a href="sanpham.php" class="btn btn-primary">Quay lại mua sắm</a>
        </div>
    <?php endif; ?>
</div>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
