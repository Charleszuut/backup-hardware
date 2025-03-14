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
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $customer_address = htmlspecialchars($_POST['customer_address']);

    // Insert customer into the Customer table
    $sql = "INSERT INTO Customer (CustomerName, CustomerAddress) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for Customer insert: " . $conn->error);
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ss", $customer_name, $customer_address);

    if ($stmt->execute()) {
        $customer_id = $stmt->insert_id;
        $stmt->close();

        // Calculate total price
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        // Insert purchase order
        $created_date = date('Y-m-d H:i:s');
        $payment_status = 'Pending';
        $notes = '';

        $sql = "INSERT INTO PurchaseOrder (CustomerID, CustomerAddress, TotalPrice, Status, CreatedDate, PaymentStatus, Notes) 
                VALUES (?, ?, ?, 'Pending', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare statement for PurchaseOrder insert: " . $conn->error);
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ssdsss", $customer_id, $customer_address, $total_price, $created_date, $payment_status, $notes);

        if ($stmt->execute()) {
            $purchase_order_id = $stmt->insert_id;
            $stmt->close();

            // Insert purchase order lines
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $unit_price = $item['price'];
                $total_item_price = $unit_price * $quantity;
                $supplierid = isset($item['supplierid']) ? $item['supplierid'] : null;

                if ($supplierid === null || !is_numeric($supplierid) || $supplierid <= 0) {
                    error_log("Invalid SupplierID for ProductID $product_id: " . var_export($supplierid, true));
                    die("Error: Invalid or missing SupplierID for ProductID $product_id.");
                }

                // Check SupplierID
                $check_stmt = $conn->prepare("SELECT SupplierID FROM supplier WHERE SupplierID = ?");
                $check_stmt->bind_param("i", $supplierid);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows == 0) {
                    error_log("SupplierID $supplierid does not exist.");
                    die("Error: SupplierID $supplierid does not exist.");
                }
                $check_stmt->close();

                // Check ProductID and get ProductName
                $product_stmt = $conn->prepare("SELECT ProductName FROM Products WHERE ProductID = ?");
                $product_stmt->bind_param("i", $product_id);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                if ($product_result->num_rows == 0) {
                    error_log("ProductID $product_id does not exist.");
                    die("Error: ProductID $product_id does not exist.");
                }
                $product = $product_result->fetch_assoc();
                $product_name = $product['ProductName'];
                $product_stmt->close();

                // Insert into PurchaseOrderLine
                $line_stmt = $conn->prepare("INSERT INTO PurchaseOrderLine (PurchaseOrderID, ProductID, Quantity, UnitPrice, TotalPrice, SupplierID, ProductName) 
                                             VALUES (?, ?, ?, ?, ?, ?, ?)");
                $line_stmt->bind_param("iiiddis", $purchase_order_id, $product_id, $quantity, $unit_price, $total_item_price, $supplierid, $product_name);
                if (!$line_stmt->execute()) {
                    error_log("Failed to insert purchase order line: " . $line_stmt->error);
                    die("Error inserting purchase order line: " . $line_stmt->error);
                }
                $line_stmt->close();
            }

            // Store CustomerID in session and clear the cart
            $_SESSION['customer_id'] = $customer_id; // Store CustomerID for use in customer_transactions.php
            $_SESSION['cart'] = [];
            header('Location: customer_transactions.php'); // Redirect to customer transactions page
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