<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four A's Marketing (Employee)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">View Transactions</h5>
                        <p class="card-text">Manage ongoing transactions.</p>
                        <a href="transactions.php" class="btn btn-primary">Go to Transactions</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
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