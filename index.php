<?php
session_start();
// 1. Mở kết nối
include_once __DIR__ . '/dbconnect.php';

// 2. Truy vấn sản phẩm + chi tiết
$sqlSelectSanPham =  "
    SELECT 
        p.product_id, 
        p.product_name, 
        p.product_description, 
        p.product_short_description, 
        p.product_price, 
        p.product_old_price, 
        p.product_quantity,
        c.category_name,
        img.image_filename,
        col.color_name,
        pd.product_detail_price,
        s.size_name,
        pd.product_detail_quantity
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_details pd ON p.product_id = pd.product_id
    LEFT JOIN color col ON pd.color_id = col.color_id
    LEFT JOIN sizes s ON pd.size_id = s.size_id
    LEFT JOIN (
        SELECT product_id, MIN(image_filename) AS image_filename
        FROM images
        GROUP BY product_id
    ) img ON img.product_id = p.product_id
    GROUP BY p.product_id
";

// 3. Thực thi truy vấn
$resultSanPham = mysqli_query($conn, $sqlSelectSanPham);
if (!$resultSanPham) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

// 4. Lấy dữ liệu
$arrDanhSachSanPham = [];
while($row = mysqli_fetch_array($resultSanPham, MYSQLI_ASSOC)) {
    $arrDanhSachSanPham[] = array(
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'product_description' => $row['product_description'],
        'product_short_description' => $row['product_short_description'],
        'product_price' => $row['product_price'],
        'product_old_price' => $row['product_old_price'],
        'product_quantity' => $row['product_quantity'],
        'category_name' => $row['category_name'],
        'image_filename' => $row['image_filename'],
        'color_name' => $row['color_name'],
        'product_detail_price' => $row['product_detail_price'],
        'size_name' => $row['size_name'],
        'product_detail_quantity' => $row['product_detail_quantity']
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop quần áo</title>
    <link rel="stylesheet" href="app.css">
    <?php include_once __DIR__ . '/layouts/styles.php'; ?>
</head>
<body>
<div class="container-fluid">
    <?php include_once __DIR__ . '/layouts/header.php'; ?>
    <hr class="featurette-divider">

    <!-- Banner dịch vụ -->
    <div class="row">
        <div class="col-12">
            <div class="service-section">
                <div class="service-box">
                    <img src="/shopquanao.com/admin/assets/img/icon/truck.png" alt="Vận chuyển & Trang trí">
                    <h4>Vận chuyển & Trang trí</h4>
                    <p>Trọn gói trang trí, miễn phí vận chuyển</p>
                </div>
                <div class="service-box">
                    <img src="/shopquanao.com/admin/assets/img/icon/discount.png" alt="Khuyến mãi hấp dẫn">
                    <h4>Khuyến mãi hấp dẫn</h4>
                    <p>Giảm giá lên tới 30%</p>
                </div>
                <div class="service-box">
                    <img src="/shopquanao.com/admin/assets/img/icon/return.png" alt="Miễn phí đổi trả">
                    <h4>Miễn phí đổi trả</h4>
                    <p>07 ngày đổi trả miễn phí</p>
                </div>
                <div class="service-box">
                    <img src="/shopquanao.com/admin/assets/img/icon/support.png" alt="Hỗ trợ nhanh chóng">
                    <h4>Hỗ trợ nhanh chóng</h4>
                    <p>Tư vấn và hỗ trợ liên tục</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="container-fluid">
        <h3 class="section-title">Khám phá những mẫu áo thun hot nhất hiện nay</h3><br>
        <div class="row row-cols-1 row-cols-md-6 g-4">
                <?php foreach ($arrDanhSachSanPham as $sp): ?>
                    <?php if (mb_strtolower($sp['category_name']) === 'áo thun'): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="/shopquanao.com/uploads/<?= htmlspecialchars($sp['image_filename'] ?? 'no-image.png') ?>" class="card-img-top" alt="Ảnh sản phẩm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($sp['product_name']) ?></h5>
                                <p class="card-text text-danger"><?= number_format($sp['product_price'], 0, ',', '.') ?> đ</p>
                                <p class="card-text text-muted" style="font-size: 14px;">Màu: <?= htmlspecialchars($sp['color_name']) ?> - Size: <?= htmlspecialchars($sp['size_name']) ?></p>
                                <a href="chi_tiet.php?product_id=<?= $sp['product_id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <br>
    <!-- Giới thiệu thương hiệu -->
    <div class="container">
        <div class="row featurette">
            <div class="col-md-7 d-flex align-items-center">
                <div class="viora-belle-intro" style="line-height: 1.8; padding: 30px;">
                    <h2 style="color:rgb(114, 108, 108); font-weight: bold; text-align: center;">VIORA BELLE – Tôn vinh giá trị thẩm mỹ thanh lịch</h2>
                    <p style="text-align: justify;">Với định hướng phát triển bền vững trong lĩnh vực thời trang nữ,
                        <strong>VIORA BELLE</strong> là điểm đến tin cậy dành cho những khách hàng yêu thích sự tinh tế, trang nhã nhưng vẫn năng động. Sản phẩm được tuyển chọn kỹ lưỡng, thiết kế theo xu hướng hiện đại và gia công tỉ mỉ nhằm đảm bảo trải nghiệm thẩm mỹ lẫn sự thoải mái tối đa.</p>
                </div>
            </div>
            <div class="col-md-5 d-flex align-items-center">
                <img src="/shopquanao.com/admin/assets/img/image1.jpg" alt="Hình ảnh VIORA BELLE" class="img-fluid mx-auto d-block" width="650">
            </div>
        </div>
    </div>
    <br>
    <h3 class="section-title">Khám phá những mẫu váy hot nhất hiện nay</h3><br>
        <div class="row row-cols-1 row-cols-md-6 g-4">
            <?php foreach ($arrDanhSachSanPham as $sp): ?>
                <?php if (mb_strtolower($sp['category_name']) === 'váy'): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="/shopquanao.com/uploads/<?= htmlspecialchars($sp['image_filename'] ?? 'no-image.png') ?>" class="card-img-top" alt="Ảnh sản phẩm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($sp['product_name']) ?></h5>
                                <p class="card-text text-danger"><?= number_format($sp['product_price'], 0, ',', '.') ?> đ</p>
                                <p class="card-text text-muted" style="font-size: 14px;">Màu: <?= htmlspecialchars($sp['color_name']) ?> - Size: <?= htmlspecialchars($sp['size_name']) ?></p>
                                <a href="chi_tiet.php?product_id=<?= $sp['product_id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <br>
        <hr class="featurette-divider"><br>
        <?php include_once __DIR__ . '/chatbot.php'; ?>
    <?php include_once __DIR__ . '/layouts/footer.php'; ?>
</div>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
