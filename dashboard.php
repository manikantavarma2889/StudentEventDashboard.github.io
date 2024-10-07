<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'db/connection.php';

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT name, photo FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch events for the logged-in user
$stmt = $pdo->prepare('SELECT title, description, date FROM events WHERE user_id = ?');
$stmt->execute([$user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handling profile photo update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check !== false) {
        $upload_ok = 1;
    } else {
        $upload_ok = 0;
    }

    // Allow certain file formats (JPG, PNG, JPEG)
    if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg") {
        $upload_ok = 0;
    }

    // If everything is ok, try to upload file
    if ($upload_ok == 1) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare('UPDATE users SET photo = ? WHERE id = ?');
            $stmt->execute([$target_file, $user_id]);
            $user['photo'] = $target_file; // Update user photo
        }
    }
}

// Set default photo if none exists
if (empty($user['photo'])) {
    $user['photo'] = 'uploads/default.png'; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: black; 
            width: 100%;
            box-sizing: border-box;
        }
        .logo {
            height: 40px;
        }
        .user-info {
            display: flex;
            align-items: center;
            color: white;
        }
        .user-info img {
            border-radius: 50%;
            height: 40px;
            width: 40px;
            margin-right: 10px;
        }
        .logout {
            font-size: 16px;
            color: white;
        }
        .main-content {
            display: flex;
            flex-grow: 1;
            width: 100%;
        }
        .sidebar {
            width: 200px;
            background-color: #333;
            color: white;
            padding: 15px;
            height: 100vh;
            box-sizing: border-box;
            position: relative;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .container {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="book-1 (1).png" alt="Logo" class="logo"> 
        <div class="user-info">
            <form method="POST" enctype="multipart/form-data">
                <label for="photo">
                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="User Photo">
                </label>
                <input type="file" name="photo" id="photo" style="display:none;" onchange="this.form.submit()">
            </form>
            <div class="logout">
                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="sidebar">
            <h3>Navigation</h3>
            <a href="dashboard.php">Home</a>
            <a href="events_done.php">Events Done</a>
            <a href="about_us.php">About Us</a>
            <a href="create_event.php">Create New Event</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="container">
            <h2>Your Events</h2>
            <?php if (!empty($events)): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                        <td><?php echo htmlspecialchars($event['date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
