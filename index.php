<?php
include('config.php');

session_start();

// Function to generate star icons based on rating
function generateStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $stars;
}

// Fetch all feedbacks with usernames
$sql = "SELECT f.*, u.username FROM Feedback f JOIN Users u ON f.user_id = u.id";
$feedbacks = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
    $result->free();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Learn Online</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .jumbotron h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .jumbotron p {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<?php
include('navbar.php');
?>

<div class="jumbotron text-center">
    <h1>Welcome to Student Task Planner</h1>
    <p>Organize and Manage Your Tasks Effectively</p>
    <a href="login.php" class="btn btn-primary btn-lg">Login to Get Started</a>
</div>


<div class="container mt-5">
    <h2>Recent Feedbacks</h2>
    <div class="row">
        <?php foreach ($feedbacks as $key => $feedback) : ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($feedback['username']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($feedback['feedback_text']); ?></p>
                        <p class="card-text">
                            Rating:
                            <?php echo generateStars($feedback['rating']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
            // Close row and start a new row every third card
            if (($key + 1) % 3 == 0) {
                echo '</div><div class="row">';
            }
            ?>
        <?php endforeach; ?>
    </div>
</div>

<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; 2024 Student Task Planner. All rights reserved.</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
