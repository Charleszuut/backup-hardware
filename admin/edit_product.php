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

// Fetch product details securely using a prepared statement
$stmt = $conn->prepare("SELECT * FROM Products WHERE ProductID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unitType = $_POST['unitType'];
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];

    // Update product using prepared statement
    $sql = "UPDATE Products SET ProductName=?, ProductCategory=?, unitType=?, UnitofMeasurement=?, UnitCost=?, Price=? WHERE ProductID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddi", $name, $category, $unitType, $unit, $unitCost, $price, $product_id);
    
    if ($stmt->execute()) {
        header('Location: products.php');
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit Product</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['ProductName']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="Household Materials" <?php echo ($product['ProductCategory'] == 'Household Materials') ? 'selected' : ''; ?>>Household Materials</option>
                    <option value="Construction Supplies" <?php echo ($product['ProductCategory'] == 'Construction Supplies') ? 'selected' : ''; ?>>Construction Supplies</option>
                    <option value="Equipment" <?php echo ($product['ProductCategory'] == 'Equipment') ? 'selected' : ''; ?>>Equipment</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="unitType" class="form-label">Unit Type</label>
                <select class="form-select" id="unitType" name="unitType" required>
                    <option value="Quantity" <?php echo ($product['unitType'] == 'Quantity') ? 'selected' : ''; ?>>Quantity</option>
                    <option value="Weight" <?php echo ($product['unitType'] == 'Weight') ? 'selected' : ''; ?>>Weight</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="unit" class="form-label">Unit</label>
                <input type="text" class="form-control" id="unit" name="unit" value="<?php echo htmlspecialchars($product['UnitofMeasurement']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="unitCost" class="form-label">Unit Cost</label>
                <input type="number" class="form-control" id="unitCost" name="unitCost" value="<?php echo htmlspecialchars($product['UnitCost']); ?>" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['Price']); ?>" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
