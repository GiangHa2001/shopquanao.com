<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php?redirect=themvaogiohang.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];
$product_id = $_POST['product_id'] ?? 0;
$color_name = $_POST['color'] ?? '';
$size_name = $_POST['size'] ?? '';
$quantity = $_POST['quantity'] ?? 1;

// Lấy color_id và size_id từ tên
$sqlColor = "SELECT color_id FROM color WHERE color_name = ?";
$stmtColor = mysqli_prepare($conn, $sqlColor);
mysqli_stmt_bind_param($stmtColor, "s", $color_name);
mysqli_stmt_execute($stmtColor);
$resultColor = mysqli_stmt_get_result($stmtColor);
$colorRow = mysqli_fetch_assoc($resultColor);
$color_id = $colorRow['color_id'] ?? 0;

$sqlSize = "SELECT size_id FROM sizes WHERE size_name = ?";
$stmtSize = mysqli_prepare($conn, $sqlSize);
mysqli_stmt_bind_param($stmtSize, "s", $size_name);
mysqli_stmt_execute($stmtSize);
$resultSize = mysqli_stmt_get_result($stmtSize);
$sizeRow = mysqli_fetch_assoc($resultSize);
$size_id = $sizeRow['size_id'] ?? 0;

if (!$product_id || !$color_id || !$size_id) {
    echo "Thông tin sản phẩm không hợp lệ.";
    exit;
}

// Kiểm tra số lượng tồn kho theo color + size
$sqlQtyCheck = "
    SELECT product_detail_quantity 
    FROM product_details 
    WHERE product_id = ? AND color_id = ? AND size_id = ?
";
$stmtQty = mysqli_prepare($conn, $sqlQtyCheck);
mysqli_stmt_bind_param($stmtQty, "iii", $product_id, $color_id, $size_id);
mysqli_stmt_execute($stmtQty);
$resultQty = mysqli_stmt_get_result($stmtQty);
$rowQty = mysqli_fetch_assoc($resultQty);
$availableQty = $rowQty['product_detail_quantity'] ?? 0;

if ($availableQty < $quantity) {
    echo "Không đủ hàng trong kho.";
    exit;
}

// Tìm hoặc tạo giỏ hàng của user
$sqlCart = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmtCart = mysqli_prepare($conn, $sqlCart);
mysqli_stmt_bind_param($stmtCart, "i", $user_id);
mysqli_stmt_execute($stmtCart);
$resultCart = mysqli_stmt_get_result($stmtCart);

if ($row = mysqli_fetch_assoc($resultCart)) {
    $cart_id = $row['cart_id'];
} else {
    $stmtNewCart = mysqli_prepare($conn, "INSERT INTO cart (user_id, cart_created_at) VALUES (?, NOW())");
    mysqli_stmt_bind_param($stmtNewCart, "i", $user_id);
    mysqli_stmt_execute($stmtNewCart);
    $cart_id = mysqli_insert_id($conn);
}

// Kiểm tra item đã tồn tại trong giỏ chưa
$sqlItem = "
    SELECT cart_item_id, cart_item_quantity 
    FROM cart_items 
    WHERE cart_id = ? AND product_id = ? AND color_id = ? AND size_id = ?
";
$stmtItem = mysqli_prepare($conn, $sqlItem);
mysqli_stmt_bind_param($stmtItem, "iiii", $cart_id, $product_id, $color_id, $size_id);
mysqli_stmt_execute($stmtItem);
$resultItem = mysqli_stmt_get_result($stmtItem);

if ($row = mysqli_fetch_assoc($resultItem)) {
    // Cập nhật số lượng
    $newQty = $row['cart_item_quantity'] + $quantity;
    $stmtUpdate = mysqli_prepare($conn, "
        UPDATE cart_items 
        SET cart_item_quantity = ? 
        WHERE cart_item_id = ?
    ");
    mysqli_stmt_bind_param($stmtUpdate, "ii", $newQty, $row['cart_item_id']);
    mysqli_stmt_execute($stmtUpdate);
} else {
    // Thêm mới vào cart_items
    $stmtAdd = mysqli_prepare($conn, "
        INSERT INTO cart_items (cart_id, product_id, color_id, size_id, cart_item_quantity)
        VALUES (?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmtAdd, "iiiii", $cart_id, $product_id, $color_id, $size_id, $quantity);
    mysqli_stmt_execute($stmtAdd);
}

// Trừ số lượng trong bảng product_details
$sqlUpdateStock = "
    UPDATE product_details 
    SET product_detail_quantity = product_detail_quantity - ? 
    WHERE product_id = ? AND color_id = ? AND size_id = ?
";
$stmtUpdateStock = mysqli_prepare($conn, $sqlUpdateStock);
mysqli_stmt_bind_param($stmtUpdateStock, "iiii", $quantity, $product_id, $color_id, $size_id);
mysqli_stmt_execute($stmtUpdateStock);

// Trừ tổng số lượng trong bảng products
$sqlUpdateProductQty = "
    UPDATE products 
    SET product_quantity = product_quantity - ? 
    WHERE product_id = ?
";
$stmtUpdateProductQty = mysqli_prepare($conn, $sqlUpdateProductQty);
mysqli_stmt_bind_param($stmtUpdateProductQty, "ii", $quantity, $product_id);
mysqli_stmt_execute($stmtUpdateProductQty);

// Thành công
header("Location: giohang.php");
exit;
?>
