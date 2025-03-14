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

// Fetch available products
$sql = "SELECT ProductID, ProductName, ProductCategory, Price, SupplierID FROM Products ORDER BY ProductName";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle punch-in submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['punch_in'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $customer_name = "Walk-In Customer";
    $customer_address = "N/A";

    // Validate inputs
    if ($quantity <= 0) {
        $error = "Quantity must be greater than 0.";
    } else {
        // Get product details
        $product_sql = "SELECT ProductName, Price FROM Products WHERE ProductID = ?";
        $stmt = $conn->prepare($product_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($product) {
            $total_price = $product['Price'] * $quantity;

            // Start transaction
            $conn->begin_transaction();
            try {
                // Insert Customer (generic walk-in)
                $customer_sql = "INSERT INTO Customer (CustomerName, CustomerAddress) VALUES (?, ?)";
                $stmt = $conn->prepare($customer_sql);
                $stmt->bind_param("ss", $customer_name, $customer_address);
                $stmt->execute();
                $customer_id = $conn->insert_id;
                $stmt->close();

                // Insert PurchaseOrder
                $order_sql = "INSERT INTO PurchaseOrder (OrderQuantity, ProductName, CustomerID, CustomerAddress, TotalPrice) 
                              VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($order_sql);
                $stmt->bind_param("isssd", $quantity, $product['ProductName'], $customer_id, $customer_address, $total_price);
                $stmt->execute();
                $purchase_order_id = $conn->insert_id;
                $stmt->close();

                // Insert PurchaseOrderLine
                $line_sql = "INSERT INTO PurchaseOrderLine (PurchaseOrderID, ProductName, ProductID, Quantity, UnitPrice, TotalPrice) 
                             VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($line_sql);
                $stmt->bind_param("isiiid", $purchase_order_id, $product['ProductName'], $product_id, $quantity, $product['Price'], $total_price);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $success = "Product punched in successfully for walk-in customer.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Failed to punch in product: " . $e->getMessage();
            }
        } else {
            $error = "Invalid product selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch In Products - Four A's Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .header-bg {
            background-color: #007bff;
            color: #fff;
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-punch {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-punch:hover {
            background-color: #218838;
            color: #fff;
        }
        .no-products {
            font-size: 1.2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <header class="header-bg text-white text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Punch In Products</h1>
            <p class="lead">Record products for walk-in customers quickly and easily.</p>
        </div>
    </header>

    <div class="container my-5">
        <h2 class="text-center mb-5">Available Products</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <img src="../assets/img/<?php echo $row['ProductName']; ?>.jpg" class="card-img-top" alt="<?php echo $row['ProductName']; ?>" 
                             onerror="this.src='../assets/img/default.jpg';">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['ProductName']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['ProductCategory']); ?></p>
                            <p class="text-success fw-bold mb-3">₱<?php echo number_format($row['Price'], 2); ?></p>
                            <form method="POST" class="form-group">
                                <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                <div class="input-group mb-2">
                                    <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                                    <button type="submit" name="punch_in" class="btn btn-punch">Punch In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='no-products text-center'>No products available to punch in.</p>";
            }
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>