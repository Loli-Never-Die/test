<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

if(!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Lấy sản phẩm trong giỏ hàng
$stmt = $conn->prepare("
    SELECT c.*, p.ProductName, p.Price, p.Image 
    FROM Cart c 
    JOIN Products p ON c.ProductID = p.ProductID 
    WHERE c.UserID = ?
");
$stmt->execute([$_SESSION['UserID']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach($cart_items as $item) {
    $total += $item['Price'] * $item['Quantity'];
}
?>

<!-- Hiển thị giỏ hàng -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h3 class="title">Giỏ hàng của bạn</h3>
                </div>
                <?php if(empty($cart_items)): ?>
                    <p>Chưa có sản phẩm nào trong giỏ hàng</p>
                <?php else: ?>
                    <!-- Hiển thị sản phẩm trong giỏ hàng -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-widget">
                                        <div class="product-img">
                                            <img src="<?php echo $item['Image']; ?>" alt="">
                                        </div>
                                        <div class="product-body">
                                            <h3 class="product-name">
                                                <a href="product.php?id=<?php echo $item['ProductID']; ?>">
                                                    <?php echo $item['ProductName']; ?>
                                                </a>
                                            </h3>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['Price']); ?>đ</td>
                                <td>
                                    <div class="input-number">
                                        <input type="number" value="<?php echo $item['Quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['Quantity']; ?>"
                                               onchange="updateQuantity(<?php echo $item['ProductID']; ?>, this.value)">
                                        <span class="qty-up">+</span>
                                        <span class="qty-down">-</span>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['Price'] * $item['Quantity']); ?>đ</td>
                                <td>
                                    <button class="delete" onclick="removeFromCart(<?php echo $item['ProductID']; ?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                <td><strong><?php echo number_format($total); ?>đ</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="text-right">
                        <a href="checkout.php" class="primary-btn">Thanh toán</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Cập nhật số lượng
function updateQuantity(productId, quantity) {
    $.ajax({
        url: 'ajax/update-cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity,
            action: 'update'
        },
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                location.reload(); // Tải lại trang để cập nhật tổng tiền
            } else {
                alert(response.message);
            }
        }
    });
}

// Xóa sản phẩm khỏi giỏ hàng
function removeFromCart(productId) {
    if(confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        $.ajax({
            url: 'ajax/update-cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                action: 'remove'
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    }
}

// Xử lý nút tăng giảm số lượng
$(document).ready(function() {
    $('.qty-up').click(function() {
        var input = $(this).parent().find('input');
        var max = parseInt(input.attr('max'));
        var value = parseInt(input.val());
        if(value < max) {
            input.val(value + 1);
            input.trigger('change');
        }
    });

    $('.qty-down').click(function() {
        var input = $(this).parent().find('input');
        var value = parseInt(input.val());
        if(value > 1) {
            input.val(value - 1);
            input.trigger('change');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 