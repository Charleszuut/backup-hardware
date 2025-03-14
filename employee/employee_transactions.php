<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Get the current date
$current_date = date('Y-m-d');

// Fetch all transactions for the current day
$sql = "SELECT po.PurchaseOrderID, po.CustomerID, c.CustomerName, po.OrderQuantity, po.ProductName, po.TotalPrice, po.CreatedDate 
        FROM PurchaseOrder po 
        JOIN Customer c ON po.CustomerID = c.CustomerID 
        WHERE DATE(po.CreatedDate) = ?
        ORDER BY po.CreatedDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
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
        <h2 class="text-center mb-4">Recent Transactions (Today: <?php echo $current_date; ?>)</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Order Quantity</th>
                    <th>Products</th>
                    <th>Total Price</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['PurchaseOrderID']; ?></td>
                        <td><?php echo $row['CustomerID']; ?></td>
                        <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                        <td><?php echo $row['OrderQuantity']; ?></td>
                        <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                        <td>₱<?php echo number_format($row['TotalPrice'], 2); ?></td>
                        <td><?php echo $row['CreatedDate']; ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">No transactions found for today.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>