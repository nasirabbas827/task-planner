<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Handle deletion of a task
if (isset($_GET["delete"]) && !empty(trim($_GET["delete"]))) {
    $task_id = trim($_GET["delete"]);

    $sql = "DELETE FROM Tasks WHERE task_id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            $delete_message = "Task deleted successfully.";
        } else {
            $delete_error = "Error: Could not execute the delete query.";
        }
        $stmt->close();
    } else {
        $delete_error = "Error: Could not prepare the delete query.";
    }
}

// Fetch user's tasks
$sql = "SELECT t.*, c.course_name FROM Tasks t JOIN Courses c ON t.course_id = c.course_id WHERE t.user_id = ?";
$tasks = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tasks</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .priority-low { background-color: #d4edda; }
        .priority-medium { background-color: #ffeeba; }
        .priority-high { background-color: #f8d7da; }
        .status-pending { color: #007bff; } /* Blue */
        .status-completed { color: #28a745; } /* Green */
        .status-overdue { color: #dc3545; } /* Red */
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Tasks</h2>
    <?php 
    if (!empty($delete_message)) {
        echo '<div class="alert alert-success">' . $delete_message . '</div>';
    }
    if (!empty($delete_error)) {
        echo '<div class="alert alert-danger">' . $delete_error . '</div>';
    }
    ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task) : ?>
            <?php
                // Calculate days until due date
                $due_date = strtotime($task['due_date']);
                $current_date = time();
                $days_until_due = floor(($due_date - $current_date) / (60 * 60 * 24));

                // Determine status class and notification
                if ($days_until_due < 3) {
                    $notification = '<span class="badge badge-warning">Due Soon</span>';
                } else {
                    $notification = '';
                }

                switch ($task['status']) {
                    case 'Pending':
                        $status_class = 'status-pending';
                        break;
                    case 'Completed':
                        $status_class = 'status-completed';
                        break;
                    case 'Overdue':
                        $status_class = 'status-overdue';
                        break;
                    default:
                        $status_class = '';
                        break;
                }
            ?>
            <tr class="priority-<?php echo strtolower($task['priority_level']); ?> <?php echo $status_class; ?>">
                <td><?php echo htmlspecialchars($task['course_name']); ?></td>
                <td><?php echo htmlspecialchars($task['title']); ?></td>
                <td><?php echo htmlspecialchars($task['description']); ?></td>
                <td><?php echo htmlspecialchars($task['due_date']); ?> <?php echo $notification; ?></td>
                <td><?php echo htmlspecialchars($task['priority_level']); ?></td>
                <td><?php echo htmlspecialchars($task['status']); ?></td>
                <td>
                    <a href="edit_task.php?task_id=<?php echo $task['task_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="view_tasks.php?delete=<?php echo $task['task_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
