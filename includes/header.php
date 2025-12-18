<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="index.php">Attendance System</a>
        </div>
        
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Reports</a></li>
            <li><a href="classes.php">Classes</a></li>
        </ul>
        
        <div class="nav-user">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" class="btn btn-small">Logout</a>
        </div>
    </div>
</nav>
