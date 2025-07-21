<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

$user_id = $_SESSION['user_logged']['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    if ($coupon_code !== '' && $user_id) {
        $now = date('Y-m-d H:i:s');

        // Kiểm tra mã có tồn tại và còn hiệu lực
        $sql = "SELECT coupon_id, discount_value FROM coupons WHERE coupon_code = ? AND start_date <= ? AND end_date >= ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $coupon_code, $now, $now);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $coupon = mysqli_fetch_assoc($result);

        if ($coupon) {
            $coupon_id = $coupon['coupon_id'];

            // Kiểm tra người dùng đã sử dụng chưa
            $check = "SELECT * FROM order_coupons WHERE user_id = ? AND coupon_id = ?";
            $stmtCheck = mysqli_prepare($conn, $check);
            mysqli_stmt_bind_param($stmtCheck, "ii", $user_id, $coupon_id);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if (mysqli_num_rows($resultCheck) === 0) {
                // Lưu vào session
                $_SESSION['applied_coupon'] = [
                    'code' => $coupon_code,
                    'id' => $coupon_id,
                    'discount' => $coupon['discount_value']
                ];
            } else {
                $_SESSION['coupon_error'] = "Bạn đã sử dụng mã này rồi!";
            }
        } else {
            $_SESSION['coupon_error'] = "Mã không hợp lệ hoặc đã hết hạn!";
        }
    }
}

header("Location: giohang.php");
exit;
?>
<?php include_once __DIR__ . '/chatbot.php'; ?>