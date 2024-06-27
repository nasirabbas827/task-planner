<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Initialize variables
$feedback_text = "";
$rating = "";
$feedback_text_err = "";
$rating_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate feedback text
    if (empty(trim($_POST["feedback_text"]))) {
        $feedback_text_err = "Please enter your feedback.";
    } else {
        $feedback_text = trim($_POST["feedback_text"]);
    }

    // Validate rating
    if (empty(trim($_POST["rating"]))) {
        $rating_err = "Please rate your experience.";
    } else {
        $rating = trim($_POST["rating"]);
        if ($rating < 1 || $rating > 5) {
            $rating_err = "Rating must be between 1 and 5.";
        }
    }

    // Insert feedback into database if no errors
    if (empty($feedback_text_err) && empty($rating_err)) {
        $sql = "INSERT INTO Feedback (user_id, feedback_text, rating) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $feedback_text, $rating);
            if ($stmt->execute()) {
                $success_message = "Feedback submitted successfully.";
                $feedback_text = $rating = ""; // Clear form fields after successful submission
            } else {
                $error_message = "Error: Could not execute the insert query.";
            }
            $stmt->close();
        } else {
            $error_message = "Error: Could not prepare the insert query.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Feedback</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Add Feedback</h2>
    <?php 
    if (isset($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (isset($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="feedback_text">Feedback:</label>
            <textarea class="form-control" id="feedback_text" name="feedback_text"><?php echo htmlspecialchars($feedback_text); ?></textarea>
            <span class="text-danger"><?php echo $feedback_text_err; ?></span>
        </div>
        <div class="form-group">
            <label for="rating">Rating:</label>
            <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($rating); ?>">
            <span class="text-danger"><?php echo $rating_err; ?></span>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
