<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM students WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Student deleted successfully!";
    } else {
        $error = "Error deleting student!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_number = sanitize($_POST['roll_number']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $class_id = (int)$_POST['class_id'];
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $address = sanitize($_POST['address']);
    $status = sanitize($_POST['status']);
    
    if (isset($_POST['student_id']) && $_POST['student_id']) {
        // Update
        $id = (int)$_POST['student_id'];
        $query = "UPDATE students SET roll_number='$roll_number', first_name='$first_name', 
                  last_name='$last_name', email='$email', phone='$phone', class_id=$class_id, 
                  date_of_birth='$date_of_birth', address='$address', status='$status' 
                  WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            $success = "Student updated successfully!";
        } else {
            $error = "Error updating student!";
        }
    } else {
        // Insert
        $query = "INSERT INTO students (roll_number, first_name, last_name, email, phone, class_id, date_of_birth, address, status) 
                  VALUES ('$roll_number', '$first_name', '$last_name', '$email', '$phone', $class_id, '$date_of_birth', '$address', '$status')";
        if (mysqli_query($conn, $query)) {
            $success = "Student added successfully!";
        } else {
            $error = "Error adding student! Roll number may already exist.";
        }
    }
}

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
    $edit_student = mysqli_fetch_assoc($result);
}

// Get all classes for dropdown
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name, section");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Student Management</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
            <form method="POST" action="">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="student_id" value="<?php echo $edit_student['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="roll_number">Roll Number *</label>
                        <input type="text" id="roll_number" name="roll_number" 
                               value="<?php echo $edit_student['roll_number'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo $edit_student['first_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo $edit_student['last_name'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo $edit_student['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?php echo $edit_student['phone'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" 
                               value="<?php echo $edit_student['date_of_birth'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="class_id">Class *</label>
                        <select id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php while ($class = mysqli_fetch_assoc($classes)): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo ($edit_student && $edit_student['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo $class['class_name'] . ' - ' . $class['section']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($edit_student && $edit_student['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_student && $edit_student['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo $edit_student['address'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_student ? 'Update Student' : 'Add Student'; ?>
                    </button>
                    <?php if ($edit_student): ?>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>All Students</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT s.*, c.class_name, c.section 
                                  FROM students s 
                                  LEFT JOIN classes c ON s.class_id = c.id 
                                  ORDER BY s.roll_number";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td><?php echo $row['roll_number']; ?></td>
                                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td><?php echo $row['class_name'] . ' - ' . $row['section']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-small btn-info">Edit</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No students found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
