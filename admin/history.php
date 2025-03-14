<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// If auth.php has isAdmin(), use it; otherwise, fetch role from DB
if (function_exists('isAdmin')) {
    if (!isAdmin()) {
        header('Location: ../login.php');
        exit();
    }
} else {
    // Assuming $_SESSION['user'] is the username, fetch role
    $username = $_SESSION['user'];
    $role_sql = "SELECT Role FROM employee WHERE Username = ?";
    $stmt = $conn->prepare($role_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $role_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$role_result || $role_result['Role'] !== 'Admin') {
        header('Location: ../login.php');
        exit();
    }
}

// Fetch employee attendance history
$sql = "SELECT ea.AttendanceID, e.EmpFName, e.EmpLName, ea.CheckInTime, ea.CheckOutTime, 
        TIMESTAMPDIFF(MINUTE, ea.CheckInTime, COALESCE(ea.CheckOutTime, NOW())) AS DurationMinutes, 
        ea.Date
        FROM employeeattendance ea 
        JOIN employee e ON ea.EmpID = e.EmpID 
        ORDER BY ea.CheckInTime DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .history-table {
            margin-top: 20px;
        }
        .duration {
            color: green;
        }
        .ongoing {
            color: orange;
        }
    </style>
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee Attendance History</h2>

        <!-- Employee Attendance Table -->
        <div class="history-table">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Check-In Time</th>
                        <th>Check-Out Time</th>
                        <th>Duration (Minutes)</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['AttendanceID']; ?></td>
                            <td><?php echo htmlspecialchars($row['EmpFName'] . ' ' . $row['EmpLName']); ?></td>
                            <td><?php echo $row['CheckInTime'] ? $row['CheckInTime'] : 'N/A'; ?></td>
                            <td><?php echo $row['CheckOutTime'] ? $row['CheckOutTime'] : 'Ongoing'; ?></td>
                            <td>
                                <?php if ($row['CheckOutTime']): ?>
                                    <span class="duration"><?php echo $row['DurationMinutes']; ?></span>
                                <?php else: ?>
                                    <span class="ongoing"><?php echo $row['DurationMinutes']; ?> (Ongoing)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['Date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>