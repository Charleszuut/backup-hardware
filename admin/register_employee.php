<?php
session_start();
include '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

include '../includes/db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empFName = $_POST['empFName'];
    $empLName = $_POST['empLName'];
    $empPos = 'Employee'; // Default position is Employee
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Insert employee using the InsertEmployee stored procedure
    $sql = "CALL InsertEmployee('$empFName', '$empLName', '$empPos', '$username', '$password', NULL)";
    if ($conn->query($sql)) {
        $success = "Employee registered successfully!";
    } else {
        $error = "Error registering employee: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Register Employee</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="empFName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="empFName" name="empFName" required>
            </div>
            <div class="mb-3">
                <label for="empLName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="empLName" name="empLName" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>