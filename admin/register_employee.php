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

// Add or Update Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_employee'])) {
    $empID = $_POST['empID'] ?? null;
    $empFName = $_POST['empFName'];
    $empLName = $_POST['empLName'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $empPos = 'Employee';

    if ($empID) {
        // Update Employee
        $stmt = $conn->prepare("UPDATE employee SET EmpFName=?, EmpLName=?, Username=?, Role=?, Phone=? WHERE EmpID=?");
        $stmt->bind_param("sssssi", $empFName, $empLName, $username, $role, $phone, $empID);
    } else {
        // Add Employee
        $stmt = $conn->prepare("CALL InsertEmployee(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $empFName, $empLName, $empPos, $username, $password, $phone);
    }
    
    if ($stmt->execute()) {
        $success = "Employee saved successfully!";
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
        <button class="btn btn-primary mb-3" onclick="openModal('employeeModal')">Add Employee</button>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th>Phone</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?= $employee['EmpID'] ?></td>
                        <td><?= $employee['EmpFName'] . ' ' . $employee['EmpLName'] ?></td>
                        <td><?= $employee['Username'] ?></td>
                        <td><?= $employee['Role'] ?></td>
                        <td><?= $employee['Phone'] ?></td>
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
        <button class="btn btn-success mb-3" onclick="openModal('supplierModal')">Add Supplier</button>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?= $supplier['SupplierID'] ?></td>
                        <td><?= $supplier['SupplierName'] ?></td>
                        <td><?= $supplier['SupplierAddress'] ?></td>
                        <td><?= $supplier['SupplierNo'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editSupplier(<?= htmlspecialchars(json_encode($supplier)) ?>)">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="supplierID" value="<?= $supplier['SupplierID'] ?>">
                                <button type="submit" name="delete_supplier" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
