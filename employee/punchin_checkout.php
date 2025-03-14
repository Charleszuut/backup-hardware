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

// Redirect if the cart is empty
if (!isset($_SESSION['punchin_cart']) || empty($_SESSION['punchin_cart'])) {
    header('Location: punch_in.php');
    exit();
}

// Process the checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = htmlspecialchars($_POST['customer_name'] ?? '');
    $customer_address = htmlspecialchars($_POST['customer_address'] ?? '');

    if (empty($customer_name) || empty($customer_address)) {
        $error = "Customer name and address are required.";
    } else if (strlen($customer_name) > 20) {
        $error = "Customer name exceeds maximum length of 20 characters.";
    } else if (strlen($customer_address) > 100) {
        $error = "Customer address exceeds maximum length of 100 characters.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Check if customer already exists
            $check_sql = "SELECT CustomerID FROM Customer WHERE CustomerName = ? AND CustomerAddress = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $customer_name, $customer_address);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $row = $check_result->fetch_assoc();
                $customer_id = $row['CustomerID'];
            } else {
                // Insert new customer
                $customer_sql = "INSERT INTO Customer (CustomerName, CustomerAddress) VALUES (?, ?)";
                $stmt = $conn->prepare($customer_sql);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement for Customer insert: " . $conn->error);
                }
                $stmt->bind_param("ss", $customer_name, $customer_address);
                $stmt->execute();
                $customer_id = $conn->insert_id;
                $stmt->close();
            }
            $check_stmt->close();

            // Calculate totals and product names
            $total_price = 0;
            $order_quantity = 0;
            $product_names = [];
            foreach ($_SESSION['punchin_cart'] as $item) {
                $total_price += $item['price'] * $item['quantity'];
                $order_quantity += $item['quantity'];
                $product_names[] = $item['product_name'];
            }
            $product_names_list = implode(", ", $product_names);

            // Insert PurchaseOrder
            $created_date = date('Y-m-d H:i:s');
            $status = 'Pending';
            $payment_status = 'Pending';

            $order_sql = "INSERT INTO PurchaseOrder (OrderQuantity, ProductName, CustomerID, CustomerAddress, TotalPrice, Status, CreatedDate, PaymentStatus) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($order_sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement for PurchaseOrder insert: " . $conn->error);
            }
            $stmt->bind_param("isisdsss", $order_quantity, $product_names_list, $customer_id, $customer_address, $total_price, $status, $created_date, $payment_status);
            $stmt->execute();
            $purchase_order_id = $conn->insert_id;
            $stmt->close();

            // Insert PurchaseOrderLine for each item
            foreach ($_SESSION['punchin_cart'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $unit_price = $item['price'];
                $total_item_price = $unit_price * $quantity;
                $supplier_id = (int)$item['supplier_id'];
                $product_name = $item['product_name'];

                // Validate SupplierID
                $check_stmt = $conn->prepare("SELECT SupplierID FROM supplier WHERE SupplierID = ?");
                $check_stmt->bind_param("i", $supplier_id);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows == 0) {
                    throw new Exception("SupplierID $supplier_id does not exist.");
                }
                $check_stmt->close();

                // Validate ProductID
                $product_stmt = $conn->prepare("SELECT ProductName FROM Products WHERE ProductID = ?");
                $product_stmt->bind_param("i", $product_id);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                if ($product_result->num_rows == 0) {
                    throw new Exception("ProductID $product_id does not exist.");
                }
                $product_stmt->close();

                // Insert into PurchaseOrderLine
                $line_sql = "INSERT INTO PurchaseOrderLine (PurchaseOrderID, ProductName, SupplierID, ProductID, Quantity, UnitPrice, TotalPrice) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($line_sql);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement for PurchaseOrderLine insert: " . $conn->error);
                }
                $stmt->bind_param("isiiidd", $purchase_order_id, $product_name, $supplier_id, $product_id, $quantity, $unit_price, $total_item_price);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();

            // Store CustomerID in session and clear the cart
            $_SESSION['customer_id'] = $customer_id;
            unset($_SESSION['punchin_cart']);
            $success = "Products punched in successfully for $customer_name.";
            header('Location: employee_transactions.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to punch in products: " . $e->getMessage();
            error_log("Checkout error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch-In Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Punch-In Checkout</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="customer_address" class="form-label">Customer Address</label>
                <input type="text" class="form-control" id="customer_address" name="customer_address" required maxlength="100">
            </div>
            <button type="submit" name="confirm_punchin" class="btn btn-success w-100">Confirm Punch-In</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>