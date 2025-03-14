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
        .btn-add {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-add:hover {
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
    <?php include 'includes/header_index.php'; ?>
    <header class="header-bg text-white text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Four A's Marketing Store</h1>
            <p class="lead">Your one-stop shop for construction and hardware supplies.</p>
        </div>
    </header>

    <div class="container my-5">
        <h2 class="text-center mb-5">Our Products</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <img src="assets/img/<?php echo $row['ProductName']; ?>.jpg" class="card-img-top" alt="<?php echo $row['ProductName']; ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo $row['ProductName']; ?></h5>
                            <p class="card-text flex-grow-1"><?php echo $row['ProductCategory']; ?></p>
                            <p class="text-success fw-bold mb-3">₱<?php echo number_format($row['Price'], 2); ?></p>
                            <form method="POST" action="cart.php" class="form-group">
                                <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $row['ProductName']; ?>">
                                <input type="hidden" name="price" value="<?php echo $row['Price']; ?>">
                                <input type="hidden" name="supplierid" value="<?php echo $row['SupplierID']; ?>">
                                <div class="input-group mb-2">
                                    <input type="number" name="quantity" value="1" min="1" class="form-control">
                                    <button type="submit" name="add_to_cart" class="btn btn-add">Add to Cart</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='no-products text-center'>No products available at this time.</p>";
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>