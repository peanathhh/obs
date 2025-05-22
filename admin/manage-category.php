<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "✅ Category added successfully.";
    }
    header("Location: manage_category.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    $_SESSION['message'] = "🗑 Category deleted successfully.";
    header("Location: manage_category.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $newName = trim($_POST['new_name']);
    if ($newName !== '') {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "✏️ Category updated successfully.";
    }
    header("Location: manage_category.php");
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6fa;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
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
        /* Main content */
        .container {
            flex-grow: 1;
            padding: 30px 40px;
            background: #fff;
            border-radius: 12px 0 0 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 20px auto 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 26px;
        }

        .message {
            background: #e6ffed;
            border-left: 6px solid #4CAF50;
            padding: 15px;
            color: #256029;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        form.add-form {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            justify-content: center;
        }

        input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            padding: 10px 16px;
            background-color:rgb(16, 59, 124);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #5941c2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 15px;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eaeaea;
        }

        th {
            background-color: #6c5ce7;
            color: white;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .actions a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            color: #333;
            font-weight: 600;
        }

        .back-link:hover {
            color: #6c5ce7;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                justify-content: space-around;
                padding: 15px 0;
                border-radius: 0 0 12px 12px;
            }
            .sidebar h2 {
                display: none;
            }
            .container {
                max-width: 100%;
                margin: 20px 10px 40px;
                border-radius: 12px;
            }
            form.add-form {
                flex-direction: column;
                align-items: stretch;
            }
            input[type="text"], button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <h2>Admin Add Category</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="add-song.php">Add Songs</a>
        <a href="manage_category.php" class="active">Manage Categories</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>

    <div class="container">
        <h1>Manage Categories</h1>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Add category form -->
        <form method="POST" class="add-form">
            <input type="text" name="name" placeholder="New category name" required>
            <input type="hidden" name="action" value="add">
            <button type="submit">Add Category</button>
        </form>

        <!-- Category Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <form method="POST" style=" align-items:center;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="text" name="new_name" value="<?= htmlspecialchars($row['name']) ?>" required>
                            <input type="hidden" name="action" value="edit">
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td class="actions">
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
