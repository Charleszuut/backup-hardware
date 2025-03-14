<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Fetch product addition history from audit_logs (Supplier-related)
$product_add_sql = "
    SELECT 
        al.action_timestamp AS event_date,
        JSON_EXTRACT(al.new_data, '$.ProductName') AS ProductName,
        JSON_EXTRACT(al.new_data, '$.Price') AS Price,
        s.SupplierName
    FROM audit_logs al
    LEFT JOIN supplier s ON JSON_EXTRACT(al.new_data, '$.SupplierID') = s.SupplierID
    WHERE al.table_name = 'Products' AND al.action_type = 'INSERT'
    ORDER BY al.action_timestamp DESC
";
$add_result = $conn->query($product_add_sql);
if (!$add_result) {
    die("Product add query failed: " . $conn->error);
}

// Fetch purchase history from PurchaseOrder and PurchaseOrderLine (Customer-related)
$purchase_sql = "
    SELECT 
        po.CreatedDate AS event_date,
        pol.ProductName,
        pol.Quantity,
        pol.TotalPrice,
        c.CustomerName,
        s.SupplierName
    FROM PurchaseOrder po
    JOIN PurchaseOrderLine pol ON po.PurchaseOrderID = pol.PurchaseOrderID
    JOIN Customer c ON po.CustomerID = c.CustomerID
    LEFT JOIN Supplier s ON pol.SupplierID = s.SupplierID
    ORDER BY po.CreatedDate DESC
";
$purchase_result = $conn->query($purchase_sql);
if (!$purchase_result) {
    die("Purchase query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .table th, .table td { vertical-align: middle; }
        .section-title { margin-top: 40px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <!-- Products Added from Supplier -->
        <h3 class="section-title text-center">Products Bought from the Suppliers</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date Added</th>
                    <th>Product</th>
                    <th>Price (₱)</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $add_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($row['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['ProductName'] ?? 'N/A'); ?></td>
                        <td><?php echo '₱' . number_format($row['Price'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Products Purchased by Customers -->
        <h3 class="section-title text-center">Products Purchased by Customers</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date Purchased</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total (₱)</th>
                    <th>Customer</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $purchase_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($row['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['ProductName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Quantity'] ?? 'N/A'); ?></td>
                        <td><?php echo '₱' . number_format($row['TotalPrice'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($row['CustomerName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>