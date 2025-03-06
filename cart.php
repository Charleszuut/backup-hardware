<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];

    $_SESSION['cart'][] = [
        'product_id' => $product_id,
        'product_name' => $product_name,
        'price' => $price
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Shopping Cart</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $total += $item['price'];
                ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>₱<?php echo number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="text-center">
            <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
            <a href="index.php" class="btn btn-secondary btn-lg">Continue Shopping</a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>