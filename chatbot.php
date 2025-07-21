<?php
include_once __DIR__ . '/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message']);
    $msgSanitized = mysqli_real_escape_string($conn, $msg);

    if ($msgSanitized !== '') {
        $sql = "INSERT INTO contact_messages (content) VALUES ('$msgSanitized')";
        mysqli_query($conn, $sql);

        $lowerMsg = strtolower($msgSanitized);

        if (strpos($lowerMsg, 'giờ') !== false && strpos($lowerMsg, 'mở') !== false) {
            $rs = mysqli_query($conn, "SELECT open_hours FROM shop_info LIMIT 1");
            $row = mysqli_fetch_assoc($rs);
            echo '<i class="fa-solid fa-clock"></i> Shop mở cửa: ' . htmlspecialchars($row['open_hours']);
            exit;
        }

        if (strpos($lowerMsg, 'địa chỉ') !== false || strpos($lowerMsg, 'ở đâu') !== false) {
            $rs = mysqli_query($conn, "SELECT address FROM shop_info LIMIT 1");
            $row = mysqli_fetch_assoc($rs);
            echo '<i class="fa-solid fa-map-pin"></i> Địa chỉ shop: ' . htmlspecialchars($row['address']);
            exit;
        }

        if (strpos($lowerMsg, 'số điện thoại') !== false || strpos($lowerMsg, 'hotline') !== false) {
            $rs = mysqli_query($conn, "SELECT phone FROM shop_info LIMIT 1");
            $row = mysqli_fetch_assoc($rs);
            echo '<i class="fa-solid fa-phone"></i> Hotline: ' . htmlspecialchars($row['phone']);
            exit;
        }

        if (strpos($lowerMsg, 'ship') !== false || strpos($lowerMsg, 'giao hàng') !== false) {
            echo '<i class="fa-solid fa-truck-fast"></i> Shop có hỗ trợ giao hàng toàn quốc. Bạn đặt hàng nhé!';
            exit;
        }

        if (strpos($lowerMsg, 'đổi hàng') !== false || strpos($lowerMsg, 'đổi trả') !== false) {
            echo '<i class="fa-solid fa-arrow-right-arrow-left"></i> Shop hỗ trợ đổi hàng trong 7 ngày với điều kiện sản phẩm còn nguyên tag và chưa qua sử dụng.';
            exit;
        }

        $sqlProd = "SELECT product_name, product_price, product_quantity, image_url 
                    FROM products 
                    WHERE product_name LIKE '%$msgSanitized%' 
                    LIMIT 5";
        $result = mysqli_query($conn, $sqlProd);

        if (mysqli_num_rows($result) > 0) {
            echo "<div>Sản phẩm bạn tìm gồm:</div>";
            while ($row = mysqli_fetch_assoc($result)) {
                $imgPath = !empty($row['image_url']) 
                    ? "/shopquanao.com/uploads/" . htmlspecialchars($row['image_url']) 
                    : "/shopquanao.com/uploads/no-image.png";

                echo "
                    <div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc; border-radius: 5px; display: flex; align-items: center; gap: 10px;'>
                        <img src='{$imgPath}' alt='{$row['product_name']}' style='width: 80px; height: 80px; object-fit: cover; border-radius: 5px;'>
                        <div>
                            <strong>{$row['product_name']}</strong><br>
                            Số lượng: {$row['product_quantity']}<br>
                            Giá: " . number_format($row['product_price'], 0, ',', '.') . "đ
                        </div>
                    </div>
                ";
            }
        } else {
            echo '<i class="fa-solid fa-x"></i> Không tìm thấy sản phẩm phù hợp với từ khóa <strong>$msgSanitized</strong>.';
        }
    } else {
        echo '<i class="fa-solid fa-triangle-exclamation" style="color: #FFD43B;"></i> Bạn chưa nhập nội dung.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chatbot - Shop</title>
  <style>
    #chat-icon {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #007bff;
      color: white;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      font-size: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 1000;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    #chatbox-customer {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: white;
      border: 1px solid #ccc;
      width: 400px;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
      z-index: 1001;
      display: none;
    }

    #chatbox-customer h4 {
      margin: 0;
      color: #007bff;
      display: flex;
      justify-content: space-between;
    }

    #messages {
      max-height: 350px;
      overflow-y: auto;
      margin: 10px 0;
      font-size: 14px;
    }

    #chatbox-customer input {
      width: 70%;
      padding: 6px;
    }

    #chatbox-customer button {
      padding: 6px 10px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    #chatbox-customer button:hover {
      background: #218838;
    }

    #close-chat {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<!-- Icon để mở -->
<div id="chat-icon" onclick="openChat()"><i class="fa-solid fa-comments" style="color: #ebeff4;"></i></div>

<!-- Khung chat ẩn -->
<div id="chatbox-customer">
  <h5>Hỗ trợ khách hàng
    <button id="close-chat" onclick="closeChat()"><i class="fa-solid fa-x"></i></button>
  </h5>
  <div id="messages"></div>
  <input type="text" id="inputMsg" placeholder="Nhập nội dung...">
  <button onclick="sendCustomerChat()">Gửi</button>
</div>

<script>
function openChat() {
  document.getElementById("chatbox-customer").style.display = "block";
  document.getElementById("chat-icon").style.display = "none";
}

function closeChat() {
  document.getElementById("chatbox-customer").style.display = "none";
  document.getElementById("chat-icon").style.display = "flex";
}

function sendCustomerChat() {
  const input = document.getElementById("inputMsg");
  const message = input.value.trim();
  if (!message) return;

  fetch("chatbot.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "message=" + encodeURIComponent(message)
  })
  .then(res => res.text())
  .then(data => {
    const messagesDiv = document.getElementById("messages");
    messagesDiv.innerHTML = 
      "<p><b>Bot:</b> " + data + "</p>" +
      "<p><b>Bạn:</b> " + message + "</p>" +
      messagesDiv.innerHTML;
    input.value = "";
  });
}
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
