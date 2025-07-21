<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php?redirect=giohang.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];
$cart_item_id = $_GET['id'] ?? 0;

if ($cart_item_id > 0) {
    // Kiểm tra quyền sở hữu cart_item có thuộc user đang đăng nhập
    $sqlCheck = "
        SELECT ci.cart_item_id 
        FROM cart_items ci 
        JOIN cart c ON ci.cart_id = c.cart_id 
        WHERE ci.cart_item_id = ? AND c.user_id = ?
    ";
    $stmt = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmt, "ii", $cart_item_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $sqlDelete = "DELETE FROM cart_items WHERE cart_item_id = ?";
        $stmtDelete = mysqli_prepare($conn, $sqlDelete);
        mysqli_stmt_bind_param($stmtDelete, "i", $cart_item_id);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
    }

    mysqli_stmt_close($stmt);
}

header('Location: giohang.php');
exit;
