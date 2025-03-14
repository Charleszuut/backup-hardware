<?php
session_start();
include 'includes/db.php';

// Redirect if the cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// Process the checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];

    // Insert customer into the Customer table
    $sql = "INSERT INTO Customer (CustomerName, CustomerAddress) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $customer_address);

    if ($stmt->execute()) {
        $customer_id = $stmt->insert_id;

        // Calculate total price
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        // Insert purchase order
        $sql = "INSERT INTO PurchaseOrder (CustomerID, TotalPrice, Status) VALUES (?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $customer_id, $total_price);

        if ($stmt->execute()) {
            $purchase_order_id = $stmt->insert_id;

            // Insert purchase order lines
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity']; // Get quantity from the cart item
                $unit_price = $item['price']; // Get unit price from the cart item
                $total_item_price = $unit_price * $quantity; // Calculate total price for the item
                $supplierid = isset($item['supplierid']) ? $item['supplierid'] : null;

                if ($supplierid === null) {
                    die("Error: SupplierID is missing for ProductID $product_id.");
                }

                // Check if SupplierID exists in the supplier table
                $check_sql = "SELECT SupplierID FROM supplier WHERE SupplierID = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $supplierid);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows == 0) {
                    die("Error: SupplierID $supplierid does not exist in the supplier table for ProductID $product_id.");
                }

                // Insert into PurchaseOrderLine
                $sql = "INSERT INTO PurchaseOrderLine (PurchaseOrderID, ProductID, Quantity, UnitPrice, TotalPrice, SupplierID)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiddi", $purchase_order_id, $product_id, $quantity, $unit_price, $total_item_price, $supplierid);

                if (!$stmt->execute()) {
                    error_log("Failed to insert purchase order line: " . $stmt->error);
                    die("Error inserting purchase order line: " . $stmt->error);
                }
            }

            // Clear the cart
            $_SESSION['cart'] = [];
            header('Location: transactions.php');
            exit();
        } else {
            error_log("Failed to create purchase order: " . $stmt->error);
            die("Error creating purchase order: " . $stmt->error);
        }
    } else {
        error_log("Failed to register customer: " . $stmt->error);
        die("Error registering customer: " . $stmt->error);
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
    <?php include 'includes/header_index.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Checkout</h2>
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