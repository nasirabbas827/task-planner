<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$reminder_id = isset($_GET["reminder_id"]) ? trim($_GET["reminder_id"]) : "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_reminder"])) {
    $reminder_date = trim($_POST["reminder_date"]);
    $reminder_time = trim($_POST["reminder_time"]);
    $reminder_message = trim($_POST["reminder_message"]);

    // Validate input
    if (empty($reminder_date) || empty($reminder_time) || empty($reminder_message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Update reminder in database
        $sql = "UPDATE Reminders SET reminder_date = ?, reminder_time = ?, reminder_message = ?, updated_at = NOW() WHERE reminder_id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $reminder_date, $reminder_time, $reminder_message, $reminder_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Reminder updated successfully.";
                header("Location: view_reminders.php");
                exit;
            } else {
                $error_message = "Error: Could not execute the update query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the update query.";
        }
    }
}

// Fetch the reminder details
$sql = "SELECT * FROM Reminders WHERE reminder_id = ? AND user_id = ?";
$reminder = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $reminder_id, $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $reminder = $row;
        } else {
            $error_message = "Error: Reminder not found.";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Reminder</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Edit Reminder</h2>
    <?php 
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <?php if ($reminder): ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?reminder_id=" . $reminder_id; ?>" method="post">
        <div class="form-group">
            <label for="reminder_date">Reminder Date:</label>
            <input type="date" class="form-control" id="reminder_date"  min="<?php echo date('Y-m-d'); ?>" name="reminder_date" value="<?php echo htmlspecialchars($reminder['reminder_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="reminder_time">Reminder Time:</label>
            <input type="time" class="form-control" id="reminder_time" name="reminder_time" value="<?php echo htmlspecialchars($reminder['reminder_time']); ?>" required>
        </div>
        <div class="form-group">
            <label for="reminder_message">Reminder Message:</label>
            <textarea class="form-control" id="reminder_message" name="reminder_message" rows="3" required><?php echo htmlspecialchars($reminder['reminder_message']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="update_reminder">Update Reminder</button>
    </form>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
