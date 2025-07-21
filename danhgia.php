<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];
$order_id = $_GET['order_id'] ?? 0;
$product_name = $_GET['product_name'] ?? '';

if (!$order_id || !$product_name) {
    echo "Thiếu thông tin sản phẩm để đánh giá.";
    exit;
}

// Lấy product_id từ tên sản phẩm
$sql = "SELECT product_id FROM products WHERE product_name = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $product_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "Không tìm thấy sản phẩm.";
    exit;
}
$product_id = $product['product_id'];

// Xử lý gửi đánh giá
if (isset($_POST['btnSubmit'])) {
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment']);
    $now = date('Y-m-d H:i:s');

    if ($rating > 0 && $comment != '') {
        $sqlInsert = "INSERT INTO reviews 
            (user_id, product_id, review_rating, review_comment, review_created_at, review_status)
            VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($conn, $sqlInsert);
        mysqli_stmt_bind_param($stmt, 'iiiss', $user_id, $product_id, $rating, $comment, $now);
        mysqli_stmt_execute($stmt);

        $_SESSION['flash_msg'] = "Đánh giá của bạn đã được gửi!";
        $_SESSION['flash_context'] = 'success';
        header("Location: lichsumuahang.php");
        exit;
    } else {
        $error = "Vui lòng nhập đầy đủ nội dung và đánh giá sao.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop quần áo</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
    <style>
        .star-rating input[type="radio"] { display: none; }
        .star-rating label {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center mb-4">Đánh giá sản phẩm</h3>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Sản phẩm</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($product_name) ?>" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Số sao</label>
            <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>">
                    <label for="star<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Nội dung đánh giá</label>
            <textarea name="comment" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-primary">Gửi đánh giá</button>
        <a href="lichsumuahang.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php include_once __DIR__ . '/chatbot.php'; ?>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
