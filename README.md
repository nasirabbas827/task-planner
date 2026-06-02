# task_planner

A lightweight PHP web application for managing courses, sessions, tasks, and reminders. It provides a clean interface for students and educators to organize their academic workload, track progress, and receive timely notifications.

---

## Overview

`task_planner` offers a simple yet powerful solution to:

- Create and edit **courses**, **sessions**, **tasks**, and **reminders**.
- View organized lists of each entity with filtering options.
- Manage user accounts (registration, login, profile updates).
- Maintain accessibility standards through `accessibility.php`.

All core functionality is implemented in pure PHP with a MySQL database backend.

---

## Features

| ✅ | Feature |
|---|---|
| 📚 | **Course Management** – add, edit, and view courses. |
| 📅 | **Session Scheduling** – create sessions linked to courses. |
| ✅ | **Task Tracking** – add tasks, set due dates, and mark completion. |
| ⏰ | **Reminders** – schedule reminders for tasks or sessions. |
| 👤 | **User Authentication** – secure login, registration, and profile updates. |
| 🖥️ | **Responsive UI** – clean layout with `css/style.css`. |
| ♿ | **Accessibility** – ARIA‑enhanced pages via `accessibility.php`. |
| 🔧 | **Modular Codebase** – each operation has its own PHP script for easy maintenance. |

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL (see `Database/task_db.sql`) |
| Front‑end | HTML5, CSS3 |
| Styling | Custom CSS (`css/style.css`) |
| Server | Apache / Nginx (any LAMP stack) |

---

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/task_planner.git
   cd task_planner
   ```

2. **Create a MySQL database**

   ```sql
   CREATE DATABASE task_planner;
   ```

3. **Import the schema**

   ```bash
   mysql -u your_user -p task_planner < Database/task_db.sql
   ```

4. **Configure the application**

   Edit `config.php` and set your database credentials:

   ```php
   <?php
   // config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'task_planner');
   define('DB_USER', 'YOUR_DB_USER');
   define('DB_PASS', 'YOUR_DB_PASSWORD');
   ?>
   ```

5. **Set up the web server**

   - Place the project folder inside your web root (e.g., `htdocs` or `www`).
   - Ensure PHP is enabled and the `public` directory is accessible.
   - Optionally configure virtual hosts for a cleaner URL.

6. **Secure the installation**

   - Set appropriate file permissions (`chmod 644` for files, `chmod 755` for directories).
   - Disable directory listing on the server.

---

## Usage

1. **Access the app**

   Open a browser and navigate to `http://localhost/task_planner/` (or your domain).

2. **Create an account**

   - Click **Register** and fill in the required fields.
   - After registration, log in with your credentials.

3. **Manage your data**

   - **Courses** – `add_course.php`, `edit_course.php`, `view_courses.php`
   - **Sessions** – `add_session.php`, `edit_session.php`, `view_sessions.php`
   - **Tasks** – `add_task.php`, `edit_task.php`, `view_tasks.php`
   - **Reminders** – `add_reminder.php`, `edit_reminder.php`, `view_reminders.php