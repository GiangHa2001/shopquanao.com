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
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/layouts/header.php'; ?>
        </div>
    </div>
    <hr class="featurette-divider"><br>
    <div class="container-fluid">
        <div class="row">
            <div class="col-2">
                <?php include_once __DIR__ . '/layouts/sidebar.php'; ?>
            </div>
            <div class="col-10">
                <div class="row row-cols-1 row-cols-md-5 g-4">
                    <?php foreach ($arrDanhSachSanPham as $sp): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="/shopquanao.com/uploads/<?= htmlspecialchars($sp['image_filename'] ?? 'no-image.png') ?>" class="card-img-top" alt="Ảnh sản phẩm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($sp['product_name']) ?></h5>
                                    <p class="card-text text-danger"><?= number_format($sp['product_price'], 0, ',', '.') ?> đ</p>
                                    <div class="d-flex gap-2 justify-content-between">
                                        <a href="chi_tiet.php?product_id=<?= $sp['product_id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                                        <form action="themvaogio.php" method="post" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?= $sp['product_id'] ?>">
                                            <!-- <button type="submit" class="btn btn-success btn-sm">Mua</button> -->
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>          
    <br>
<hr class="featurette-divider">
  <div class="row">
    <div class="col-12"><br>
      <?php include_once __DIR__ . '/layouts/footer.php'; ?>
    </div>
  </div>
</div>
<?php include_once __DIR__ . '/chatbot.php'; ?>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
