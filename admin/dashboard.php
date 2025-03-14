<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php'; // Include database connection

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Fetch monthly sales data for the current year
$current_year = date('Y');
$sales_sql = "
    SELECT 
        MONTH(po.CreatedDate) AS month,
        SUM(po.TotalPrice) AS total_sales
    FROM PurchaseOrder po
    WHERE YEAR(po.CreatedDate) = ?
    GROUP BY MONTH(po.CreatedDate)
    ORDER BY month ASC
";
$stmt = $conn->prepare($sales_sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error); // Debug if prepare fails
}
$stmt->bind_param("i", $current_year);
$stmt->execute();
$sales_result = $stmt->get_result();

// Prepare data for the chart
$months = array_fill(1, 12, 0); // Initialize array with 0 for all 12 months
$month_names = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
    7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
];
while ($row = $sales_result->fetch_assoc()) {
    $months[(int)$row['month']] = (float)$row['total_sales'];
}
$stmt->close();

// Convert data to JSON for JavaScript
$labels = json_encode(array_values($month_names));
$sales_data = json_encode(array_values($months));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four A's Marketing (Admin Dashboard)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Admin Dashboard</h2>

        <!-- Monthly Sales Graph -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Monthly Sales History (<?php echo $current_year; ?>)</h5>
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Manage Products</h5>
                        <p class="card-text">Add, edit, or delete products.</p>
                        <a href="products.php" class="btn btn-primary">Go to Products</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Manage Transactions</h5>
                        <p class="card-text">View and manage transactions.</p>
                        <a href="transactions.php" class="btn btn-primary">Go to Transactions</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Register Employee</h5>
                        <p class="card-text">Register new employee accounts.</p>
                        <a href="register_employee.php" class="btn btn-primary">Register Employee</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar', // You can change to 'line' for a line graph
            data: {
                labels: <?php echo $labels; ?>, // Month names (Jan-Dec)
                datasets: [{
                    label: 'Monthly Sales (₱)',
                    data: <?php echo $sales_data; ?>, // Sales totals
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Amount (₱)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    </script>
</body>
</html>