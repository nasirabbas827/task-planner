<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Handle deletion of a course
if (isset($_GET["delete"]) && !empty(trim($_GET["delete"]))) {
    $course_id = trim($_GET["delete"]);

    $sql = "DELETE FROM Courses WHERE course_id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $course_id, $user_id);
        if ($stmt->execute()) {
            $delete_message = "Course deleted successfully.";
        } else {
            $delete_error = "Error: Could not execute the delete query.";
        }
        $stmt->close();
    } else {
        $delete_error = "Error: Could not prepare the delete query.";
    }
}

// Fetch user's courses
$sql = "SELECT * FROM Courses WHERE user_id = ?";
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
    <title>My Courses</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Courses</h2>
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
                <th>Course Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course) : ?>
            <tr>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                <td>
                    <a href="edit_course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="view_courses.php?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
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
