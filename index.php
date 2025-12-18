<?php
require_once 'config.php';
requireLogin();

// Get statistics
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE status='active'"))['count'];
$total_classes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM classes"))['count'];
$today = date('Y-m-d');
$today_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE attendance_date='$today'"))['count'];
$present_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE attendance_date='$today' AND status='present'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $total_classes; ?></h3>
                    <p>Total Classes</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $present_today; ?></h3>
                    <p>Present Today</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?php echo $today_attendance; ?></h3>
                    <p>Marked Today</p>
                </div>
            </div>
        </div>
        
        <div class="recent-section">
            <h2>Recent Attendance (Today)</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT a.*, s.roll_number, s.first_name, s.last_name, c.class_name, c.section 
                                  FROM attendance a 
                                  JOIN students s ON a.student_id = s.id 
                                  LEFT JOIN classes c ON s.class_id = c.id 
                                  WHERE a.attendance_date = '$today' 
                                  ORDER BY a.created_at DESC 
                                  LIMIT 10";
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
                                <td><?php echo $row['roll_number']; ?></td>
                                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                <td><?php echo $row['class_name'] . ' - ' . $row['section']; ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td><?php echo date('h:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No attendance marked today</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
