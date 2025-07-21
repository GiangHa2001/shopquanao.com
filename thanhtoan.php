<?php
session_start();
include_once __DIR__ . '/dbconnect.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_logged'])) {
    header('Location: /dangnhap.php');
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];
$coupon_code = $_SESSION['applied_coupon']['code'] ?? '';
$coupon_value = $_SESSION['applied_coupon']['value'] ?? 0;

$sql_user = "SELECT * FROM users WHERE user_id = $user_id";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

$user_name = $user['user_name'];
$user_email = $user['user_email'];
$user_address = $user['user_address'] ?? 'Chưa cập nhật';
$user_phone = $user['user_phone'] ?? 'Chưa cập nhật';

$sql = "SELECT ci.product_id, ci.cart_item_quantity, ci.size_id, ci.color_id, 
               p.product_name, p.product_price, c2.cart_id
        FROM cart_items ci
        JOIN cart c2 ON ci.cart_id = c2.cart_id
        JOIN products p ON ci.product_id = p.product_id
        WHERE c2.user_id = $user_id";

$result = mysqli_query($conn, $sql);
$cart = [];
$total_price = 0;
$cart_id = null;

while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row['product_price'] * $row['cart_item_quantity'];
    $cart[] = [
        'id' => $row['product_id'],
        'name' => $row['product_name'],
        'quantity' => $row['cart_item_quantity'],
        'price' => $row['product_price'],
        'size_id' => $row['size_id'],
        'color_id' => $row['color_id']
    ];
    $total_price += $subtotal;
    $cart_id = $row['cart_id'];
}

if (empty($cart)) {
    die('Giỏ hàng rỗng!');
}

$shipping_fee = 30000;
$discount = 0;
if ($coupon_code) {
    $discount = ($coupon_value <= 1) ? ($total_price * $coupon_value) : $coupon_value;
}

$final_amount = $total_price + $shipping_fee - $discount;

$order_sql = "INSERT INTO orders (user_id, order_total_price, discounted_total, shipping_fee, coupon_code, order_created_at)
              VALUES (?, ?, ?, ?, ?, NOW())";
$stmt_order = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt_order, "iddds", $user_id, $total_price, $final_amount, $shipping_fee, $coupon_code);
mysqli_stmt_execute($stmt_order);
$order_id = mysqli_insert_id($conn);

foreach ($cart as $item) {
    $detail_sql = "INSERT INTO order_items (order_id, product_id, order_item_quantity, order_item_price, size_id, color_id)
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detail = mysqli_prepare($conn, $detail_sql);
    mysqli_stmt_bind_param($stmt_detail, "iiidii", $order_id, $item['id'], $item['quantity'], $item['price'], $item['size_id'], $item['color_id']);
    mysqli_stmt_execute($stmt_detail);
}

if (!empty($user_email)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'webfashionshop482@gmail.com';
        $mail->Password = 'plmn htqw dyfb gigl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('webfashionshop482@gmail.com', 'Shop Quần Áo');
        $mail->addAddress($user_email, $user_name);
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đơn hàng #' . $order_id;

        $body = "<h2>Xin chào $user_name,</h2>";
        $body .= "<p><strong>Mã đơn hàng:</strong> #$order_id</p>";
        $body .= "<p><strong>Địa chỉ:</strong> $user_address</p>";
        $body .= "<p><strong>Số điện thoại:</strong> $user_phone</p>";

        $body .= "<table border='1' cellpadding='6' cellspacing='0' style='width:100%; border-collapse: collapse;'>
                    <thead>
                        <tr style='background-color:#eee;'>
                            <th>Tên sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Kích thước</th>
                            <th>Màu sắc</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead><tbody>";

        $sql_details = "SELECT oi.order_item_quantity, oi.order_item_price,
                               p.product_name, s.size_name, c.color_name
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        LEFT JOIN sizes s ON oi.size_id = s.size_id
                        LEFT JOIN color c ON oi.color_id = c.color_id
                        WHERE oi.order_id = $order_id";

        $result_details = mysqli_query($conn, $sql_details);
        while ($item = mysqli_fetch_assoc($result_details)) {
            $subtotal = $item['order_item_quantity'] * $item['order_item_price'];
            $body .= "<tr>
                        <td>{$item['product_name']}</td>
                        <td>{$item['order_item_quantity']}</td>
                        <td>{$item['size_name']}</td>
                        <td>{$item['color_name']}</td>
                        <td>" . number_format($item['order_item_price']) . " đ</td>
                        <td>" . number_format($subtotal) . " đ</td>
                    </tr>";
        }
        $body .= "</tbody></table>";
        $body .= "<p><strong>Tạm tính:</strong> " . number_format($total_price) . " đ</p>";
        $body .= "<p><strong>Phí vận chuyển:</strong> " . number_format($shipping_fee) . " đ</p>";
        $body .= "<p><strong>Giảm giá:</strong> " . number_format($discount) . " đ</p>";
        $body .= "<h3>Tổng cộng: " . number_format($final_amount) . " đ</h3>";
        $body .= "<p>Chúng tôi sẽ giao hàng đến bạn sớm nhất!</p>";

        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Gửi email thất bại: " . $mail->ErrorInfo);
    }
}

if ($cart_id) {
    mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id = $cart_id");
    mysqli_query($conn, "DELETE FROM cart WHERE cart_id = $cart_id");
}

unset($_SESSION['applied_coupon']);
header('Location: thankyou.php');

exit;
?>
<?php include_once __DIR__ . '/chatbot.php'; ?>