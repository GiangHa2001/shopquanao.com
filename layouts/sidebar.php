<?php include_once 'dbconnect.php'; ?>
<div class="filter-content">
  <div class="filter-container">

    <!-- Danh mục sản phẩm -->
    <aside class="aside-item">
      <h5>Danh mục sản phẩm</h5>
      <ul>
        <?php
        $category_sql = "SELECT category_id, category_name FROM categories";
        $category_result = mysqli_query($conn, $category_sql);
        while ($cat = mysqli_fetch_assoc($category_result)):
        ?>
          <li>
            <label>
              <input type="checkbox" name="category[]" value="<?= $cat['category_id'] ?>"> <?= htmlspecialchars($cat['category_name']) ?>
            </label>
          </li>
        <?php endwhile; ?>
      </ul>
    </aside>

    <!-- Màu sắc -->
    <aside class="aside-item">
      <h5>Màu sắc</h5>
      <ul>
        <?php
        $color_sql = "SELECT color_id, color_name FROM color";
        $color_result = mysqli_query($conn, $color_sql);
        while ($color = mysqli_fetch_assoc($color_result)):
        ?>
          <li>
            <label>
              <input type="checkbox" name="color[]" value="<?= $color['color_id'] ?>"> <?= htmlspecialchars($color['color_name']) ?>
            </label>
          </li>
        <?php endwhile; ?>
      </ul>
    </aside>

    <!-- Kích thước -->
    <aside class="aside-item">
      <h5>Kích thước</h5>
      <ul>
        <?php
        $size_sql = "SELECT size_id, size_name FROM sizes";
        $size_result = mysqli_query($conn, $size_sql);
        while ($size = mysqli_fetch_assoc($size_result)):
        ?>
          <li>
            <label>
              <input type="checkbox" name="size[]" value="<?= $size['size_id'] ?>"> <?= htmlspecialchars($size['size_name']) ?>
            </label>
          </li>
        <?php endwhile; ?>
      </ul>
    </aside>

    <!-- Giá -->
    <aside class="aside-item">
      <h5>Giá</h5>
      <ul>
        <li><label><input type="checkbox" name="price[]" value="<200000"> Dưới 200.000đ</label></li>
        <li><label><input type="checkbox" name="price[]" value="200000-400000"> 200.000 - 400.000đ</label></li>
        <li><label><input type="checkbox" name="price[]" value=">400000"> Trên 400.000đ</label></li>
      </ul>
    </aside>

  </div>
</div>

<script>
function getSelectedValues(name) {
  return Array.from(document.querySelectorAll('input[name="' + name + '"]:checked')).map(cb => cb.value);
}

function applyFilters() {
  const params = new URLSearchParams();
  getSelectedValues('category[]').forEach(val => params.append('categories[]', val));
  getSelectedValues('price[]').forEach(val => params.append('prices[]', val));
  getSelectedValues('size[]').forEach(val => params.append('sizes[]', val));
  getSelectedValues('color[]').forEach(val => params.append('colors[]', val));
  window.location.href = './filter.php?' + params.toString();
}

document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
  cb.addEventListener('change', applyFilters);
});
</script>
