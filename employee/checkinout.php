<?php
session_start();
include '../includes/auth.php'; // Use auth.php for authentication
include '../includes/db.php';   // Database connection

// Get the logged-in employee's username from $_SESSION['user']
$logged_in_username = $_SESSION['user'] ?? 'Not logged in';
$current_date = date('Y-m-d'); // Current date for filtering attendance

// Fetch all employees
$sql = "SELECT EmpID, EmpFName, EmpLName, EmpPos, Username, Role, Status, Phone FROM employee";
$employee_result = $conn->query($sql);
if (!$employee_result) {
    die("Employee query failed: " . $conn->error);
}

// Fetch today's attendance records initially
$attendance_sql = "
    SELECT ea.EmpID, ea.CheckInTime, ea.CheckOutTime 
    FROM EmployeeAttendance ea 
    WHERE DATE(ea.CheckInTime) = ? OR DATE(ea.CheckOutTime) = ?
";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("ss", $current_date, $current_date);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
$attendance_data = [];
while ($row = $attendance_result->fetch_assoc()) {
    $attendance_data[$row['EmpID']] = $row;
}
$attendance_stmt->close();

// Handle check-in/check-out action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $_POST['emp_id'];
    $action = $_POST['action'];

    // Verify the action is for the logged-in employee
    $check_sql = "SELECT Username FROM employee WHERE EmpID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $emp_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $employee = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($employee['Username'] !== $logged_in_username) {
        $error = "You can only update your own attendance.";
    } else {
        if ($action === 'check_in') {
            if (isset($attendance_data[$emp_id]) && $attendance_data[$emp_id]['CheckInTime'] && !$attendance_data[$emp_id]['CheckOutTime']) {
                $error = "You are already checked in for today.";
            } else {
                $checkin_sql = "INSERT INTO EmployeeAttendance (EmpID, CheckInTime) VALUES (?, NOW())";
                $stmt = $conn->prepare($checkin_sql);
                $stmt->bind_param("i", $emp_id);
                if ($stmt->execute()) {
                    $success = "Checked in successfully at " . date('H:i:s');
                } else {
                    $error = "Check-in failed: " . $stmt->error;
                }
                $stmt->close();
            }
        } elseif ($action === 'check_out') {
            if (!isset($attendance_data[$emp_id]) || !$attendance_data[$emp_id]['CheckInTime'] || $attendance_data[$emp_id]['CheckOutTime']) {
                $error = "You must check in before checking out, or you’ve already checked out.";
            } else {
                $checkout_sql = "UPDATE EmployeeAttendance SET CheckOutTime = NOW() WHERE EmpID = ? AND DATE(CheckInTime) = ? AND CheckOutTime IS NULL";
                $stmt = $conn->prepare($checkout_sql);
                $stmt->bind_param("is", $emp_id, $current_date);
                if ($stmt->execute()) {
                    $success = "Checked out successfully at " . date('H:i:s');
                } else {
                    $error = "Check-out failed: " . $stmt->error;
                }
                $stmt->close();
            }
        }

        // Refresh attendance data after action by re-preparing the statement
        $attendance_stmt = $conn->prepare($attendance_sql); // Re-prepare the statement
        $attendance_stmt->bind_param("ss", $current_date, $current_date);
        $attendance_stmt->execute();
        $attendance_result = $attendance_stmt->get_result();
        $attendance_data = [];
        while ($row = $attendance_result->fetch_assoc()) {
            $attendance_data[$row['EmpID']] = $row;
        }
        $attendance_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Check-In/Check-Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .employee-table { margin-top: 20px; }
        .action-btn { margin-right: 5px; }
        .status-badge { font-size: 0.9em; }
    </style>
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee Check-In/Check-Out (<?php echo $current_date; ?>)</h2>
        <p>Logged in as: <?php echo htmlspecialchars($logged_in_username); ?></p>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="table-responsive employee-table">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Position</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($employee_result->num_rows > 0) {
                        while ($row = $employee_result->fetch_assoc()) {
                            $is_logged_in_employee = ($row['Username'] === $logged_in_username);
                            $emp_id = $row['EmpID'];
                            $check_in_time = isset($attendance_data[$emp_id]['CheckInTime']) ? date('H:i:s', strtotime($attendance_data[$emp_id]['CheckInTime'])) : '-';
                            $check_out_time = isset($attendance_data[$emp_id]['CheckOutTime']) ? date('H:i:s', strtotime($attendance_data[$emp_id]['CheckOutTime'])) : '-';
                            $checked_in = isset($attendance_data[$emp_id]['CheckInTime']) && !$attendance_data[$emp_id]['CheckOutTime'];
                            $checked_out = isset($attendance_data[$emp_id]['CheckOutTime']);
                            $check_in_disabled = $checked_in ? 'disabled' : '';
                            $check_out_disabled = !$checked_in || $checked_out ? 'disabled' : '';
                    ?>
                    <tr>
                        <td><?php echo $row['EmpID']; ?></td>
                        <td><?php echo htmlspecialchars($row['EmpFName']); ?></td>
                        <td><?php echo htmlspecialchars($row['EmpLName']); ?></td>
                        <td><?php echo htmlspecialchars($row['EmpPos']); ?></td>
                        <td><?php echo htmlspecialchars($row['Username']); ?></td>
                        <td><?php echo htmlspecialchars($row['Role']); ?></td>
                        <td><?php echo $check_in_time; ?></td>
                        <td><?php echo $check_out_time; ?></td>
                        <td>
                            <?php if ($is_logged_in_employee && $logged_in_username !== 'Not logged in'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
                                    <button type="submit" name="action" value="check_in" class="btn btn-success btn-sm action-btn" <?php echo $check_in_disabled; ?>>Check In</button>
                                    <button type="submit" name="action" value="check_out" class="btn btn-danger btn-sm action-btn" <?php echo $check_out_disabled; ?>>Check Out</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No employees found.</td></tr>";
                    }
                    $employee_result->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>