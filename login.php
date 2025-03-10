<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST['input']; // This can be either email (for customers) or username (for employees/admins)
    $password = $_POST['password'];

    // Debugging: Print input values
    echo "Input: $input<br>";
    echo "Password from form: $password<br>";

    // Check if the user is a customer (using email)
    $sql = "SELECT * FROM CustomerAccount WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Customer found: " . $row['CustomerName'] . "<br>"; // Debugging
        if (password_verify($password, $row['Password'])) {
            $_SESSION['customer'] = $row['CustomerName'];
            $_SESSION['customerAccountID'] = $row['CustomerAccountID']; // Store the customer account ID in the session
            header('Location: index.php');
            exit();
        } else {
            echo "Customer password mismatch.<br>"; // Debugging
        }
    }

    // Check if the user is an admin or employee (using username)
    $sql = "SELECT * FROM Employee WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Employee/Admin found: " . $row['Username'] . "<br>"; // Debugging
        echo "Password from DB: " . $row['Password'] . "<br>"; // Debugging
        echo "Role: " . $row['EmpPos'] . "<br>"; // Debugging

        // Check if the password matches (assuming plain text for now)
        if ($password === $row['Password']) {
            $_SESSION['user'] = $row['Username'];
            $_SESSION['role'] = $row['EmpPos']; // Store the role (Admin or Employee)
            $_SESSION['empID'] = $row['EmpID']; // Store the employee ID in the session

            // Debugging: Print session variables
            echo "Session role: " . $_SESSION['role'] . "<br>"; // Debugging

            // Redirect based on role
            if ($_SESSION['role'] == 'Admin') {
                echo "Redirecting to admin dashboard...<br>"; // Debugging
                header('Location: admin/dashboard.php');
                exit();
            } else {
                echo "Redirecting to employee dashboard...<br>"; // Debugging
                header('Location: employee/dashboard.php');
                exit();
            }
        } else {
            echo "Employee/Admin password mismatch.<br>"; // Debugging
        }
    }

    // If neither customer nor employee/admin is found, show an error
    $error = "Invalid email/username or password!";
    echo $error; // Debugging
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="input" class="form-label">Email or Username</label>
                                <input type="text" class="form-control" id="input" name="input" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>