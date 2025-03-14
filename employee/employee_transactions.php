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

// Handle sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'CreatedDate';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
$valid_sort_columns = ['PurchaseOrderID', 'CustomerName', 'TotalPrice', 'CreatedDate'];

if (!in_array($sort, $valid_sort_columns)) {
    $sort = 'CreatedDate';
}

$next_order = $order === 'ASC' ? 'desc' : 'asc';

// Check for success message
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Fetch transactions for today only
$sql = "SELECT po.PurchaseOrderID, po.CustomerID, c.CustomerName, po.OrderQuantity, po.ProductName, po.TotalPrice, po.CreatedDate, po.Status 
        FROM PurchaseOrder po 
        JOIN Customer c ON po.CustomerID = c.CustomerID 
        WHERE DATE(po.CreatedDate) = ?
        ORDER BY $sort $order";
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
    <title>Employee Transactions - Today</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Recent Transactions (Today: <?php echo $current_date; ?>)</h2>

        <div class="text-center mb-4">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>
                        <a href="?sort=PurchaseOrderID&order=<?php echo $sort === 'PurchaseOrderID' ? $next_order : 'asc'; ?>" class="text-white text-decoration-none">
                            Order ID <?php echo $sort === 'PurchaseOrderID' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>Customer ID</th>
                    <th>
                        <a href="?sort=CustomerName&order=<?php echo $sort === 'CustomerName' ? $next_order : 'asc'; ?>" class="text-white text-decoration-none">
                            Customer Name <?php echo $sort === 'CustomerName' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>Order Quantity</th>
                    <th>Products</th>
                    <th>
                        <a href="?sort=TotalPrice&order=<?php echo $sort === 'TotalPrice' ? $next_order : 'asc'; ?>" class="text-white text-decoration-none">
                            Total Price <?php echo $sort === 'TotalPrice' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=CreatedDate&order=<?php echo $sort === 'CreatedDate' ? $next_order : 'asc'; ?>" class="text-white text-decoration-none">
                            Created Date <?php echo $sort === 'CreatedDate' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                        </a>
                    </th>
                    <th>Status</th>
                    <th>Actions</th>
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
                        <td><?php echo $row['Status']; ?></td>
                        <td>
                            <a href="update_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_order.php?id=<?php echo $row['PurchaseOrderID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="9" class="text-center">No transactions found for today.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>