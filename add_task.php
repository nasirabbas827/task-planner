<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_task"])) {
    $course_id = trim($_POST["course_id"]);
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $due_date = trim($_POST["due_date"]);
    $priority_level = trim($_POST["priority_level"]);
    $status = trim($_POST["status"]);

    // Validate input
    if (empty($title) || empty($course_id) || empty($due_date) || empty($priority_level) || empty($status)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert task into database
        $sql = "INSERT INTO Tasks (course_id, user_id, title, description, due_date, priority_level, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iisssss", $course_id, $user_id, $title, $description, $due_date, $priority_level, $status);
            if ($stmt->execute()) {
                $success_message = "Task added successfully.";
            } else {
                $error_message = "Error: Could not execute the query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the query.";
        }
    }
}

// Fetch user's courses for the dropdown
$sql = "SELECT course_id, course_name FROM Courses WHERE user_id = ?";
$courses = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Add a New Task</h2>
    <?php 
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="course_id">Course:</label>
            <select class="form-control" id="course_id" name="course_id">
                <?php foreach ($courses as $course) : ?>
                <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Task Title:</label>
            <input type="text" class="form-control" id="title" name="title">
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date:</label>
            <input type="date" class="form-control" id="due_date" min="<?php echo date('Y-m-d'); ?>" name="due_date">
        </div>
        <div class="form-group">
            <label for="priority_level">Priority Level:</label>
            <select class="form-control" id="priority_level" name="priority_level">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select class="form-control" id="status" name="status">
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Overdue">Overdue</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="add_task">Add Task</button>
        <a class="btn btn-outline-dark" href="view_tasks.php">View Tasks</a>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
