<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap/index.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user_phone = trim($_POST['user_phone']);
$user_address = trim($_POST['user_address']);

if ($user_phone && $user_address) {
    $sqlUpdate = "UPDATE users SET user_phone = ?, user_address = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sqlUpdate);
    mysqli_stmt_bind_param($stmt, "ssi", $user_phone, $user_address, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Quay lại trang giỏ hàng
header("Location: giohang.php?update=success");
exit;
?>