<?php
include_once __DIR__ . '/dbconnect.php';
$product_id = $_GET['product_id'] ?? 0;

$sql = "SELECT r.*, u.user_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id = $product_id AND r.review_status = 'approved'
        ORDER BY r.review_created_at DESC";

$result = mysqli_query($conn, $sql);
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
        .review-box {
            max-width: 100%;
            padding: 15px;
            background: #f9f9f9;
        }
        .review-box h4 { margin: 0 0 5px; }
        .stars { color: #ffc107; }
        .comment { margin-top: 5px; }
    </style>
</head>
<body>
    <h2 style="text-align:left;">Đánh giá từ khách hàng</h2>
    <div>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="review-box">
            <h4><?= htmlspecialchars($row['user_name']) ?> - 
                <span class="stars"><?= str_repeat('★', $row['review_rating']) ?><?= str_repeat('☆', 5 - $row['review_rating']) ?></span>
            </h4>
            <div class="comment"><?= nl2br(htmlspecialchars($row['review_comment'])) ?></div>
            <small>Ngày đánh giá: <?= $row['review_created_at'] ?></small>
        </div>
    <?php endwhile; ?>
    </div>
    <?php include_once __DIR__ . '/chatbot.php'; ?>
    <br>
</body>
</html>
