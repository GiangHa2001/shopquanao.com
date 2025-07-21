<?php
$vnp_HashSecret = "SECRETKEY123456789"; 

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$data = $_GET;
unset($data['vnp_SecureHash']);
unset($data['vnp_SecureHashType']);
ksort($data);
$hashData = '';
foreach ($data as $key => $value) {
    $hashData .= $key . '=' . $value . '&';
}
$hashData = rtrim($hashData, '&');

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

if ($secureHash === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        echo "<h3 style='color: green'>Thanh toán thành công!</h3>";
        // Ghi hóa đơn vào database tại đây
    } else {
        echo "<h3 style='color: red'>Thanh toán thất bại hoặc bị hủy.</h3>";
    }
} else {
    echo "<h3 style='color: red'>Sai chữ ký hash!</h3>";
}
?>
