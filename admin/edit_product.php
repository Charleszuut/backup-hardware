<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$sql = "SELECT * FROM Products WHERE ProductID = $product_id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];

    // Update product
    $sql = "UPDATE Products SET ProductName='$name', ProductCategory='$category', UnitofMeasurement='$unit', UnitCost=$unitCost, Price=$price WHERE ProductID=$product_id";
    if ($conn->query($sql)) {
        header('Location: products.php');
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit Product</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $product['ProductName']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo $product['ProductCategory']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="unit" class="form-label">Unit of Measurement</label>
                <input type="text" class="form-control" id="unit" name="unit" value="<?php echo $product['UnitofMeasurement']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="unitCost" class="form-label">Unit Cost</label>
                <input type="number" class="form-control" id="unitCost" name="unitCost" value="<?php echo $product['UnitCost']; ?>" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['Price']; ?>" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>