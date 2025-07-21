<?php
session_start();
include_once __DIR__ . '/dbconnect.php';

$product_id = $_GET['product_id'] ?? 0;
if (!$product_id) {
    echo "Thiếu ID sản phẩm.";
    exit;
}

// 1. Thông tin sản phẩm
$sqlProduct = "
    SELECT 
        p.product_id,
        p.product_name,
        p.product_description,
        p.product_short_description,
        p.product_price,
        p.product_old_price,
        p.product_quantity,
        p.image_url,
        c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = $product_id
    LIMIT 1
";
$resultProduct = mysqli_query($conn, $sqlProduct);
if (!$resultProduct || mysqli_num_rows($resultProduct) === 0) {
    echo "Không tìm thấy sản phẩm.";
    exit;
}
$sp = mysqli_fetch_array($resultProduct, MYSQLI_ASSOC);

// 2. Ảnh phụ
$sqlImages = "SELECT image_filename FROM images WHERE product_id = $product_id";
$resultImages = mysqli_query($conn, $sqlImages);
$allImages = [];
while ($row = mysqli_fetch_assoc($resultImages)) {
    $allImages[] = $row['image_filename'];
}

// 3. Màu sắc
$sqlColors = " SELECT DISTINCT c.color_name
    FROM product_details pd
    JOIN color c ON c.color_id = pd.color_id
    WHERE pd.product_id = $product_id
";
$resultColors = mysqli_query($conn, $sqlColors);
$colors = [];
while ($row = mysqli_fetch_assoc($resultColors)) {
   $colors[] = $row['color_name'];
}

// 4. Tổng số lượng theo từng size
$sqlSizes = " SELECT s.size_name AS size, SUM(pd.product_detail_quantity) AS total_quantity
    FROM product_details pd
    JOIN sizes s ON s.size_id = pd.size_id
    WHERE pd.product_id = $product_id
    GROUP BY s.size_name
";

