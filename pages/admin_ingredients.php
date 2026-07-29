<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['add_ingredient'])) {
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $desc = trim($_POST['description']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO ingredients (ingredient_name, status, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $status, $desc);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_POST['update_ingredient'])) {
    $id = $_POST['ingredient_id'];
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $desc = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE ingredients SET ingredient_name = ?, status = ?, description = ? WHERE ingredient_id = ?");
    $stmt->bind_param("sssi", $name, $status, $desc, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['delete_ingredient'])) {
    $id = $_POST['ingredient_id'];
    $stmt = $conn->prepare("DELETE FROM ingredients WHERE ingredient_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ingredients - FlareWise</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <style>
        .inline-form { display: flex; gap: 8px; align-items: center; }
        .inline-form input[type="text"] { margin: 0; flex: 1; padding: 8px; }
        .inline-form select { margin: 0; width: 100px; padding: 8px; }
        .inline-form button { margin: 0; padding: 8px 12px; font-size: 0.8rem; }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <div class="nav-brand">FlareWise</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="symptoms.php">Symptoms</a>
                <a href="medication.php">Medication</a>
                <a href="upload.php">Images</a>
                <a href="profile.php">Profile</a>
                <a href="about.php">About Us</a>
                <a href="ingredients_checker.php">Ingredients</a>
                <a href="admin_dashboard.php" class="active">Admin</a>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn" href="javascript:void(0);">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="main">
        <h1>Manage Ingredients</h1>
        <a href="admin_dashboard.php" style="display:inline-block; margin-bottom:20px;">&larr; Back to Admin Dashboard</a>

        <div class="card">
            <h2>Add New Ingredient</h2>
            <form method="POST">
                <label>Ingredient Name</label>
                <input type="text" name="name" required>

                <label>Status</label>
                <select name="status">
                    <option value="Safe">Safe</option>
                    <option value="Avoid">Avoid</option>
                    <option value="Caution">Caution</option>
                </select>

                <label>Description</label>
                <input type="text" name="description">

                <input type="submit" name="add_ingredient" value="Add Ingredient">
            </form>
        </div>

        <hr>

        <h2 class="section-title">Ingredients Database</h2>
        <div class="recent">
            <table>
                <thead>
                    <tr>
                        <th style="width:25%">Name</th>
                        <th style="width:15%">Status</th>
                        <th style="width:40%">Description</th>
                        <th style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM ingredients ORDER BY ingredient_name ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while($row = $result->fetch_assoc())
                    {
                        echo "<tr>
                            <form method='POST'>
                                <input type='hidden' name='ingredient_id' value='".htmlspecialchars($row['ingredient_id'])."'>
                                <td><input type='text' name='name' value='".htmlspecialchars($row['ingredient_name'])."' required style='padding:6px;'></td>
                                <td>
                                    <select name='status' style='padding:6px;'>
                                        <option value='Safe' ".($row['status']=='Safe'?'selected':'').">Safe</option>
                                        <option value='Avoid' ".($row['status']=='Avoid'?'selected':'').">Avoid</option>
                                        <option value='Caution' ".($row['status']=='Caution'?'selected':'').">Caution</option>
                                    </select>
                                </td>
                                <td><input type='text' name='description' value='".htmlspecialchars($row['description'])."' style='padding:6px;'></td>
                                <td>
                                    <div style='display:flex; gap:5px;'>
                                        <button type='submit' name='update_ingredient' style='margin:0; padding:6px 10px; font-size:0.8rem;'>Update</button>
                                        <button type='submit' name='delete_ingredient' class='btn-danger' style='margin:0; padding:6px 10px; font-size:0.8rem;' onclick='return confirm(\"Delete this ingredient?\");'>Delete</button>
                                    </div>
                                </td>
                            </form>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <script src="../assets/js/firebase-config.js"></script>
    <script>
        const auth = firebase.auth();
        auth.onAuthStateChanged(user => { if (!user) window.location = 'login.html'; });
        document.getElementById('signout-link').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('../api/logout_session.php');
            await auth.signOut();
            window.location.href = '../index.php';
        });
    </script>
</body>
</html>