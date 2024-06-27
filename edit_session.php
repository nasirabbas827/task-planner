<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$session_id = isset($_GET["session_id"]) ? trim($_GET["session_id"]) : "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_session"])) {
    $session_date = trim($_POST["session_date"]);
    $start_time = trim($_POST["start_time"]);
    $end_time = trim($_POST["end_time"]);
    $session_notes = trim($_POST["session_notes"]);

    // Validate input
    if (empty($session_date) || empty($start_time) || empty($end_time)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Update session in database
        $sql = "UPDATE StudySessions SET session_date = ?, start_time = ?, end_time = ?, session_notes = ?, updated_at = NOW() WHERE session_id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssii", $session_date, $start_time, $end_time, $session_notes, $session_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Session updated successfully.";
                header("Location: view_sessions.php");
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

// Fetch the session details
$sql = "SELECT ss.*, c.course_name FROM StudySessions ss JOIN Courses c ON ss.course_id = c.course_id WHERE ss.session_id = ? AND ss.user_id = ?";
$session = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $session_id, $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $session = $row;
        } else {
            $error_message = "Error: Session not found.";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Study Session</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Edit Study Session</h2>
    <?php 
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <?php if ($session): ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?session_id=" . $session_id; ?>" method="post">
        <div class="form-group">
            <label for="session_date">Session Date:</label>
            <input type="date" class="form-control" id="session_date" min="<?php echo date('Y-m-d'); ?>" name="session_date" value="<?php echo htmlspecialchars($session['session_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($session['start_time']); ?>" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time:</label>
            <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($session['end_time']); ?>" required>
        </div>
        <div class="form-group">
            <label for="session_notes">Session Notes:</label>
            <textarea class="form-control" id="session_notes" name="session_notes"><?php echo htmlspecialchars($session['session_notes']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="update_session">Update Session</button>
    </form>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
