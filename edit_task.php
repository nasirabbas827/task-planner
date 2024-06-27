<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Check if task_id is provided
if (isset($_GET["task_id"]) && !empty(trim($_GET["task_id"]))) {
    $task_id = trim($_GET["task_id"]);

    // Fetch task details
    $sql = "SELECT * FROM Tasks WHERE task_id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $task = $result->fetch_assoc();
            } else {
                header("location: error.php");
                exit;
            }
        } else {
            echo "Error: Could not execute the fetch query.";
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare the fetch query.";
    }
} else {
    header("location: error.php");
    exit;
}

// Handle task update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $due_date = trim($_POST["due_date"]);
    $priority_level = trim($_POST["priority_level"]);
    $status = trim($_POST["status"]);

    $sql = "UPDATE Tasks SET title = ?, description = ?, due_date = ?, priority_level = ?, status = ? WHERE task_id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssii", $title, $description, $due_date, $priority_level, $status, $task_id, $user_id);
        if ($stmt->execute()) {
            header("location: view_tasks.php");
            exit;
        } else {
            echo "Error: Could not execute the update query.";
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare the update query.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Edit Task</h2>
    <form action="edit_task.php?task_id=<?php echo $task_id; ?>" method="post">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($task['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($task['due_date']); ?>" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Priority Level</label>
            <select name="priority_level" class="form-control" required>
                <option value="Low" <?php echo ($task['priority_level'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo ($task['priority_level'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo ($task['priority_level'] == 'High') ? 'selected' : ''; ?>>High</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="Pending" <?php echo ($task['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Completed" <?php echo ($task['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Overdue" <?php echo ($task['status'] == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Update">
            <a href="view_tasks.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
