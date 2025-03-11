<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Function to fetch all employees
function getEmployees($conn) {
    $employees = [];
    $result = $conn->query("SELECT * FROM employee");
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    return $employees;
}

// Function to fetch all suppliers
function getSuppliers($conn) {
    $suppliers = [];
    $result = $conn->query("SELECT * FROM supplier");
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
    return $suppliers;
}

$success = $error = "";

// Register new employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_employee'])) {
    $empFName = $_POST['empFName'] ?? '';
    $empLName = $_POST['empLName'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $empPos = 'Employee';

    $stmt = $conn->prepare("CALL InsertEmployee(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $empFName, $empLName, $empPos, $username, $password, $phone);
    $success = $stmt->execute() ? "Employee registered successfully!" : "Error: " . $conn->error;
    $stmt->close();
}

// Register new supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_supplier'])) {
    $supplierName = $_POST['supplierName'] ?? '';
    $supplierAddress = $_POST['supplierAddress'] ?? '';
    $supplierNo = $_POST['supplierNo'] ?? '';

    $stmt = $conn->prepare("INSERT IGNORE INTO supplier (SupplierName, SupplierAddress, SupplierNo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $supplierName, $supplierAddress, $supplierNo);
    $success = $stmt->execute() ? "Supplier registered successfully!" : "Error: " . $conn->error;
    $stmt->close();
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

        <!-- Employee Registration -->
        <button class="btn btn-primary w-100 mb-3" onclick="toggleForm('employeeForm')">Register Employee</button>
        <div id="employeeForm" style="display: none;">
            <h3>Register Employee</h3>
            <form method="POST">
                <input type="hidden" name="register_employee" value="1">
                <div class="mb-3"><label>First Name</label><input type="text" class="form-control" name="empFName" required></div>
                <div class="mb-3"><label>Last Name</label><input type="text" class="form-control" name="empLName" required></div>
                <div class="mb-3"><label>Username</label><input type="text" class="form-control" name="username" required></div>
                <div class="mb-3"><label>Password</label><input type="password" class="form-control" name="password" required></div>
                <div class="mb-3"><label>Role</label><select class="form-control" name="role" required><option value="Employee">Employee</option><option value="Manager">Manager</option><option value="Admin">Admin</option></select></div>
                <div class="mb-3"><label>Phone Number</label><input type="text" class="form-control" name="phone" required></div>
                <button type="submit" class="btn btn-primary w-100">Register Employee</button>
            </form>
        </div>

        <!-- Supplier Registration -->
        <button class="btn btn-success w-100 mt-4 mb-3" onclick="toggleForm('supplierForm')">Register Supplier</button>
        <div id="supplierForm" style="display: none;">
            <h3>Register Supplier</h3>
            <form method="POST">
                <input type="hidden" name="register_supplier" value="1">
                <div class="mb-3"><label>Supplier Name</label><input type="text" class="form-control" name="supplierName" required></div>
                <div class="mb-3"><label>Supplier Address</label><input type="text" class="form-control" name="supplierAddress" required></div>
                <div class="mb-3"><label>Supplier Phone</label><input type="text" class="form-control" name="supplierNo" required></div>
                <button type="submit" class="btn btn-success w-100">Register Supplier</button>
            </form>
        </div>

        <!-- Employee List -->
        <h3 class="text-center mt-5">Employee List</h3>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Phone</th></tr></thead>
            <tbody><?php foreach ($employees as $employee): ?><tr><td><?php echo $employee['EmpID']; ?></td><td><?php echo $employee['EmpFName'] . ' ' . $employee['EmpLName']; ?></td><td><?php echo $employee['Username']; ?></td><td><?php echo $employee['Role']; ?></td><td><?php echo $employee['Status']; ?></td><td><?php echo $employee['Phone']; ?></td></tr><?php endforeach; ?></tbody>
        </table>

        <!-- Supplier List -->
        <h3 class="text-center mt-5">Supplier List</h3>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th></tr></thead>
            <tbody><?php foreach ($suppliers as $supplier): ?><tr><td><?php echo $supplier['SupplierID']; ?></td><td><?php echo $supplier['SupplierName']; ?></td><td><?php echo $supplier['SupplierAddress']; ?></td><td><?php echo $supplier['SupplierNo']; ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>