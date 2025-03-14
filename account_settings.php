<?php
session_start();
include 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['customer'];

// Fetch current user details
$sql = "SELECT * FROM customeraccount WHERE CustomerName = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = htmlspecialchars($_POST['username']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    // Check if username already exists (excluding current user)
    $check_sql = "SELECT CustomerAccountID FROM customeraccount WHERE CustomerName = ? AND CustomerName != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $new_username, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $errors[] = "Username already exists.";
    }
    $check_stmt->close();

    // Validate password
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Update username and password (if provided)
        if (!empty($new_password)) {
            $update_sql = "UPDATE customeraccount SET CustomerName = ?, Password = ? WHERE CustomerName = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $new_username, $new_password, $username);
        } else {
            $update_sql = "UPDATE customeraccount SET CustomerName = ? WHERE CustomerName = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_username, $username);
        }

        if ($update_stmt->execute()) {
            // Update session variable
            $_SESSION['customer'] = $new_username;
            $success = "Account updated successfully!";
        } else {
            $errors[] = "Failed to update account: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .account-settings {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .btn-update {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header_index.php'; ?>
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Account Settings</h2>
        <div class="account-settings">
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['CustomerName']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="text" class="form-control" id="password" name="password">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="text" class="form-control" id="confirm_password" name="confirm_password">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-update w-100">Update Account</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>