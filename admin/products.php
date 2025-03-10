<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];

    $sql = "CALL InsertProduct('$name', '$category', '$unit', $unitCost, $price)";
    $conn->query($sql);
}


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Products WHERE ProductID=$id";
    $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Products</h2>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="name" placeholder="Product Name" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="category" placeholder="Category" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="unit" placeholder="Unit of Measurement" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="unitCost" placeholder="Unit Cost" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="price" placeholder="Price" step="0.01" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_product" class="btn btn-success w-100">Add</button>
                </div>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM Products";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['ProductID']; ?></td>
                        <td><?php echo $row['ProductName']; ?></td>
                        <td><?php echo $row['ProductCategory']; ?></td>
                        <td><?php echo $row['UnitofMeasurement']; ?></td>
                        <td>₱<?php echo number_format($row['UnitCost'], 2); ?></td>
                        <td>₱<?php echo number_format($row['Price'], 2); ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>