<?php
include '../includes/auth.php';
include '../includes/db.php';

// Fetch counts
$totalSongs = $conn->query("SELECT COUNT(*) as total FROM songs")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f1f3f5;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: #f8f9fa;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .main-content {
            margin-left: 240px;
            padding: 40px;
        }

        .main-content h2 {
            margin-bottom: 30px;
        }

        .dashboard-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 20px;
            width: 200px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .card p {
            font-size: 28px;
            margin-top: 10px;
            color: #007bff;
        }

        .card a {
            text-decoration: none;
            color: inherit;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add-song.php"> Add Song</a>
    <a href="manage-category.php"> Manage Category</a>
    <a href="../logout.php"> Logout</a>
</div>

<div class="main-content">
    <h2>Welcome to the Admin Dashboard</h2>

    <div class="dashboard-cards">
        <div class="card">
            <a href="add-song.php">
                <h3>Total Songs</h3>
                <p><?= $totalSongs ?></p>
            </a>
        </div>
        <div class="card">
            <a href="manage-category.php">
                <h3>Total Categories</h3>
                <p><?= $totalCategories ?></p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
