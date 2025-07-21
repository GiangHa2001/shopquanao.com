<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged'])) {
    header("Location: dangnhap/index.php?redirect=danhgia_user.php");
    exit;
}

$user_id = $_SESSION['user_logged']['user_id'];

// Lấy danh sách đánh giá của người dùng
$sql = "
    SELECT 
        r.*, 
        p.product_name 
    FROM reviews r 
    JOIN products p ON r.product_id = p.product_id
    WHERE r.user_id = ?
    ORDER BY r.review_created_at DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reviews = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
}

// Mảng trạng thái đánh giá và class tương ứng
$statusLabels = [
    'pending'       => 'Chờ duyệt',
    'approved'      => 'Hiển thị',
    'rejected'      => 'Bị từ chối',
    'spam'          => 'Spam',
    'hidden'        => 'Ẩn',
    'visible'       => 'Hiển thị công khai',
    'edited'        => 'Đã chỉnh sửa',
    'user_deleted'  => 'Bạn đã xóa',
    'admin_deleted' => 'Admin đã xóa',
    'flagged'       => 'Bị báo cáo'
];

$statusClasses = [
    'pending'       => 'bg-warning text-dark',
    'approved'      => 'bg-success text-white',
    'rejected'      => 'bg-danger text-white',
    'spam'          => 'bg-dark text-white',
    'hidden'        => 'bg-secondary text-white',
    'visible'       => 'bg-success text-white',
    'edited'        => 'bg-info text-white',
    'user_deleted'  => 'bg-light text-muted',
    'admin_deleted' => 'bg-light text-muted',
    'flagged'       => 'bg-danger text-white'
];
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
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .stars {
            color: #ffc107;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
        }
     </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/header.php'; ?> 
        </div>
    </div>
    <hr class="featurette-divider"><br>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">TẤT CẢ ĐÁNH GIÁ CỦA BẠN</h3>
                <?php if (empty($reviews)): ?>
                    <div class="alert alert-warning text-center">Bạn chưa thực hiện đánh giá nào.</div>
                <?php else: ?>
                    <?php foreach ($reviews as $r): 
                        $status = $r['review_status'];
                        $label = $statusLabels[$status] ?? 'Không xác định';
                        $class = $statusClasses[$status] ?? 'bg-secondary text-white';
                    ?>
                        <div class="review-box">
                            <h5><?= htmlspecialchars($r['product_name']) ?></h5>
                            <div>
                                <span class="stars">
                                    <?= str_repeat('★', $r['review_rating']) ?>
                                    <?= str_repeat('☆', 5 - $r['review_rating']) ?>
                                </span>
                                <!-- <small class="ms-2">
                                    <span class="status-badge <?= $class ?>">
                                        <?= $label ?>
                                    </span>
                                </small> -->
                            </div>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($r['review_comment'])) ?></p>
                            <small>Ngày đánh giá: <?= htmlspecialchars($r['review_created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <hr class="featurette-divider">
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/footer.php'; ?>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/chatbot.php'; ?>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
