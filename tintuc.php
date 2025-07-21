<?php
session_start();
// 1. Mở kết nối
include_once __DIR__ . '/dbconnect.php';

// 2. Câu lệnh SELECT đúng với tên cột
$sqlSelectNews =  " SELECT title, content, publish_date, author, image
    FROM shop_news
    ORDER BY publish_date DESC
";

// 3. Thực thi truy vấn
$resultNews = mysqli_query($conn, $sqlSelectNews);
if (!$resultNews) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

// 4. Lấy dữ liệu và gán đúng tên
$arrDanhSachTinTuc = [];
while($row = mysqli_fetch_array($resultNews, MYSQLI_ASSOC)) {
    $arrDanhSachTinTuc[] = array(
        'title' => $row['title'],
        'content' => $row['content'],
        'publish_date' => $row['publish_date'],
        'author' => $row['author'],
        'image' => $row['image']
    );
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
            <?php include_once __DIR__ . '/layouts/header.php'; ?><br>
            </div>
                <hr class="featurette-divider"><br>
            <div class="container">
                <h2 class=" mb-4 text-center">TIN TỨC THỜI TRANG</h2>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($arrDanhSachTinTuc as $news): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="/shopquanao.com/admin/assets/img/<?= htmlspecialchars($news['image'] ?? 'no-image.png') ?>" class="card-img-top" style="height:400px;" alt="Hình ảnh tin tức">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                    <p class="card-text"><?= nl2br(htmlspecialchars(mb_substr($news['content'], 0, 150))) ?>...</p>
                                </div>
                                <div class="card-footer text-muted">
                                    Ngày đăng: <?= date('d/m/Y', strtotime($news['publish_date'])) ?> | Tác giả: <?= htmlspecialchars($news['author']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div><br>
            </div>
            <hr class="featurette-divider"><br>
            <?php include_once __DIR__ . '/chatbot.php'; ?>
            <div class="col-12">
                <?php include_once __DIR__ . '/layouts/footer.php'; ?>
            </div>
        </div>
    </div>
<?php include_once __DIR__ . '/layouts/scripts.php'; ?>
</body>
</html>
