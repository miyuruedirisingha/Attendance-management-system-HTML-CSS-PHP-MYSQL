<?php
require_once 'config.php';
requireLogin();

$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get all classes
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name, section");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>View Attendance Reports</h1>
        
        <div class="card">
            <h2>Filter Options</h2>
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php while ($class = mysqli_fetch_assoc($classes)): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo $class['class_name'] . ' - ' . $class['section']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date" value="<?php echo $from_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date" value="<?php echo $to_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">View Report</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($selected_class > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Attendance Report</h2>
                    <a href="export_attendance.php?class_id=<?php echo $selected_class; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" 
                       class="btn btn-success">Export to CSV</a>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Total Days</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Excused</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT s.id, s.roll_number, s.first_name, s.last_name,
                                      COUNT(a.id) as total_days,
                                      SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                                      SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                                      SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                                      SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused
                                      FROM students s
                                      LEFT JOIN attendance a ON s.id = a.student_id 
                                          AND a.attendance_date BETWEEN '$from_date' AND '$to_date'
                                      WHERE s.class_id = $selected_class AND s.status = 'active'
                                      GROUP BY s.id
                                      ORDER BY s.roll_number";
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $total = $row['total_days'];
                                    $present = $row['present'];
                                    $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
                                    $percentage_class = $percentage >= 75 ? 'badge-success' : ($percentage >= 50 ? 'badge-warning' : 'badge-danger');
                            ?>
                                <tr>
                                    <td><?php echo $row['roll_number']; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $total; ?></td>
                                    <td><span class="badge badge-success"><?php echo $present; ?></span></td>
                                    <td><span class="badge badge-danger"><?php echo $row['absent']; ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo $row['late']; ?></span></td>
                                    <td><span class="badge badge-info"><?php echo $row['excused']; ?></span></td>
                                    <td><span class="badge <?php echo $percentage_class; ?>"><?php echo $percentage; ?>%</span></td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No attendance records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <h2>Detailed Attendance Records</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, s.roll_number, s.first_name, s.last_name
                                      FROM attendance a
                                      JOIN students s ON a.student_id = s.id
                                      WHERE s.class_id = $selected_class 
                                        AND a.attendance_date BETWEEN '$from_date' AND '$to_date'
                                      ORDER BY a.attendance_date DESC, s.roll_number";
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $status_class = '';
                                    switch($row['status']) {
                                        case 'present': $status_class = 'badge-success'; break;
                                        case 'absent': $status_class = 'badge-danger'; break;
                                        case 'late': $status_class = 'badge-warning'; break;
                                        case 'excused': $status_class = 'badge-info'; break;
                                    }
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['attendance_date'])); ?></td>
                                    <td><?php echo $row['roll_number']; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td><?php echo $row['remarks']; ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No detailed records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
