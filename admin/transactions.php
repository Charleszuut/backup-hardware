<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Admin Transactions</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT po.PurchaseOrderID, c.CustomerName, p.ProductName, po.OrderQuantity, po.TotalPrice, po.Status
                        FROM PurchaseOrder po
                        JOIN Customer c ON po.CustomerID = c.CustomerID
                        JOIN Products p ON po.ProductID = p.ProductID";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['PurchaseOrderID']; ?></td>
                        <td><?php echo $row['CustomerName']; ?></td>
                        <td><?php echo $row['ProductName']; ?></td>
                        <td><?php echo $row['OrderQuantity']; ?></td>
                        <td>₱<?php echo number_format($row['TotalPrice'], 2); ?></td>
                        <td><?php echo $row['Status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>