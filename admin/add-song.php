<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Handle Add Song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_song'])) {
    $title = $_POST['title'];
    $composer = $_POST['composer'];
    $lyrics = $_POST['lyrics'];
    $category_id = $_POST['category_id'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $video_link = $_POST['video_link'];
    $cover_photo = '';

    if ($_FILES['cover_photo']['name']) {
        $target_dir = "../uploads/";
        $filename = basename($_FILES['cover_photo']['name']);
        $target_file = $target_dir . time() . "_" . $filename;
        move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target_file);
        $cover_photo = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO songs (title, composer, lyrics, cover_photo, video_link, category_id, is_public) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $title, $composer, $lyrics, $cover_photo, $video_link, $category_id, $is_public);
    $stmt->execute();
    $_SESSION['message'] = "Song added!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit Song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_song'])) {
    $id = $_POST['song_id'];
    $title = $_POST['title'];
    $composer = $_POST['composer'];
    $lyrics = $_POST['lyrics'];
    $category_id = $_POST['category_id'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $video_link = $_POST['video_link'];
    $cover_photo = $_POST['existing_cover_photo'];

    if ($_FILES['cover_photo']['name']) {
        $target_dir = "../uploads/";
        $filename = basename($_FILES['cover_photo']['name']);
        $target_file = $target_dir . time() . "_" . $filename;
        move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target_file);
        $cover_photo = $target_file;
    }

    $stmt = $conn->prepare("UPDATE songs SET title=?, composer=?, lyrics=?, cover_photo=?, video_link=?, category_id=?, is_public=? WHERE id=?");
    $stmt->bind_param("sssssisi", $title, $composer, $lyrics, $cover_photo, $video_link, $category_id, $is_public, $id);
    $stmt->execute();
    $_SESSION['message'] = "Song updated!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete Song
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM songs WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Song deleted!";
    } else {
        $_SESSION['message'] = "Error deleting song.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categories = $conn->query("SELECT * FROM categories");
$songs = $conn->query("SELECT s.*, c.name AS category FROM songs s LEFT JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Songs</title>
    <style>
        /* Basic Reset & Body */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        /* Container with sidebar + main */
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 100vh;
            box-sizing: border-box;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar nav a {
            display: block;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .sidebar nav a:hover {
            background-color: #575757;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
        }

        h2 {
            text-align: center;
        }

        .button {
            background: #4CAF50;
            color: white;
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            margin: 5px 0 15px 0;
            border-radius: 4px;
            font-size: 16px;
        }
        .button.edit { background: #2196F3; }
        .button.delete { background: #f44336; }
        a.button-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        a.button-link:hover {
            background: #0056b3;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background: #eee;
        }
        img { 
            width: 60px; 
            border-radius: 4px;
        }

        .message {
            background: #dff0d8;
            padding: 10px;
            border: 1px solid #3c763d;
            color: #3c763d;
            margin-bottom: 15px;
            border-radius: 5px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            position: relative;
            border-radius: 6px;
        }
        .close {
            position: absolute;
            top: 10px; right: 20px;
            font-size: 24px;
            cursor: pointer;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
            margin-top: 6px;
        }
        .checkbox-label {
            display: inline-flex;
            align-items: center;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            gap: 4px;
        }
        .checkbox-label input[type="checkbox"] {
            margin: 0;
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <aside class="sidebar">
        <h2>Admin Add Music</h2>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="add-song.php">Add Song</a>
            <a href="manage-category.php">Manage Categories</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <script>
                setTimeout(() => document.querySelector('.message')?.remove(), 3000);
            </script>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <h2>Add Songs</h2>

        <button class="button" onclick="openAddModal()">+ Add New Song</button>
        <!-- <a href="dashboard.php" class="button-link">← Back to Dashboard</a> -->

        <table>
            <thead>
                <tr>
                    <th>Title</th><th>Composer</th><th>Category</th><th>Cover</th><th>Video</th><th>Public</th><th>Date uploaded</th><th>Actions</th> 
                </tr>
            </thead>
            <tbody>
                <?php while ($song = $songs->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($song['title']) ?></td>
                    <td><?= htmlspecialchars($song['composer']) ?></td>
                    <td><?= htmlspecialchars($song['category']) ?></td>
                    <td><?php if ($song['cover_photo']): ?><img src="<?= htmlspecialchars($song['cover_photo']) ?>" alt="Cover Photo"><?php endif; ?></td>
                    <td><a href="<?= htmlspecialchars($song['video_link']) ?>" target="_blank">Link</a></td>
                    <td><?= $song['is_public'] ? 'Yes' : 'No' ?></td>
                    <td><?= date('Y-m-d', strtotime($song['created_at'])) ?></td>
                    <td>
                        <button class="button edit" onclick='openEditModal(<?= json_encode($song) ?>)'>Edit</button>
                        <a class="button delete" href="?delete=<?= $song['id'] ?>" onclick="return confirm('Delete this song?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Add Song Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeAddModal()">&times;</span>
                <h3>Add New Song</h3>
                <form method="POST" enctype="multipart/form-data">
                    <label>Title</label>
                    <input type="text" name="title" required>
                    
                    <label>Composer</label>
                    <input type="text" name="composer" required>
                    
                    <label>Lyrics</label>
                    <textarea name="lyrics" rows="6" required></textarea>
                    
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Cover Photo</label>
                    <input type="file" name="cover_photo" accept="image/*">

                    <label>Video Link (YouTube etc.)</label>
                    <input type="url" name="video_link" placeholder="https://">

                    <label class="checkbox-label"><input type="checkbox" name="is_public"> Public</label>

                    <button type="submit" name="add_song" class="button">Add Song</button>
                </form>
            </div>
        </div>

        <!-- Edit Song Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h3>Edit Song</h3>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="song_id" id="edit_song_id">
                    <label>Title</label>
                    <input type="text" name="title" id="edit_title" required>

                    <label>Composer</label>
                    <input type="text" name="composer" id="edit_composer" required>

                    <label>Lyrics</label>
                    <textarea name="lyrics" id="edit_lyrics" rows="6" required></textarea>

                    <label>Category</label>
                    <select name="category_id" id="edit_category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Cover Photo (leave empty to keep current)</label>
                    <input type="file" name="cover_photo" accept="image/*">
                    <input type="hidden" name="existing_cover_photo" id="edit_existing_cover">

                    <label>Video Link (YouTube etc.)</label>
                    <input type="url" name="video_link" id="edit_video_link" placeholder="https://">

                    <label class="checkbox-label"><input type="checkbox" name="is_public" id="edit_is_public"> Public</label>

                    <button type="submit" name="edit_song" class="button">Update Song</button>
                </form>
            </div>
        </div>

    </main>
</div>

<script>
    // Modal open/close
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');

    function openAddModal() {
        addModal.style.display = 'block';
    }
    function closeAddModal() {
        addModal.style.display = 'none';
    }

    function openEditModal(song) {
        document.getElementById('edit_song_id').value = song.id;
        document.getElementById('edit_title').value = song.title;
        document.getElementById('edit_composer').value = song.composer;
        document.getElementById('edit_lyrics').value = song.lyrics;
        document.getElementById('edit_category_id').value = song.category_id;
        document.getElementById('edit_existing_cover').value = song.cover_photo || '';
        document.getElementById('edit_video_link').value = song.video_link || '';
        document.getElementById('edit_is_public').checked = song.is_public == 1 ? true : false;

        editModal.style.display = 'block';
    }
    function closeEditModal() {
        editModal.style.display = 'none';
    }

    // Close modals if clicked outside content
    window.onclick = function(event) {
        if (event.target == addModal) closeAddModal();
        if (event.target == editModal) closeEditModal();
    }
</script>

</body>
</html>
