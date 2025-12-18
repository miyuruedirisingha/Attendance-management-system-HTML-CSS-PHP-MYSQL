<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';
$selected_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');
$selected_class = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $attendance_date = sanitize($_POST['attendance_date']);
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($_POST['attendance'] as $student_id => $status) {
            $student_id = (int)$student_id;
            $status = sanitize($status);
            $remarks = isset($_POST['remarks'][$student_id]) ? sanitize($_POST['remarks'][$student_id]) : '';
            
            // Check if attendance already exists
            $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id=$student_id AND attendance_date='$attendance_date'");
            
            if (mysqli_num_rows($check) > 0) {
                // Update existing
                $query = "UPDATE attendance SET status='$status', remarks='$remarks', marked_by=$user_id 
                          WHERE student_id=$student_id AND attendance_date='$attendance_date'";
            } else {
                // Insert new
                $query = "INSERT INTO attendance (student_id, attendance_date, status, remarks, marked_by) 
                          VALUES ($student_id, '$attendance_date', '$status', '$remarks', $user_id)";
            }
            
            if (mysqli_query($conn, $query)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        if ($success_count > 0) {
            $success = "Attendance marked successfully for $success_count student(s)!";
        }
        if ($error_count > 0) {
            $error = "Error marking attendance for $error_count student(s)!";
        }
    }
}

// Get all classes
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name, section");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Mark Attendance</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Select Date and Class</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="attendance_date">Date</label>
                        <input type="date" id="attendance_date" name="attendance_date" 
                               value="<?php echo $selected_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php 
                            mysqli_data_seek($classes, 0);
                            while ($class = mysqli_fetch_assoc($classes)): 
                            ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo $class['class_name'] . ' - ' . $class['section']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Load Students</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($selected_class > 0): ?>
            <div class="card">
                <h2>Mark Attendance for <?php echo date('F d, Y', strtotime($selected_date)); ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                    <input type="hidden" name="mark_attendance" value="1">
                    
                    <div class="attendance-actions">
                        <button type="button" class="btn btn-small btn-success" onclick="markAll('present')">Mark All Present</button>
                        <button type="button" class="btn btn-small btn-danger" onclick="markAll('absent')">Mark All Absent</button>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT s.*, a.status as current_status, a.remarks as current_remarks 
                                          FROM students s 
                                          LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = '$selected_date'
                                          WHERE s.class_id = $selected_class AND s.status = 'active'
                                          ORDER BY s.roll_number";
                                $result = mysqli_query($conn, $query);
                                
                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                        $current_status = $row['current_status'] ?? 'absent';
                                ?>
                                    <tr>
                                        <td><?php echo $row['roll_number']; ?></td>
                                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $row['id']; ?>]" class="attendance-select">
                                                <option value="present" <?php echo $current_status == 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo $current_status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="late" <?php echo $current_status == 'late' ? 'selected' : ''; ?>>Late</option>
                                                <option value="excused" <?php echo $current_status == 'excused' ? 'selected' : ''; ?>>Excused</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="remarks[<?php echo $row['id']; ?>]" 
                                                   value="<?php echo $row['current_remarks'] ?? ''; ?>" 
                                                   placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center;">No students found in this class</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function markAll(status) {
            const selects = document.querySelectorAll('.attendance-select');
            selects.forEach(select => {
                select.value = status;
            });
        }
    </script>
</body>
</html>
