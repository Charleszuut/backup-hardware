<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'includes/db.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch products
$sql = "SELECT * FROM Products";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four A's Marketing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header_index.php'; ?>
    <header class="bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Four A's Marketing Store</h1>
            <p class="lead">Your one-stop shop for construction and hardware supplies.</p>
        </div>
    </header>

    <div class="container my-5">
        <h2 class="text-center mb-4">Our Products</h2>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Uncomment the next line temporarily to debug column names
                    // echo "<pre>" . print_r($row, true) . "</pre>";
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <img src="assets/img/<?php echo $row['ProductName']; ?>.jpg" class="card-img-top" alt="<?php echo $row['ProductName']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['ProductName']; ?></h5>
                            <p class="card-text"><?php echo $row['ProductCategory']; ?></p>
                            <p class="text-success fw-bold">₱<?php echo number_format($row['Price'], 2); ?></p>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $row['ProductName']; ?>">
                                <input type="hidden" name="price" value="<?php echo $row['Price']; ?>">
                                <input type="hidden" name="supplierid" value="<?php echo $row['SupplierID']; ?>">
                                <input type="number" name="quantity" value="1" min="1">
                                <button type="submit" name="add_to_cart">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='text-center'>No products available at this time.</p>";
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>