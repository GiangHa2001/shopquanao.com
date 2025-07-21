<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];

// Lấy cart_id
$sqlCart = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmtCart = mysqli_prepare($conn, $sqlCart);
mysqli_stmt_bind_param($stmtCart, "i", $user_id);
mysqli_stmt_execute($stmtCart);
mysqli_stmt_bind_result($stmtCart, $cart_id);
mysqli_stmt_fetch($stmtCart);
mysqli_stmt_close($stmtCart);

// Cập nhật số lượng cho từng item
if (isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $item_id => $quantity) {
        $quantity = max(1, intval($quantity)); // Đảm bảo >= 1
        $sqlUpdate = "UPDATE cart_items SET cart_item_quantity = ? WHERE cart_item_id = ? AND cart_id = ?";
        $stmt = mysqli_prepare($conn, $sqlUpdate);
        mysqli_stmt_bind_param($stmt, "iii", $quantity, $item_id, $cart_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header("Location: giohang.php");
exit;
