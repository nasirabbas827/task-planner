<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch total counts
$sql_total_tasks = "SELECT COUNT(*) AS total_tasks FROM Tasks WHERE user_id = ?";
$sql_total_courses = "SELECT COUNT(*) AS total_courses FROM Courses WHERE user_id = ?";
$sql_total_reminders = "SELECT COUNT(*) AS total_reminders FROM Reminders WHERE user_id = ?";
$total_tasks = $total_courses = $total_reminders = 0;

if ($stmt = $conn->prepare($sql_total_tasks)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_tasks = $row['total_tasks'];
    }
    $stmt->close();
}

if ($stmt = $conn->prepare($sql_total_courses)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_courses = $row['total_courses'];
    }
    $stmt->close();
}

if ($stmt = $conn->prepare($sql_total_reminders)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_reminders = $row['total_reminders'];
    }
    $stmt->close();
}

// Fetch tasks based on priority levels and due date within the current week
$current_date = date('Y-m-d');
$end_of_week = date('Y-m-d', strtotime('next Sunday'));
$sql_priority_tasks = "SELECT * FROM Tasks WHERE user_id = ? AND due_date >= ? AND due_date <= ? ORDER BY FIELD(priority_level, 'High', 'Medium', 'Low')";
$priority_tasks = [];
if ($stmt = $conn->prepare($sql_priority_tasks)) {
    $stmt->bind_param("iss", $user_id, $current_date, $end_of_week);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $priority_tasks[$row['priority_level']][] = $row;
        }
    }
    $stmt->close();
}

// Fetch reminders
$sql_reminders = "SELECT * FROM Reminders WHERE user_id = ?";
$reminders = [];
if ($stmt = $conn->prepare($sql_reminders)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reminders[] = $row;
        }
    }
    $stmt->close();
}

// Fetch study sessions
$sql_sessions = "SELECT ss.*, c.course_name FROM StudySessions ss JOIN Courses c ON ss.course_id = c.course_id WHERE ss.user_id = ?";
$sessions = [];
if ($stmt = $conn->prepare($sql_sessions)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Planner Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>

.card {
    border: none;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card .card-body {
    padding: 20px;
    border-radius: 5px;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.card .display-4 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #495057;
}

/* Priority Colors */
.priority-low {
    background-color: #d4edda;
    border-left: 5px solid #28a745;
}

.priority-medium {
    background-color: #ffeeba;
    border-left: 5px solid #ffc107;
}

.priority-high {
    background-color: #f8d7da;
    border-left: 5px solid #dc3545;
}

/* Reminder Styles */
.reminder-card {
    background-color: #e2e3e5;
    border-left: 5px solid #6c757d;
}

.reminder-card .card-title {
    color: #343a40;
}

/* Calendar Styles */
#calendar {
    margin-top: 20px;
    background-color: #ffffff;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

.fc-toolbar {
    margin-bottom: 20px;
}

.fc-button {
    background-color: #007bff;
    border: none;
    color: #fff;
    border-radius: 5px;
    padding: 5px 10px;
    margin-right: 5px;
    font-size: 0.9rem;
}

.fc-button:hover {
    background-color: #0056b3;
}

.fc-button:focus {
    outline: none;
}

.fc-button-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.fc-button-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.fc-today-button {
    background-color: #28a745;
    border-color: #28a745;
}

.fc-today-button:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.fc-view-container {
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.fc-day-header {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.fc-day {
    padding: 15px;
    border-right: 1px solid #dee2e6;
}

.fc-day-number {
    font-weight: 600;
}

.fc-event {
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    font-size: 0.85rem;
    margin-bottom: 5px;
}

.fc-event.fc-event-hori {
    margin-bottom: 5px;
}

.fc-event, .fc-event-dot {
    background-color: #007bff;
    color: #fff;
}

.fc-event:hover {
    background-color: #0056b3;
}

.fc-title {
    font-weight: 600;
}

/* Add any additional custom styles here */

    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">

    <!-- Total Counts Section -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Tasks</h5>
                    <p class="card-text display-4"><?php echo $total_tasks; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Courses</h5>
                    <p class="card-text display-4"><?php echo $total_courses; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Reminders</h5>
                    <p class="card-text display-4"><?php echo $total_reminders; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Based on Priority Levels and Due Date within Current Week -->
    <div class="mt-5">
        <h2>Tasks Based on Priority Levels</h2>
        <div class="row mt-4">
            <?php foreach (['High', 'Medium', 'Low'] as $priority_level) : ?>
                <div class="col-md-4">
                    <div class="card <?php echo strtolower('priority-' . $priority_level); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $priority_level; ?> Priority</h5>
                            <?php if (isset($priority_tasks[$priority_level])) : ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($priority_tasks[$priority_level] as $task) : ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($task['title']); ?></strong><br>
                                            Status: <?php echo htmlspecialchars($task['status']); ?><br>
                                            Due Date: <?php echo htmlspecialchars($task['due_date']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p class="card-text">No <?php echo strtolower($priority_level); ?> priority tasks due this week.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Reminders Section -->
    <div class="mt-5">
        <h2>Reminders</h2>
        <div class="row mt-4">
            <?php foreach ($reminders as $reminder) : ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($reminder['reminder_message']); ?></h5>
                            <p class="card-text">Reminder Date: <?php echo htmlspecialchars($reminder['reminder_date']); ?></p>
                            <p class="card-text">Reminder Time: <?php echo htmlspecialchars($reminder['reminder_time']); ?></p>
                            <a href="edit_reminder.php?reminder_id=<?php echo $reminder['reminder_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="view_reminders.php?delete=<?php echo $reminder['reminder_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this reminder?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="mt-5">
        <h2>Calendar</h2>
        <div id="calendar"></div>
    </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize FullCalendar
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: [
            // Add tasks to calendar
            <?php foreach ($priority_tasks as $priority_level => $tasks) : ?>
                <?php foreach ($tasks as $task) : ?>
                    {
                        title: '<?php echo addslashes($task['title']); ?>',
                        start: '<?php echo $task['due_date']; ?>',
                        backgroundColor: '<?php echo ($priority_level == 'High') ? "#f8d7da" : (($priority_level == 'Medium') ? "#ffeeba" : "#d4edda"); ?>',
                        borderColor: '<?php echo ($priority_level == 'High') ? "#f8d7da" : (($priority_level == 'Medium') ? "#ffeeba" : "#d4edda"); ?>'
                    },
                <?php endforeach; ?>
            <?php endforeach; ?>
            // Add study sessions to calendar
            <?php foreach ($sessions as $session) : ?>
                {
                    title: '<?php echo addslashes($session['course_name'] . ": " . $session['session_notes']); ?>',
                    start: '<?php echo $session['session_date'] . "T" . $session['start_time']; ?>',
                    end: '<?php echo $session['session_date'] . "T" . $session['end_time']; ?>',
                    backgroundColor: '#bde0fe',
                    borderColor: '#bde0fe'
                },
            <?php endforeach; ?>
        ],
        editable: false
    });
});
</script>
</body>
</html>
