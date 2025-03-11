<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Fetch suppliers from the database
$supplierQuery = "SELECT SupplierID, SupplierName FROM Supplier";
$supplierResult = $conn->query($supplierQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $unitType = isset($_POST['unitType']) ? $_POST['unitType'] : '';
    $unit = $_POST['unit'];
    $unitCost = $_POST['unitCost'];
    $price = $_POST['price'];
    $supplierID = $_POST['supplier']; // Get the selected supplier ID

    if (empty($unitType)) {
        echo "Unit type is required.";
    } else {
        // Update the SQL query to include SupplierID
        $sql = "CALL InsertProduct('$name', '$category', '$unitType', '$unit', $unitCost, $price, $supplierID)";
        $conn->query($sql);
    }
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

        <!-- Button to trigger modal -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

        <!-- Modal -->
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
                                <label for="unitType" class="form-label">Unit of Measurement Type</label>
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
                                    <?php while ($supplier = $supplierResult->fetch_assoc()): ?>
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
                // Fetch products with supplier information
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
                            <a href="edit_product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
        const unitInput = document.getElementById('unit');

        if (unitType === 'Quantity') {
            unitInputContainer.style.display = 'block';
            unitInput.type = 'number';  // Quantity should only accept integers
            unitInput.placeholder = 'Enter quantity (e.g., 10)';
        } else if (unitType === 'Weight') {
            unitInputContainer.style.display = 'block';
            unitInput.type = 'number';  // Weight should accept decimals
            unitInput.placeholder = 'Enter weight (e.g., 10.5)';
        } else {
            unitInputContainer.style.display = 'none';
        }
    }

    // Validate on form submission
    document.querySelector('form').addEventListener('submit', function(event) {
        const unitType = document.getElementById('unitType').value;
        const unitInput = document.getElementById('unit').value;

        if (unitType === 'Quantity') {
            // Ensure only integers are entered
            if (!Number.isInteger(parseFloat(unitInput)) || parseFloat(unitInput) <= 0) {
                alert("Please enter a valid quantity (integer).");
                event.preventDefault();  // Prevent form submission
            }
        } else if (unitType === 'Weight') {
            // Ensure a valid decimal number is entered and append "kg"
            if (isNaN(unitInput) || parseFloat(unitInput) <= 0) {
                alert("Please enter a valid weight (decimal number).");
                event.preventDefault();  // Prevent form submission
            } else {
                // Append "kg" to the unit for weight
                document.getElementById('unit').value = unitInput + " kg";
            }
        }
    });
</script>
</body>
</html>