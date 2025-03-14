<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Check if the user is logged in and is an employee
if (!isEmployee()) {
    header('Location: ../login.php');
    exit();
}

// Get the PurchaseOrderID from the URL
if (!isset($_GET['id'])) {
    header('Location: employee_transactions.php');
    exit();
}
$purchase_order_id = $_GET['id'];

// Fetch the purchase order details
$sql = "SELECT * FROM PurchaseOrder WHERE PurchaseOrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchase_order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: employee_transactions.php');
    exit();
}
$order = $result->fetch_assoc();

// Handle form submission to update the order status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];

    // Update the order status
    $sql = "UPDATE PurchaseOrder SET Status = ? WHERE PurchaseOrderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $purchase_order_id);

    if ($stmt->execute()) {
        header('Location: employee_transactions.php');
        exit();
    } else {
        $error = "Failed to update order status.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Update Order Status</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Pending" <?php echo $order['Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Processing" <?php echo $order['Status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="Completed" <?php echo $order['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $order['Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Status</button