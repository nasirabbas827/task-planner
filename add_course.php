<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_course"])) {
    $course_name = trim($_POST["course_name"]);
    $course_description = trim($_POST["course_description"]);
    $user_id = $_SESSION["id"];

    // Validate input
    if (empty($course_name)) {
        $error_message = "Course name is required.";
    } else {
        // Insert course into database
        $sql = "INSERT INTO Courses (user_id, course_name, course_description) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $course_name, $course_description);
            if ($stmt->execute()) {
                $success_message = "Course created successfully.";
            } else {
                $error_message = "Error: Could not execute the query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the query.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HomePage</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Create a New Course</h2>
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
            <label for="course_name">Course Name:</label>
            <input type="text" class="form-control" id="course_name" name="course_name">
        </div>
        <div class="form-group">
            <label for="course_description">Course Description:</label>
            <textarea class="form-control" id="course_description" name="course_description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="create_course">Create Course</button>
        <a class="btn btn-outline-dark" href="view_courses.php">View Courses</a>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
