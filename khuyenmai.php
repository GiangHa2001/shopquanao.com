<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

// Lấy danh sách coupon từ DB
$now = date('Y-m-d H:i:s');
$sql = "SELECT * FROM coupons ORDER BY start_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$coupons = [];
while ($row = mysqli_fetch_assoc($result)) {
    $coupons[] = $row;
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
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/header.php'; ?>
        </div>
    </div>
    <hr class="featurette-divider"><br>
     <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                    <h2 class="text-center mb-4">DANH SÁCH KHUYẾN MÃI</h2>
                        <?php if (!empty($coupons)): ?>
                        <div class="row row-cols-1 row-cols-md-4 g-4">
                        <?php foreach ($coupons as $coupon): 
                            $start = strtotime($coupon['start_date']);
                            $end = strtotime($coupon['end_date']);
                            $now = strtotime(date('Y-m-d H:i:s'));
                            $is_active = ($start <= $now && $end >= $now);
                        ?>
                            <div class="col">
                                <div class="card h-100 <?= $is_active ? '' : 'bg-light text-muted' ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($coupon['coupon_code']) ?></h5>
                                        <!-- <p><?= strip_tags($coupon['coupon_description']) ?></p> -->
                                        <p><strong>Giảm:</strong> 
                                            <?= ($coupon['discount_value'] <= 1) 
                                                ? number_format($coupon['discount_value'] * 100, 0) . '%' 
                                                : number_format($coupon['discount_value'], 0, ',', '.') . ' đ' ?>
                                        </p>
                                        <p><strong>Hiệu lực:</strong><br>
                                            <?= date('d/m/Y', $start) ?> – <?= date('d/m/Y', $end) ?><br>
                                            <span class="badge <?= $is_active ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $is_active ? 'Đang hoạt động' : 'Hết hạn' ?>
                                            </span>
                                        </p>
                                        <?php if ($is_active): ?>
                                        <form method="POST" action="add_khuyenmai.php">
                                            <input type="hidden" name="coupon_code" value="<?= htmlspecialchars($coupon['coupon_code']) ?>">
                                            <button type="submit" class="btn btn-primary">Áp dụng</button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Đã hết hạn</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">Không có mã khuyến mãi nào khả dụng.</div>
                    <?php endif; ?>
                <div class="text-center mt-4">
                    <a href="giohang.php" class="btn btn-secondary">Quay về giỏ hàng</a>
                </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="featurette-divider">
    <div class="row">
        <div class="col-12"><br>
        <?php include_once __DIR__ . '/layouts/footer.php'; ?>
        </div>
    </div>
    <?php include_once __DIR__ . '/chatbot.php'; ?>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
