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
    <title>Employee Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee Transactions</h2>
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
                // Fetch all purchase orders with customer and product details
                $sql = "SELECT po.PurchaseOrderID, c.CustomerName, pol.ProductName, pol.Quantity, pol.TotalPrice, po.Status
                        FROM PurchaseOrder po
                        JOIN Customer c ON po.CustomerID = c.CustomerID
                        JOIN PurchaseOrderLine pol ON po.PurchaseOrderID = pol.PurchaseOrderID";
                $result = $conn->query($sql);

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
                            <td>
                                <a href="employee/update_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-warning btn-sm">Update</a>
                                <a href="employee/delete_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                            </td>
                        </tr>
                <?php
                    endwhile;
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No transactions found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>