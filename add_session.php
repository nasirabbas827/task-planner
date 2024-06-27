<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch user's courses to populate the dropdown
$sql_courses = "SELECT * FROM Courses WHERE user_id = ?";
$courses = [];
if ($stmt = $conn->prepare($sql_courses)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_session"])) {
    $course_id = trim($_POST["course_id"]);
    $session_date = trim($_POST["session_date"]);
    $start_time = trim($_POST["start_time"]);
    $end_time = trim($_POST["end_time"]);
    $session_notes = trim($_POST["session_notes"]);

    // Validate input (you may want to add more validation as per your requirements)
    if (empty($course_id) || empty($session_date) || empty($start_time) || empty($end_time)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert into StudySessions table
        $sql_insert = "INSERT INTO StudySessions (user_id, course_id, session_date, start_time, end_time, session_notes)
                       VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param("iissss", $user_id, $course_id, $session_date, $start_time, $end_time, $session_notes);
            if ($stmt->execute()) {
                $success_message = "Study session added successfully.";
                header("Location: view_sessions.php");
                exit;
            } else {
                $error_message = "Error: Could not execute the insert query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the insert query.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Study Session</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Add Study Session</h2>
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
            <select class="form-control" id="course_id" name="course_id" required>
                <!-- Populate with user's courses fetched from database -->
                <?php foreach ($courses as $course) : ?>
                    <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="session_date">Session Date:</label>
            <input type="date" class="form-control" id="session_date" min="<?php echo date('Y-m-d'); ?>" name="session_date" required>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time:</label>
            <input type="time" class="form-control" id="end_time"  name="end_time" required>
        </div>
        <div class="form-group">
            <label for="session_notes">Session Notes:</label>
            <textarea class="form-control" id="session_notes" name="session_notes"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="add_session">Add Study Session</button>
        <a class="btn btn-outline-dark" href="view_sessions.php">View Sessions</a>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
