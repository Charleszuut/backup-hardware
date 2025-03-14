<?php
session_start();
include 'includes/db.php';

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = (float)$_POST['price']; // Ensure price is a float
    $quantity = (int)$_POST['quantity']; // Ensure quantity is an integer
    $supplierid = (int)$_POST['supplierid']; // Ensure supplierid is an integer

    // Ensure quantity is at least 1
    if ($quantity < 1) {
        $quantity = 1; // Set quantity to 1 if it's less than 1
    }

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    $item_exists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            // Update quantity if the product is already in the cart
            $item['quantity'] += $quantity;
            $item_exists = true;
            break;
        }
    }

    // If the product is not in the cart, add it
    if (!$item_exists) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id, // Ensure this is included
            'product_name' => $product_name,
            'price' => $price,
            'quantity' => $quantity,
            'supplierid' => $supplierid
        ];
    }

    // Redirect to the cart page to avoid form resubmission
    header('Location: cart.php');
    exit();
}

// Handle removing items from the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product_id'])) {
    $remove_product_id = $_POST['remove_product_id'];

    // Loop through the cart and remove the product with the matching ID
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $remove_product_id) {
            unset($_SESSION['cart'][$key]); // Remove the item from the cart
            break;
        }
    }

    // Redirect to the cart page to avoid form resubmission
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header_index.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Your Cart</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Action</th> <!-- Add a column for the Remove button -->
                </tr>
            </thead>
            <tbody>
                <?php
                $total_price = 0;
                if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total_price += $item_total;
                ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['supplierid']; ?></td> <!-- Display supplierid -->
                            <td>₱<?php echo number_format($item_total, 2); ?></td>
                            <td>
                                <!-- Form to remove the product -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="remove_product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    endforeach;
                } else {
                    echo '<tr><td colspan="6" class="text-center">Your cart is empty.</td></tr>';
                }
                ?>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total</td>
                    <td class="fw-bold">₱<?php echo number_format($total_price, 2); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="text-center">
            <a href="index.php" class="btn btn-secondary">Back to Shop</a>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>