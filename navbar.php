<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="home.php">Task Planner</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <?php
            if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
                echo '<li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="update_profile.php">Update Profile</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="add_course.php">Courses</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="add_task.php">Tasks</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="add_reminder.php">Reminder</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="add_session.php">Study Sessions</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="add_feedback.php">Feedbacks</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="accessibility.php">For Disables</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>';
            }
            ?>
        </ul>
    </div>
</nav>
