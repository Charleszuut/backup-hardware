<?php
session_start();
include 'includes/auth.php';
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Determine the user's role
$role = isset($_GET['role']) ? $_GET['role'] : $_SESSION['role'];

// Validate role and access
if ($role !== 'admin' && $role !== 'employee' && $role !== 'customer') {
    die("Invalid role.");
}

// For customers, ensure they can only see their own transactions
$customer_id = null;
if ($role === 'customer') {
    if (!isset($_SESSION['customer_id'])) {
        die("Customer ID not found. Please log in again.");
    }
    $customer_id = $_SESSION['customer_id'];
}

// Prepare the SQL query based on role
if ($role === 'admin' || $role === 'employee') {
    // Admins and employees see all transactions
    $sql = "SELECT po.PurchaseOrderID, c.CustomerName, pol.ProductName, pol.Quantity, pol.TotalPrice, po.Status
            FROM PurchaseOrder po
            JOIN Customer c ON po.CustomerID = c.CustomerID
            JOIN PurchaseOrderLine pol ON po.PurchaseOrderID = pol.PurchaseOrderID";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
} else {
    // Customers see only their own transactions
    $sql = "SELECT po.PurchaseOrderID, c.CustomerName, pol.ProductName, pol.Quantity, pol.TotalPrice, po.Status
            FROM PurchaseOrder po
            JOIN Customer c ON po.CustomerID = c.CustomerID
            JOIN PurchaseOrderLine pol ON po.PurchaseOrderID = pol.PurchaseOrderID
            WHERE po.CustomerID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $customer_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($role === 'customer') ? 'My Transactions' : 'All Transactions'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    // Include the appropriate header based on role
    if ($role === 'admin') {
        include '../admin/header_admin.php';
    } elseif ($role === 'employee') {
        include '../employee/header_employee.php';
    } else {
        include '../header_index.php'; // Customer header
    }
    ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><?php echo ($role === 'customer') ? 'My Transactions' : 'All Transactions'; ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Execute the query
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()):
                ?>
                        <tr>
                            <td><?php echo $row['PurchaseOrderID']; ?></td>
                            <td><?php echo $row['CustomerName']; ?></td>
                            <td><?php echo $row['ProductName']; ?></td>
                            <td><?php echo $row['Quantity']; ?></td>
                            <td>₱<?php echo number_format($row['TotalPrice'], 2); ?></td>
                            <td><?php echo $row['Status']; ?></td>
                            <td>
                                <?php if ($role === 'admin' || $role === 'employee'): ?>
                                    <a href="employee/update_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-warning btn-sm">Update</a>
                                    <a href="employee/delete_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                <?php else: ?>
                                    <span class="text-muted">View Only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    endwhile;
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No transactions found.</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>