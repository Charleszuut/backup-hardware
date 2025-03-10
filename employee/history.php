<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_employee.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee History</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee Name</th>
                    <th>Role</th>
                    <th>Action Taken</th>
                    <th>Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all employee history records
                $sql = "SELECT eh.HistoryID, e.EmpFName, e.EmpLName, e.EmpPos, eh.ActionTaken, eh.ActionDate, eh.Notes
                        FROM EmployeeHistory eh
                        JOIN Employee e ON eh.EmpID = e.EmpID";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()):
                ?>
                        <tr>
                            <td><?php echo $row['HistoryID']; ?></td>
                            <td><?php echo $row['EmpFName'] . ' ' . $row['EmpLName']; ?></td>
                            <td><?php echo $row['EmpPos']; ?></td>
                            <td><?php echo $row['ActionTaken']; ?></td>
                            <td><?php echo $row['ActionDate']; ?></td>
                            <td><?php echo $row['Notes']; ?></td>
                        </tr>
                <?php
                    endwhile;
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No history found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>