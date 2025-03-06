<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];

    // Insert customer without OrderID
    $sql = "INSERT INTO Customer (CustomerName, CustomerAddress) VALUES ('$customer_name', '$customer_address')";
    if ($conn->query($sql)) {
        $customer_id = $conn->insert_id;

        // Insert purchase order
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'];
        }

        $sql = "INSERT INTO PurchaseOrder (OrderQuantity, ProductID, CustomerID, CustomerAddress, TotalPrice)
                VALUES (1, {$item['product_id']}, $customer_id, '$customer_address', $total)";
        if ($conn->query($sql)) {
            // Clear the cart
            $_SESSION['cart'] = [];
            header('Location: index.php');
            exit();
        } else {
            $error = "Error creating purchase order: " . $conn->error;
        }
    } else {
        $error = "Error registering customer: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Checkout</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>
            <div class="mb-3">
                <label for="customer_address" class="form-label">Address</label>
                <input type="text" class="form-control" id="customer_address" name="customer_address" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Place Order</button>
        </form>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>