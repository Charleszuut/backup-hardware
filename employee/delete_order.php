<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Check if ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: transactions.php');
    exit();
}

$purchaseOrderID = (int)$_GET['id'];
$error = "";

// Delete the purchase order
$sql = "DELETE FROM PurchaseOrder WHERE PurchaseOrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchaseOrderID);

if ($stmt->execute()) {
    // Check if any rows were affected (ensures the ID existed)
    if ($stmt->affected_rows > 0) {
        header('Location: transactions.php?success=Order deleted successfully');
    } else {
        $error = "No order found with ID $purchaseOrderID.";
    }
} else {
    $error = "Error deleting order: " . $conn->error;
}

$stmt->close();
$conn->close();

// If there’s an error, display it (optional fallback)
if ($error) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>$error</div>
            <a href='transactions.php' class='btn btn-secondary'>Back to Transactions</a>
        </div>
    </body>
    </html>";
    exit();
}
?>