<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">
  <a class="navbar-brand" href="#">VIORA BELLE</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarContent">
    <div class="d-flex align-items-center ms-auto">
      <div class="search-bar me-3">
        <div class="input-group" style="width: 100%;">
          <!-- <form action="timkiem.php" method="GET" class="d-flex">
            <input type="text" class="form-control rounded-start-pill" placeholder="Tìm kiếm...">
            <button class="btn btn-outline-secondary rounded-end-pill" type="button">Search</button>
          </form> -->
          <form action="timkiem.php" method="GET" class="d-flex">
              <input type="text" name="keyword" class="form-control rounded-start-pill" placeholder="Tìm kiếm...">
              <button class="btn btn-outline-secondary rounded-end-pill" type="submit">Search</button>
          </form>

        </div>
      </div>
      <ul class="navbar-nav mb-2 mb-lg-0 gap-3">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="sanpham.php">Sản phẩm</a></li>
        <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin tức</a></li>
        <li class="nav-item"><a class="nav-link" href="lienhe.php">Liên hệ</a></li>
        <li class="nav-item"><a class="nav-link" href="khuyenmai.php"><i class="fa-solid fa-gift me-2" style="color:#F2DB07;"></i> Khuyến mãi</a></li>
        <li class="nav-item"><a class="nav-link" href="lichsumuahang.php">Đơn hàng</a></li>
        <li class="nav-item"><a class="nav-link" href="danhgia.php">Đánh giá</a></li>
      </ul>
    </div>
    <div class="d-flex align-items-center gap-3 ms-3">
      <div class="cart-icon"><a href="giohang.php"><i class="fas fa-shopping-cart text-secondary"></i></a></div>
      <button class="btn btn-primary"><a href="/shopquanao.com/dangnhap/" style="text-decoration:none; color:aliceblue; ">Sign-up</a></button>
    </div>
  </div>
</nav>
<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="/shopquanao.com/admin/assets/img/nen_2.jpg" class="d-block w-100" style="height:500px;" alt="...">
    </div>
    <div class="carousel-item">
      <img src="/shopquanao.com/admin/assets/img/nen6.jpg" class="d-block w-100" style="height:500px;" alt="...">
    </div>
    <div class="carousel-item">
      <img src="/shopquanao.com/admin/assets/img/nen5.jpg" class="d-block w-100" style="height:500px;" alt="...">
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
