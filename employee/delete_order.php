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

// Delete the purchase order
$sql = "DELETE FROM PurchaseOrder WHERE PurchaseOrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchase_order_id);

if ($stmt->execute()) {
    header('Location: employee_transactions.php');
    exit();
} else {
    die("Failed to delete order.");
}
?>