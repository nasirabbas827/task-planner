<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch user's tasks for selection in reminder form
$sql = "SELECT task_id, title FROM Tasks WHERE user_id = ?";
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["set_reminder"])) {
    $task_id = $_POST["task_id"];
    $reminder_date = $_POST["reminder_date"];
    $reminder_time = $_POST["reminder_time"];
    $reminder_message = $_POST["reminder_message"];

    // Insert reminder into database
    $sql = "INSERT INTO Reminders (task_id, user_id, reminder_date, reminder_time, reminder_message) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iisss", $task_id, $user_id, $reminder_date, $reminder_time, $reminder_message);
        if ($stmt->execute()) {
            $success_message = "Reminder set successfully.";
        } else {
            $error_message = "Error: Could not execute the insert query.";
        }
        $stmt->close();
    } else {
        $error_message = "Error: Could not prepare the insert query.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Reminder</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Set Reminder</h2>
    <?php 
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="task_id">Select Task:</label>
            <select class="form-control" id="task_id" name="task_id" required>
                <option value="">Select Task</option>
                <?php foreach ($tasks as $task) : ?>
                <option value="<?php echo $task['task_id']; ?>"><?php echo htmlspecialchars($task['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="reminder_date">Reminder Date:</label>
            <input type="date" class="form-control" id="reminder_date" name="reminder_date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label for="reminder_time">Reminder Time:</label>
            <input type="time" class="form-control" id="reminder_time" name="reminder_time" required>
        </div>
        <div class="form-group">
            <label for="reminder_message">Reminder Message:</label>
            <textarea class="form-control" id="reminder_message" name="reminder_message" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="set_reminder">Set Reminder</button>
        <a class="btn btn-outline-dark" href="view_reminders.php">View Reminder</a>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
