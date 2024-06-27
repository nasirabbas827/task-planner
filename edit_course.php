<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$course_id = isset($_GET["course_id"]) ? trim($_GET["course_id"]) : "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_course"])) {
    $course_name = trim($_POST["course_name"]);
    $course_description = trim($_POST["course_description"]);

    // Validate input
    if (empty($course_name)) {
        $error_message = "Course name is required.";
    } else {
        // Update course in database
        $sql = "UPDATE Courses SET course_name = ?, course_description = ?, updated_at = NOW() WHERE course_id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $course_name, $course_description, $course_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Course updated successfully.";
            } else {
                $error_message = "Error: Could not execute the update query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the update query.";
        }
    }
}

// Fetch the course details
$sql = "SELECT * FROM Courses WHERE course_id = ? AND user_id = ?";
$course = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $course_id, $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $course = $row;
        } else {
            $error_message = "Error: Course not found.";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Edit Course</h2>
    <?php 
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <?php if ($course): ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?course_id=" . $course_id; ?>" method="post">
        <div class="form-group">
            <label for="course_name">Course Name:</label>
            <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>">
        </div>
        <div class="form-group">
            <label for="course_description">Course Description:</label>
            <textarea class="form-control" id="course_description" name="course_description"><?php echo htmlspecialchars($course['course_description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="update_course">Update Course</button>
    </form>
    <?php else: ?>
    <p class="text-danger">Course not found.</p>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
