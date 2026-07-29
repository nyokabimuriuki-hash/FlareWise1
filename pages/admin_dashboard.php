<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Only accessible to admins
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FlareWise</title>
    <link rel="stylesheet" href="../assets/css/app.css">
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
        <h1>Admin Dashboard</h1>

        <div class="cards">
            <div class="card">
                <h2>Manage Content</h2>
                <a href="admin_ingredients.php" class="cta-button" style="margin-top: 10px;">Manage Ingredients</a>
            </div>
        </div>

        <h2 class="section-title" style="margin-top: 30px;">User Management</h2>
        <div class="recent">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT id, fullname, email, role FROM users ORDER BY id DESC");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while($row = $result->fetch_assoc())
                    {
                        echo "<tr>
                            <td>".htmlspecialchars($row['id'])."</td>
                            <td><strong>".htmlspecialchars($row['fullname'])."</strong></td>
                            <td>".htmlspecialchars($row['email'])."</td>
                            <td>".htmlspecialchars($row['role'])."</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Firebase SDKs (compat) -->
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <script src="../assets/js/firebase-config.js"></script>

    <script>
        const auth = firebase.auth();

        // Redirect to login if not authenticated
        auth.onAuthStateChanged(user => {
            if (!user) {
                window.location = 'login.html';
            }
        });

        // Sign out handler
        document.getElementById('signout-link').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('../api/logout_session.php');
            await auth.signOut();
            // Redirect to main page, not login, as index.php handles routing
            window.location.href = '../index.php';
        });
    </script>

</body>
</html>
