<?php
include_once __DIR__ . '/dbconnect.php';

// Lấy từ khóa tìm kiếm
$keyword = $_GET['keyword'] ?? '';

// Truy vấn sản phẩm (chỉ JOIN với categories, KHÔNG JOIN product_details, sizes, color)
$sql = "
    SELECT DISTINCT
        p.product_id,
        p.product_name,
        p.product_price,
        p.image_url,
        c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE 1=1
";

$params = [];
$types = '';

if ($keyword !== '') {
    $sql .= " AND (
        p.product_name LIKE ? OR 
        c.category_name LIKE ? OR 
        EXISTS (
            SELECT 1 
            FROM product_details pd 
            JOIN sizes s ON pd.size_id = s.size_id 
            WHERE pd.product_id = p.product_id AND s.size_name LIKE ?
        ) OR 
        EXISTS (
            SELECT 1 
            FROM product_details pd 
            JOIN color cl ON pd.color_id = cl.color_id 
            WHERE pd.product_id = p.product_id AND cl.color_name LIKE ?
        )
    )";
    $kw = '%' . $keyword . '%';
    $params = [$kw, $kw, $kw, $kw];
    $types = 'ssss';
}

// Thực hiện truy vấn
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Lấy danh sách sản phẩm
$arrDanhSachSanPham = [];
while ($row = mysqli_fetch_assoc($result)) {
    $arrDanhSachSanPham[] = $row;
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
                <h4 class="mb-4">Kết quả tìm kiếm cho từ khóa: <strong><?= htmlspecialchars($keyword) ?></strong></h4>
                <div class="row row-cols-1 row-cols-md-5 g-4">
                    <?php foreach ($arrDanhSachSanPham as $sp): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="/shopquanao.com/uploads/<?= htmlspecialchars($sp['image_url'] ?? 'no-image.png') ?>" class="card-img-top" alt="Ảnh sản phẩm">
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
    <?php include_once __DIR__ . '/chatbot.php'; ?>
</div>        
</body>
</html>
