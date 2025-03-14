<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle sorting for Products Bought from Suppliers
$sort_add = isset($_GET['sort_add']) ? $_GET['sort_add'] : 'event_date';
$order_add = isset($_GET['order_add']) && $_GET['order_add'] === 'asc' ? 'ASC' : 'DESC';
$valid_add_columns = ['event_date', 'ProductName', 'Price', 'SupplierName'];
if (!in_array($sort_add, $valid_add_columns)) {
    $sort_add = 'event_date';
}
$next_order_add = $order_add === 'ASC' ? 'desc' : 'asc';

// Handle sorting for Products Purchased by Customers
$sort_purchase = isset($_GET['sort_purchase']) ? $_GET['sort_purchase'] : 'event_date';
$order_purchase = isset($_GET['order_purchase']) && $_GET['order_purchase'] === 'asc' ? 'ASC' : 'DESC';
$valid_purchase_columns = ['event_date', 'ProductName', 'Quantity', 'TotalPrice', 'CustomerName', 'SupplierName'];
if (!in_array($sort_purchase, $valid_purchase_columns)) {
    $sort_purchase = 'event_date';
}
$next_order_purchase = $order_purchase === 'ASC' ? 'desc' : 'asc';

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
    ORDER BY $sort_add $order_add
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
    ORDER BY $sort_purchase $order_purchase
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
                    <th>
                        <a href="?sort_add=event_date&order_add=<?php echo $sort_add === 'event_date' ? $next_order_add : 'asc'; ?>" class="text-white text-decoration-none">
                            Date Added <?php echo $sort_add === 'event_date' ? ($order_add === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_add=ProductName&order_add=<?php echo $sort_add === 'ProductName' ? $next_order_add : 'asc'; ?>" class="text-white text-decoration-none">
                            Product <?php echo $sort_add === 'ProductName' ? ($order_add === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_add=Price&order_add=<?php echo $sort_add === 'Price' ? $next_order_add : 'asc'; ?>" class="text-white text-decoration-none">
                            Price (₱) <?php echo $sort_add === 'Price' ? ($order_add === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_add=SupplierName&order_add=<?php echo $sort_add === 'SupplierName' ? $next_order_add : 'asc'; ?>" class="text-white text-decoration-none">
                            Supplier <?php echo $sort_add === 'SupplierName' ? ($order_add === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
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
                    <th>
                        <a href="?sort_purchase=event_date&order_purchase=<?php echo $sort_purchase === 'event_date' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Date Purchased <?php echo $sort_purchase === 'event_date' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_purchase=ProductName&order_purchase=<?php echo $sort_purchase === 'ProductName' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Product <?php echo $sort_purchase === 'ProductName' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_purchase=Quantity&order_purchase=<?php echo $sort_purchase === 'Quantity' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Quantity <?php echo $sort_purchase === 'Quantity' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_purchase=TotalPrice&order_purchase=<?php echo $sort_purchase === 'TotalPrice' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Total (₱) <?php echo $sort_purchase === 'TotalPrice' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_purchase=CustomerName&order_purchase=<?php echo $sort_purchase === 'CustomerName' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Customer <?php echo $sort_purchase === 'CustomerName' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_purchase=SupplierName&order_purchase=<?php echo $sort_purchase === 'SupplierName' ? $next_order_purchase : 'asc'; ?>" class="text-white text-decoration-none">
                            Supplier <?php echo $sort_purchase === 'SupplierName' ? ($order_purchase === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
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