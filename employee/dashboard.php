<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four A's Marketing (Employee)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .punch-in-card {
            background-color: #007bff; /* Blue background for emphasis */
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .punch-in-card .card-title {
            font-size: 1.75rem;
            font-weight: bold;
        }
        .punch-in-card .btn {
            background-color: #fff;
            color: #007bff;
            font-weight: bold;
            border: none;
        }
        .punch-in-card .btn:hover {
            background-color: #f8f9fa;
        }
        .secondary-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .secondary-card .card-title {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-5">Employee Dashboard</h2>

        <!-- Punch In Products (Prominent Section) -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card punch-in-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Order Products</h5>
                        <p class="card-text">Order products for walk-in customers.</p>
                        <a href="punch_in.php" class="btn btn-light">Order Products now</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Options (Transactions and History) -->
        <div class="row">
            <div class="col-md-6">
                <div class="card secondary-card">
                    <div class="card-body">
                        <h5 class="card-title">View Transactions</h5>
                        <p class="card-text">Manage ongoing transactions.</p>
                        <a href="transactions.php" class="btn btn-primary">Go to Transactions</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card secondary-card">
                    <div class="card-body">
                        <h5 class="card-title">View History</h5>
                        <p class="card-text">View completed transactions.</p>
                        <a href="history.php" class="btn btn-primary">Go to History</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>