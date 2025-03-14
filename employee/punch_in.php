<?php
// Enable error reporting and display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include authentication and database connection
include '../includes/auth.php';
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Initialize variables
$success = '';
$error = '';

// Measure query execution time for debugging
$start_time = microtime(true);

// Fetch available products with error handling and limit
$sql = "SELECT ProductID, ProductName, ProductCategory, Price, SupplierID FROM Products ORDER BY ProductName LIMIT 50";
$result = $conn->query($sql);

if (!$result) {
    $error = "Query failed: " . $conn->error;
    error_log("Database query error: " . $conn->error);
} else {
    error_log("Query executed in " . (microtime(true) - $start_time) . " seconds. Rows fetched: " . $result->num_rows);
}

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['punch_in'])) {
    $start_time = microtime(true); // Measure processing time
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $product_name = htmlspecialchars($_POST['product_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0.00);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);

    if ($quantity <= 0) {
        $error = "Quantity must be greater than 0.";
    } elseif (empty($product_name) || $price <= 0) {
        $error = "Invalid product data.";
    } else {
        if (!isset($_SESSION['punchin_cart'])) {
            $_SESSION['punchin_cart'] = [];
        }

        $item_exists = false;
        foreach ($_SESSION['punchin_cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $item_exists = true;
                break;
            }
        }

        if (!$item_exists) {
            $_SESSION['punchin_cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'price' => $price,
                'quantity' => $quantity,
                'supplier_id' => $supplier_id
            ];
        }

        error_log("Cart updated in " . (microtime(true) - $start_time) . " seconds. Cart: " . print_r($_SESSION['punchin_cart'], true));
        header('Location: punchin_cart.php');
        exit();
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

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 g-4" id="product-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <img src="../assets/img/<?php echo htmlspecialchars($row['ProductName']); ?>.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($row['ProductName']); ?>" 
                             onerror="this.onerror=null; this.src='../assets/img/default.jpg';">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['ProductName']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['ProductCategory']); ?></p>
                            <p class="text-success fw-bold mb-3">₱<?php echo number_format($row['Price'], 2); ?></p>
                            <form method="POST" class="form-group">
                                <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['ProductName']); ?>">
                                <input type="hidden" name="price" value="<?php echo $row['Price']; ?>">
                                <input type="hidden" name="supplier_id" value="<?php echo $row['SupplierID']; ?>">
                                <div class="input-group mb-2">
                                    <input type="number" name="quantity" value="1" min="1" class="form-control">
                                    <button type="submit" name="punch_in" class="btn btn-add">Punch In</button>
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
            <a href="punchin_cart.php" class="btn btn-primary">View Cart</a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>