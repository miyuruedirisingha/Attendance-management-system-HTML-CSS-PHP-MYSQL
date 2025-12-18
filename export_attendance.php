<?php
require_once 'config.php';
requireLogin();

$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

if ($selected_class > 0) {
    // Get class name
    $class_result = mysqli_query($conn, "SELECT * FROM classes WHERE id = $selected_class");
    $class_info = mysqli_fetch_assoc($class_result);
    $class_name = $class_info['class_name'] . ' - ' . $class_info['section'];
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Write headers
    fputcsv($output, ['Attendance Report']);
    fputcsv($output, ['Class: ' . $class_name]);
    fputcsv($output, ['Period: ' . $from_date . ' to ' . $to_date]);
    fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Roll No', 'Student Name', 'Total Days', 'Present', 'Absent', 'Late', 'Excused', 'Attendance %']);
    
    // Get data
    $query = "SELECT s.roll_number, s.first_name, s.last_name,
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
    
    while ($row = mysqli_fetch_assoc($result)) {
        $total = $row['total_days'];
        $present = $row['present'];
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        fputcsv($output, [
            $row['roll_number'],
            $row['first_name'] . ' ' . $row['last_name'],
            $total,
            $present,
            $row['absent'],
            $row['late'],
            $row['excused'],
            $percentage . '%'
        ]);
    }
    
    fclose($output);
    exit();
}
?>
