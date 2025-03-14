<?php
session_start();
include 'includes/db.php';

// Check if customer_id is set in session
if (!isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
$sql = "SELECT CustomerName, CustomerAddress FROM Customer WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();
$stmt->close();

// Fetch purchase orders for this customer
$sql = "SELECT PurchaseOrderID, TotalPrice, Status, CreatedDate, PaymentStatus 
        FROM PurchaseOrder 
        WHERE CustomerID = ? 
        ORDER BY CreatedDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header_index.php'; ?>
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Your Transactions</h2>

        <!-- Customer Info Card -->
        <?php if ($customer): ?>
            <div class="customer-card">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['CustomerName']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['CustomerAddress']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Transactions Table -->
        <?php if ($orders_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Total Price</th>
                            <th scope="col">Status</th>
                            <th scope="col">Payment Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $order['PurchaseOrderID']; ?></td>
                                <td>₱<?php echo number_format($order['TotalPrice'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $order['Status'] === 'Pending' ? 'bg-warning' : 'bg-success'; ?>">
                                        <?php echo $order['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $order['PaymentStatus'] === 'Pending' || $order['PaymentStatus'] === 'Unpaid' ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $order['PaymentStatus']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['CreatedDate'])); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['PurchaseOrderID']; ?>">View Details</button>
                                </td>
                            </tr>

                            <!-- Modal for Order Details -->
                            <div class="modal fade" id="orderDetailsModal<?php echo $order['PurchaseOrderID']; ?>" tabindex="-1" aria-labelledby="orderDetailsLabel<?php echo $order['PurchaseOrderID']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="orderDetailsLabel<?php echo $order['PurchaseOrderID']; ?>">Order #<?php echo $order['PurchaseOrderID']; ?> Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            $order_id = $order['PurchaseOrderID'];
                                            $line_sql = "SELECT ProductName, Quantity, UnitPrice, TotalPrice, SupplierID 
                                                         FROM PurchaseOrderLine 
                                                         WHERE PurchaseOrderID = ?";
                                            $line_stmt = $conn->prepare($line_sql);
                                            $line_stmt->bind_param("i", $order_id);
                                            $line_stmt->execute();
                                            $line_result = $line_stmt->get_result();
                                            ?>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total Price</th>
                                                        <th>Supplier ID</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($line = $line_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($line['ProductName']); ?></td>
                                                            <td><?php echo $line['Quantity']; ?></td>
                                                            <td>₱<?php echo number_format($line['UnitPrice'], 2); ?></td>
                                                            <td>₱<?php echo number_format($line['TotalPrice'], 2); ?></td>
                                                            <td><?php echo $line['SupplierID']; ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                            <?php $line_stmt->close(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-transactions">
                <p>No transactions found for your account.</p>
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-danger">Back to Home</a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>