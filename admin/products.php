<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Fetch suppliers from the database
$supplierQuery = "SELECT SupplierID, SupplierName FROM Supplier";
$supplierResult = $conn->query($supplierQuery);

// Add Product Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unitType = isset($_POST['unitType']) ? $_POST['unitType'] : '';
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];
    $supplierID = $_POST['supplier'];

    if (empty($unitType)) {
        echo "Unit type is required.";
    } else {
        $sql = "CALL InsertProduct('$name', '$category', '$unitType', '$unit', $unitCost, $price, $supplierID)";
        $conn->query($sql);
    }
}

// Edit Product Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unitType = $_POST['unitType'];
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];
    $supplierID = $_POST['supplier']; // Get the supplier ID from the form

    $sql = "UPDATE Products SET ProductName=?, ProductCategory=?, unitType=?, UnitofMeasurement=?, UnitCost=?, Price=?, SupplierID=? WHERE ProductID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddii", $name, $category, $unitType, $unit, $unitCost, $price, $supplierID, $product_id);
    
    if ($stmt->execute()) {
        header('Location: products.php'); // Refresh page after update
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
    $stmt->close();
}


if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Prepare and execute the DELETE query
    $stmt = $conn->prepare("DELETE FROM Products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Redirect to refresh the page after successful deletion
        header('Location: products.php');
        exit();
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
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

        <!-- Add Product Button -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="name" placeholder="Product Name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="Household Materials">Household Materials</option>
                                    <option value="Construction Supplies">Construction Supplies</option>
                                    <option value="Equipment">Equipment</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="unitType" class="form-label">Unit Type</label>
                                <select class="form-select" id="unitType" name="unitType" required onchange="toggleUnitInput()">
                                    <option value="" disabled selected>Select Measurement Type</option>
                                    <option value="Quantity">Quantity</option>
                                    <option value="Weight">Weight</option>
                                </select>
                            </div>
                            <div class="mb-3" id="unitInputContainer" style="display:none;">
                                <label for="unit" class="form-label">Unit (e.g., pcs, kg)</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="Unit of Measurement (e.g., pcs, kg)" required>
                            </div>
                            <div class="mb-3">
                                <label for="unitCost" class="form-label">Unit Cost</label>
                                <input type="number" class="form-control" id="unitCost" name="unitCost" placeholder="Unit Cost" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" placeholder="Price" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="supplier" class="form-label">Supplier</label>
                                <select class="form-select" id="supplier" name="supplier" required>
                                    <option value="" disabled selected>Select Supplier</option>
                                    <?php 
                                    $supplierResult->data_seek(0); // Reset pointer
                                    while ($supplier = $supplierResult->fetch_assoc()): ?>
                                        <option value="<?php echo $supplier['SupplierID']; ?>">
                                            <?php echo $supplier['SupplierName']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

      <!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" id="editProductId" name="product_id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <select class="form-select" id="editCategory" name="category" required>
                            <option value="Household Materials">Household Materials</option>
                            <option value="Construction Supplies">Construction Supplies</option>
                            <option value="Equipment">Equipment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editUnitType" class="form-label">Unit Type</label>
                        <select class="form-select" id="editUnitType" name="unitType" required onchange="toggleEditUnitInput()">
                            <option value="Quantity">Quantity</option>
                            <option value="Weight">Weight</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editUnitInputContainer">
                        <label for="editUnit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="editUnit" name="unit" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUnitCost" class="form-label">Unit Cost</label>
                        <input type="number" class="form-control" id="editUnitCost" name="unitCost" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="editPrice" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSupplier" class="form-label">Supplier</label>
                        <select class="form-select" id="editSupplier" name="supplier" required>
                            <option value="" disabled selected>Select Supplier</option>
                            <?php 
                            $supplierResult->data_seek(0); // Reset pointer
                            while ($supplier = $supplierResult->fetch_assoc()): ?>
                                <option value="<?php echo $supplier['SupplierID']; ?>">
                                    <?php echo $supplier['SupplierName']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="edit_product">Update Product</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

      <!-- Product List Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Unit Type</th>
            <th>Unit</th>
            <th>Unit Cost</th>
            <th>Price</th>
            <th>Supplier</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT p.*, s.SupplierName 
                FROM Products p 
                LEFT JOIN Supplier s ON p.SupplierID = s.SupplierID";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo $row['ProductID']; ?></td>
                <td><?php echo $row['ProductName']; ?></td>
                <td><?php echo $row['ProductCategory']; ?></td>
                <td><?php echo $row['unitType']; ?></td>
                <td><?php echo $row['UnitofMeasurement']; ?></td>
                <td>₱<?php echo number_format($row['UnitCost'], 2); ?></td>
                <td>₱<?php echo number_format($row['Price'], 2); ?></td>
                <td><?php echo $row['SupplierName']; ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                            onclick="populateEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                    <a href="?delete=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleUnitInput() {
        const unitType = document.getElementById('unitType').value;
        const unitInputContainer = document.getElementById('unitInputContainer');
        if (unitType === 'Quantity' || unitType === 'Weight') {
            unitInputContainer.style.display = 'block';
        } else {
            unitInputContainer.style.display = 'none';
        }
    }

    function toggleEditUnitInput() {
        const unitType = document.getElementById('editUnitType').value;
        const unitInputContainer = document.getElementById('editUnitInputContainer');
        if (unitType === 'Quantity' || unitType === 'Weight') {
            unitInputContainer.style.display = 'block';
        } else {
            unitInputContainer.style.display = 'none';
        }
    }

    function populateEditModal(product) {
    document.getElementById('editProductId').value = product.ProductID;
    document.getElementById('editName').value = product.ProductName;
    document.getElementById('editCategory').value = product.ProductCategory;
    document.getElementById('editUnitType').value = product.unitType;
    document.getElementById('editUnit').value = product.UnitofMeasurement;
    document.getElementById('editUnitCost').value = product.UnitCost;
    document.getElementById('editPrice').value = product.Price;
    document.getElementById('editSupplier').value = product.SupplierID; // Set the selected supplier
    toggleEditUnitInput(); // Ensure unit input visibility is correct
}
    </script>
</body>
</html>