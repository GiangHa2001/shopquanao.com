<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

// Lấy user
$user_id = $_SESSION['user_logged']['user_id'] ?? 0;

if (!$user_id) {
    header("Location: dangnhap/index.php?redirect=giohang.php");
    exit;
}

// Tổng tiền cần thanh toán (đã tính ở trang giỏ hàng)
$shipping_fee = $_POST['shipping_method'] ?? 30000;
$coupon_code = $_SESSION['applied_coupon']['code'] ?? '';
$coupon_discount = 0;

$sqlCart = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmtCart = mysqli_prepare($conn, $sqlCart);
mysqli_stmt_bind_param($stmtCart, "i", $user_id);
mysqli_stmt_execute($stmtCart);
mysqli_stmt_bind_result($stmtCart, $cart_id);
mysqli_stmt_fetch($stmtCart);
mysqli_stmt_close($stmtCart);

if (!$cart_id) {
    die('Không tìm thấy giỏ hàng');
}

$sqlItems = "
    SELECT p.product_price, ci.cart_item_quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
";
$stmtItems = mysqli_prepare($conn, $sqlItems);
mysqli_stmt_bind_param($stmtItems, "i", $cart_id);
mysqli_stmt_execute($stmtItems);
$resultItems = mysqli_stmt_get_result($stmtItems);

$subtotal = 0;
while ($item = mysqli_fetch_assoc($resultItems)) {
    $subtotal += $item['product_price'] * $item['cart_item_quantity'];
}

// Áp dụng mã giảm giá
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

// Tính tổng sau giảm giá
$amount = max(0, $subtotal - $coupon_discount) + $shipping_fee;

// === Tạo link thanh toán VNPAY === //
$vnp_TmnCode = "2QXUI4EW"; 
$vnp_HashSecret = "SECRETKEY123456789";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; 
$vnp_Returnurl = "http://localhost/shopquanao.com/vnpay_return.php"; 

$order_id = time();
$vnp_TxnRef = $order_id;
$vnp_OrderInfo = "Thanh toán đơn hàng $order_id";
$vnp_Amount = $amount * 100; // nhân 100 vì đơn vị là VNĐ x 100
$vnp_Locale = "vn";
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "billpayment",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef
);

ksort($inputData);
$query = "";
$hashdata = "";
foreach ($inputData as $key => $value) {
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
    $hashdata .= $key . "=" . $value . '&';
}
$query = rtrim($query, '&');
$hashdata = rtrim($hashdata, '&');

$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= "?" . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

// Chuyển hướng sang cổng thanh toán
header("Location: $vnp_Url");
exit;
?>
