<?php
include_once 'dbconnect.php';

$categories = $_GET['categories'] ?? [];
$prices     = $_GET['prices'] ?? [];
$sizes      = $_GET['sizes'] ?? [];
$colors     = $_GET['colors'] ?? [];

$sql = "
    SELECT 
        p.product_id, 
        p.product_name, 
        p.product_price, 
        img.image_filename
    FROM products p
    LEFT JOIN product_details pd ON p.product_id = pd.product_id
    LEFT JOIN color col ON pd.color_id = col.color_id
    LEFT JOIN sizes s ON pd.size_id = s.size_id
    LEFT JOIN (
        SELECT product_id, MIN(image_filename) AS image_filename
        FROM images
        GROUP BY product_id
    ) img ON img.product_id = p.product_id
    WHERE 1
";

$params = [];
$types = "";

if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $sql .= " AND p.category_id IN ($placeholders)";
    $params = array_merge($params, $categories);
    $types .= str_repeat('i', count($categories)); // category_id là số nguyên
}

if (!empty($colors)) {
    $placeholders = implode(',', array_fill(0, count($colors), '?'));
    $sql .= " AND col.color_id IN ($placeholders)";
    $params = array_merge($params, $colors);
    $types .= str_repeat('i', count($colors));
}

if (!empty($sizes)) {
    $placeholders = implode(',', array_fill(0, count($sizes), '?'));
    $sql .= " AND s.size_id IN ($placeholders)";
    $params = array_merge($params, $sizes);
    $types .= str_repeat('i', count($sizes));
}

$priceConditions = [];
foreach ($prices as $price) {
    if (preg_match('/^<\s*(\d+)/', $price, $m)) {
        $priceConditions[] = "p.product_price < " . intval($m[1]);
    } elseif (preg_match('/^>\s*(\d+)/', $price, $m)) {
        $priceConditions[] = "p.product_price > " . intval($m[1]);
    } elseif (preg_match('/^(\d+)-(\d+)$/', $price, $m)) {
        $priceConditions[] = "(p.product_price >= {$m[1]} AND p.product_price <= {$m[2]})";
    }
}
if (!empty($priceConditions)) {
    $sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
}

$sql .= " GROUP BY p.product_id";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
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
                <div class="col-2">
                    <?php include_once __DIR__ . '/layouts/sidebar.php'; ?>
                </div>
                <div class="col-10">
                        <h4 class="mb-3">Kết quả tìm kiếm:</h4>
                        <?php if ($result->num_rows > 0): ?>
                        <div class="row row-cols-1 row-cols-md-5 g-4">
                            <?php while ($sp = $result->fetch_assoc()): ?>
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
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                            <p>Không tìm thấy sản phẩm.</p>
                        <?php endif; ?>
                </div>
            </div>
          </div>
    </div>
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