$resultSizes = mysqli_query($conn, $sqlSizes);
$sizes = [];
while ($row = mysqli_fetch_assoc($resultSizes)) {
    $sizes[] = $row;
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
        .thumbnail {
            width: 80px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 4px;
            border-radius: 4px;
            transition: transform 0.2s;
        }
        .thumbnail:hover {
            transform: scale(1.05);
        }
        .main-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border: 1px solid #ccc;
            padding: 6px;
            border-radius: 6px;
        }
        .scroll-thumbs {
            max-height: 500px;
            overflow-y: auto;
        }
        input[type="radio"] {
            display: none;
        }
        input[type="radio"]:checked + label {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        label.btn {
            cursor: pointer;
            user-select: none;
        }
        .description-wrapper {
            position: relative;
            max-height: 3.5em; /* ~2 dòng */
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .description-wrapper.expanded {
            max-height: 1000px;
        }

        .toggle-btn {
            color: #0d6efd;
            background: none;
            border: none;
            padding: 0;
            margin-top: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        label.btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
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
    <div class="row mt-4">
    <!-- Cột ảnh phụ -->
    <div class="col-md-2 scroll-thumbs d-flex flex-column align-items-center">
        <?php foreach ($allImages as $img): ?>
            <img src="/shopquanao.com/uploads/<?= htmlspecialchars($img) ?>" class="thumbnail" alt="Ảnh phụ">
        <?php endforeach; ?>
    </div>
    <!-- Cột ảnh chính -->
    <div class="col-md-5">
        <img src="/shopquanao.com/uploads/<?= htmlspecialchars($sp['image_url']) ?>" class="main-image" alt="Ảnh chính">
    </div>
    <!-- Cột thông tin mô tả và giá -->
        <div class="col-md-5">
            <p><strong>Giá khuyến mãi:</strong>
                <span class="text-danger"><?= number_format($sp['product_price'], 0, ',', '.') ?> đ</span>
                <?php if ($sp['product_old_price']): ?>&nbsp;|&nbsp;
                    <strong>Giá gốc:</strong>
                    <del><?= number_format($sp['product_old_price'], 0, ',', '.') ?> đ</del>
                <?php endif; ?>
            </p>
            <p><strong>Mô tả ngắn:</strong><br><?= nl2br(htmlspecialchars($sp['product_short_description'])) ?></p>
            <!-- Chi tiết -->
            <p class="mt-3"><strong>Chi tiết:</strong></p>
            <div class="description-wrapper" id="full-description">
                <?= nl2br(htmlspecialchars($sp['product_description'])) ?>
            </div>
            <button class="toggle-btn" onclick="toggleDescription('full-description', this)">Xem thêm</button>
            <!--<p><strong>Chi tiết:</strong><br><?= nl2br(htmlspecialchars($sp['product_description'])) ?></p> -->
            <p><strong>Danh mục:</strong> <?= htmlspecialchars($sp['category_name']) ?></p>
            <p><strong>Tổng số lượng còn:</strong> <?= (int)$sp['product_quantity'] ?></p>
            <?php if ((int)$sp['product_quantity'] > 0): ?>
                <form action="themvaogio.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $sp['product_id'] ?>">

                    <p><strong>Màu sắc:</strong></p>
                    <?php if (!empty($colors)): ?>
                        <div class="mb-2 d-flex flex-wrap gap-2">
                            <?php foreach ($colors as $color): ?>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="color" value="<?= htmlspecialchars($color) ?>" required hidden>
                                    <?= htmlspecialchars($color) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- <p>Không có màu sắc nào.</p> -->
                    <div class="alert alert-warning" role="alert">Không có màu sắc nào.</div>

                    <?php endif; ?>

                    <p><strong>Kích cỡ:</strong></p>
                    <?php if (!empty($sizes)): ?>
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            <?php foreach ($sizes as $sz): ?>
                            <?php $qty = (int)$sz['total_quantity']; ?>
                            <label class="btn btn-outline-secondary">
                                <input 
                                    type="radio" 
                                    name="size" 
                                    value="<?= htmlspecialchars($sz['size']) ?>" 
                                    required 
                                    hidden 
                                    data-quantity="<?= $qty ?>"
                                >
                                <?= htmlspecialchars($sz['size']) ?> (Tổng: <?= $qty ?>)
                            </label>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Không có kích cỡ nào.</p>
                    <?php endif; ?>   

                    <label for="quantity"><strong>Số lượng:</strong></label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control w-25 mb-3">

                    <div class="mt-4 d-flex gap-3">
                        <button type="submit" class="btn btn-success btn-lg">Thêm vào giỏ</button>
                        <a href="sanpham.php" class="btn btn-secondary btn-lg mt-2">Quay lại</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger mt-3"><strong>Sản phẩm đã hết hàng!</strong></div>
                <a href="sanpham.php" class="btn btn-secondary btn-lg mt-2">Quay lại</a>
            <?php endif; ?>

        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <?php include_once __DIR__ . '/danhgia-sp.php'; ?>
        </div>
    </div>
    </div>
    <hr class="featurette-divider mt-5">
    <?php include_once __DIR__ . '/layouts/footer.php'; ?>
</div>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
<script>
function toggleDescription(id, btn) {
    const desc = document.getElementById(id);
    desc.classList.toggle("expanded");
    btn.textContent = desc.classList.contains("expanded") ? "Thu gọn" : "Xem thêm";
}
</script>
<script>
function toggleDescription(id, btn) {
    const desc = document.getElementById(id);
    desc.classList.toggle("expanded");
    btn.textContent = desc.classList.contains("expanded") ? "Thu gọn" : "Xem thêm";
}

// Kích hoạt lựa chọn màu
document.querySelectorAll('input[name="color"]').forEach((input) => {
    input.addEventListener('change', () => {
        document.querySelectorAll('input[name="color"]').forEach(i => {
            i.parentElement.classList.remove('active');
        });
        input.parentElement.classList.add('active');
    });
});

// Kích hoạt lựa chọn size
document.querySelectorAll('input[name="size"]').forEach((input) => {
    input.addEventListener('change', () => {
        document.querySelectorAll('input[name="size"]').forEach(i => {
            i.parentElement.classList.remove('active');
        });
        input.parentElement.classList.add('active');
    });
});
</script>
<script>
document.querySelectorAll('input[name="size"]').forEach((input) => {
    input.addEventListener('change', () => {
        // Bỏ active khỏi tất cả
        document.querySelectorAll('input[name="size"]').forEach(i => {
            i.parentElement.classList.remove('active');
        });
        // Active size vừa chọn
        input.parentElement.classList.add('active');

        // Lấy số lượng tối đa từ data
        const maxQty = parseInt(input.dataset.quantity);
        const qtyInput = document.getElementById('quantity');
        qtyInput.max = maxQty;

        // Nếu số lượng hiện tại lớn hơn max, thì điều chỉnh lại
        if (parseInt(qtyInput.value) > maxQty) {
            qtyInput.value = maxQty;
        }
    });
});
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
