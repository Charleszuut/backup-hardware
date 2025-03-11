<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Fetch all employees
function getEmployees($conn) {
    $employees = [];
    $result = $conn->query("SELECT * FROM employee");
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    return $employees;
}

// Fetch all suppliers
function getSuppliers($conn) {
    $suppliers = [];
    $result = $conn->query("SELECT * FROM supplier");
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
    return $suppliers;
}

$success = $error = "";

// Add Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_employee'])) {
    $empFName = $_POST['empFName'];
    $empLName = $_POST['empLName'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $isActive = $_POST['isActive'];
    $empPos = 'Employee';
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Add Employee
    $stmt = $conn->prepare("CALL InsertEmployee(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisi", $empFName, $empLName, $empPos, $username, $hashedPassword, $role, $phone, $isActive, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success = "Employee added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Update Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_employee'])) {
    $empID = $_POST['empID'];
    $empFName = $_POST['empFName'];
    $empLName = $_POST['empLName'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $isActive = $_POST['isActive'];

    // Update Employee
    $stmt = $conn->prepare("UPDATE employee SET EmpFName=?, EmpLName=?, Username=?, Role=?, Phone=?, IsActive=? WHERE EmpID=?");
    $stmt->bind_param("sssssii", $empFName, $empLName, $username, $role, $phone, $isActive, $empID);
    
    if ($stmt->execute()) {
        $success = "Employee updated successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Delete Employee
if (isset($_POST['delete_employee'])) {
    $empID = $_POST['empID'];
    $stmt = $conn->prepare("DELETE FROM employee WHERE EmpID=?");
    $stmt->bind_param("i", $empID);
    $stmt->execute();
    $stmt->close();
    $success = "Employee deleted successfully!";
}

// Add or Update Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_supplier'])) {
    $supplierID = $_POST['supplierID'] ?? null;
    $supplierName = $_POST['supplierName'];
    $supplierAddress = $_POST['supplierAddress'];
    $supplierNo = $_POST['supplierNo'];

    if ($supplierID) {
        // Update Supplier
        $stmt = $conn->prepare("UPDATE supplier SET SupplierName=?, SupplierAddress=?, SupplierNo=? WHERE SupplierID=?");
        $stmt->bind_param("sssi", $supplierName, $supplierAddress, $supplierNo, $supplierID);
    } else {
        // Add Supplier
        $stmt = $conn->prepare("INSERT INTO supplier (SupplierName, SupplierAddress, SupplierNo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $supplierName, $supplierAddress, $supplierNo);
    }

    if ($stmt->execute()) {
        $success = "Supplier saved successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Delete Supplier
if (isset($_POST['delete_supplier'])) {
    $supplierID = $_POST['supplierID'];
    $stmt = $conn->prepare("DELETE FROM supplier WHERE SupplierID=?");
    $stmt->bind_param("i", $supplierID);
    $stmt->execute();
    $stmt->close();
    $success = "Supplier deleted successfully!";
}

$employees = getEmployees($conn);
$suppliers = getSuppliers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees & Suppliers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Employees & Suppliers</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

       <!-- Employee List -->
<h3 class="text-center mt-5">Employees</h3>
<button class="btn btn-primary mb-3" onclick="openAddEmployeeModal()">Add Employee</button>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Role</th> <!-- Corrected: Role column -->
            <th>Phone</th> <!-- Corrected: Phone column -->
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <td><?= $employee['EmpID'] ?></td>
                <td><?= $employee['EmpFName'] . ' ' . $employee['EmpLName'] ?></td>
                <td><?= $employee['Username'] ?></td>
                <td><?= $employee['Role'] ?></td> <!-- Corrected: Role data -->
                <td><?= $employee['Phone'] ?></td> <!-- Corrected: Phone data -->
                <td><?= $employee['IsActive'] ? 'Active' : 'Inactive' ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editEmployee(<?= htmlspecialchars(json_encode($employee)) ?>)">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="empID" value="<?= $employee['EmpID'] ?>">
                        <button type="submit" name="delete_employee" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Supplier List -->
<h3 class="text-center mt-5">Suppliers</h3>
<button class="btn btn-success mb-3" onclick="openAddSupplierModal()">Add Supplier</button>
<table class="table table-bordered">
    <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($suppliers as $supplier): ?>
            <tr>
                <td><?= $supplier['SupplierID'] ?></td>
                <td><?= $supplier['SupplierName'] ?></td>
                <td><?= $supplier['SupplierAddress'] ?></td>
                <td><?= $supplier['SupplierNo'] ?></td>
                <td><button class="btn btn-warning btn-sm" onclick="editSupplier(<?= htmlspecialchars(json_encode($supplier)) ?>)">Edit</button>
                    <form method="POST" style="display:inline;">
                        
                        <input type="hidden" name="supplierID" value="<?= $supplier['SupplierID'] ?>">
                        
                        <button type="submit" name="delete_supplier" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addEmployeeForm">
                        <div class="mb-3">
                            <label for="empFName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="empFName" name="empFName" required>
                        </div>
                        <div class="mb-3">
                            <label for="empLName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="empLName" name="empLName" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" name="role" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="isActive" class="form-label">Status</label>
                            <select class="form-select" id="isActive" name="isActive" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editEmployeeForm">
                        <input type="hidden" name="empID" id="editEmpID">
                        <div class="mb-3">
                            <label for="editEmpFName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editEmpFName" name="empFName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmpLName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editEmpLName" name="empLName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <input type="text" class="form-control" id="editRole" name="role" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="editIsActive" class="form-label">Status</label>
                            <select class="form-select" id="editIsActive" name="isActive" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" name="edit_employee" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="addSupplierForm">
                    <div class="mb-3">
                        <label for="supplierName" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="supplierName" name="supplierName" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplierAddress" class="form-label">Supplier Address</label>
                        <input type="text" class="form-control" id="supplierAddress" name="supplierAddress" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplierNo" class="form-label">Supplier Phone</label>
                        <input type="text" class="form-control" id="supplierNo" name="supplierNo" required>
                    </div>
                    <button type="submit" name="save_supplier" class="btn btn-primary">Add Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editSupplierForm">
                    <input type="hidden" name="supplierID" id="editSupplierID">
                    <div class="mb-3">
                        <label for="editSupplierName" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="editSupplierName" name="supplierName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSupplierAddress" class="form-label">Supplier Address</label>
                        <input type="text" class="form-control" id="editSupplierAddress" name="supplierAddress" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSupplierNo" class="form-label">Supplier Phone</label>
                        <input type="text" class="form-control" id="editSupplierNo" name="supplierNo" required>
                    </div>
                    <button type="submit" name="save_supplier" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to open the Add Employee modal
        function openAddEmployeeModal() {
            const modal = new bootstrap.Modal(document.getElementById('addEmployeeModal'));
            modal.show();
        }

        // Function to open the Edit Employee modal and populate the form
        function editEmployee(employee) {
            document.getElementById('editEmpID').value = employee.EmpID;
            document.getElementById('editEmpFName').value = employee.EmpFName;
            document.getElementById('editEmpLName').value = employee.EmpLName;
            document.getElementById('editUsername').value = employee.Username;
            document.getElementById('editRole').value = employee.Role;
            document.getElementById('editPhone').value = employee.Phone;
            document.getElementById('editIsActive').value = employee.IsActive;

            const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
            modal.show();
        }
        // Function to open the Add Supplier modal
    function openAddSupplierModal() {
        const modal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
        modal.show();
    }

    // Function to open the Edit Supplier modal and populate the form
    function editSupplier(supplier) {
        document.getElementById('editSupplierID').value = supplier.SupplierID;
        document.getElementById('editSupplierName').value = supplier.SupplierName;
        document.getElementById('editSupplierAddress').value = supplier.SupplierAddress;
        document.getElementById('editSupplierNo').value = supplier.SupplierNo;

        const modal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
        modal.show();
    }
    </script>
</body>
</html>