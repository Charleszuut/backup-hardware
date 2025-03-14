<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Handle removing items from the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product_id'])) {
    $remove_product_id = (int)$_POST['remove_product_id'];

    foreach ($_SESSION['punchin_cart'] as $key => $item) {
        if ($item['product_id'] == $remove_product_id) {
            unset($_SESSION['punchin_cart'][$key]);
            break;
        }
    }

    // Redirect to avoid form resubmission
    header('Location: punchin_cart.php');
    exit();
}

// Handle confirming the punch-in (redirect to checkout)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_punchin'])) {
    if (empty($_SESSION['punchin_cart'])) {
        $error = "Cart is empty. Add items to punch in.";
    } else {
        // Redirect to checkout page
        header('Location: punchin_checkout.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch-In Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Punch-In Cart</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Supplier ID</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_price = 0;
                if (!empty($_SESSION['punchin_cart'])) {
                    foreach ($_SESSION['punchin_cart'] as $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total_price += $item_total;
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['supplier_id']; ?></td>
                            <td>₱<?php echo number_format($item_total, 2); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="remove_product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    endforeach;
                } else {
                    echo '<tr><td colspan="6" class="text-center">Your punch-in cart is empty.</td></tr>';
                }
                ?>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total</td>
                    <td class="fw-bold">₱<?php echo number_format($total_price, 2); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="text-center">
            <a href="punch_in.php" class="btn btn-secondary">Add More Products</a>
            <?php if (!empty($_SESSION['punchin_cart'])): ?>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="confirm_punchin" class="btn btn-success">Confirm Punch-In</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>