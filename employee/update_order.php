<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    echo "Redirecting to login.php because user is not set.";
    header('Location: ../login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: transactions.php');
    exit();
}

$purchaseOrderID = (int)$_GET['id'];
$success = $error = "";

$sql = "SELECT po.PurchaseOrderID, po.Status, c.CustomerName, p.ProductName, pol.Quantity, pol.TotalPrice
        FROM PurchaseOrder po
        JOIN Customer c ON po.CustomerID = c.CustomerID
        JOIN PurchaseOrderLine pol ON po.PurchaseOrderID = pol.PurchaseOrderID
        JOIN Products p ON pol.ProductID = p.ProductID
        WHERE po.PurchaseOrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchaseOrderID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: transactions.php');
    exit();
}

$order = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $updateSql = "UPDATE PurchaseOrder SET Status = ? WHERE PurchaseOrderID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $purchaseOrderID);

    if ($updateStmt->execute()) {
        $success = "Order status updated successfully!";
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
    } else {
        $error = "Error updating status: " . $conn->error;
    }
    $updateStmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Update Order Status</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order #<?php echo $order['PurchaseOrderID']; ?></h5>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['CustomerName']); ?></p>
                <p><strong>Product:</strong> <?php echo htmlspecialchars($order['ProductName']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $order['Quantity']; ?></p>
                <p><strong>Total:</strong> ₱<?php echo number_format($order['TotalPrice'], 2); ?></p>

                <form method="POST">
                    <div class="mb-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Pending" <?php echo $order['Status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Paid" <?php echo $order['Status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    <a href="transactions.php" class="btn btn-secondary">Back to Transactions</a>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>