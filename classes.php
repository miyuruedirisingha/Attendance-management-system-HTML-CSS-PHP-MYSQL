<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM classes WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Class deleted successfully!";
    } else {
        $error = "Error deleting class!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = sanitize($_POST['class_name']);
    $section = sanitize($_POST['section']);
    $description = sanitize($_POST['description']);
    
    if (isset($_POST['class_id']) && $_POST['class_id']) {
        // Update
        $id = (int)$_POST['class_id'];
        $query = "UPDATE classes SET class_name='$class_name', section='$section', description='$description' WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            $success = "Class updated successfully!";
        } else {
            $error = "Error updating class!";
        }
    } else {
        // Insert
        $query = "INSERT INTO classes (class_name, section, description) VALUES ('$class_name', '$section', '$description')";
        if (mysqli_query($conn, $query)) {
            $success = "Class added successfully!";
        } else {
            $error = "Error adding class!";
        }
    }
}

// Get class for editing
$edit_class = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM classes WHERE id = $id");
    $edit_class = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Class Management</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php echo $edit_class ? 'Edit Class' : 'Add New Class'; ?></h2>
            <form method="POST" action="">
                <?php if ($edit_class): ?>
                    <input type="hidden" name="class_id" value="<?php echo $edit_class['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="class_name">Class Name *</label>
                        <input type="text" id="class_name" name="class_name" 
                               value="<?php echo $edit_class['class_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="section">Section *</label>
                        <input type="text" id="section" name="section" 
                               value="<?php echo $edit_class['section'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?php echo $edit_class['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_class ? 'Update Class' : 'Add Class'; ?>
                    </button>
                    <?php if ($edit_class): ?>
                        <a href="classes.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>All Classes</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Section</th>
                            <th>Description</th>
                            <th>Total Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT c.*, COUNT(s.id) as student_count 
                                  FROM classes c 
                                  LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                                  GROUP BY c.id 
                                  ORDER BY c.class_name, c.section";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td><?php echo $row['class_name']; ?></td>
                                <td><?php echo $row['section']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['student_count']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-small btn-info">Edit</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure? This will not delete students, but will unlink them from this class.')">Delete</a>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No classes found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
