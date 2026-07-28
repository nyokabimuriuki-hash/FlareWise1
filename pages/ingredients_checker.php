<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

$results = [];
$warning_message = '';

if (isset($_POST['check'])) {
    $input = $_POST['ingredients'];
    $list = array_filter(array_map('trim', explode(',', $input)));

    if (!empty($list)) {
        // Fetch latest symptom severity
        $stmt_symptom = $conn->prepare("SELECT itching, redness, dryness, irritation FROM symptoms WHERE user_id = ? ORDER BY symptom_date DESC LIMIT 1");
        $stmt_symptom->bind_param("i", $user_id);
        $stmt_symptom->execute();
        $res = $stmt_symptom->get_result();

        $high_severity = false;
        if ($row = $res->fetch_assoc()) {
            $avg = ($row['itching'] + $row['redness'] + $row['dryness'] + $row['irritation']) / 4;
            if ($avg > 5) {
                $high_severity = true;
            }
        }
        $stmt_symptom->close();

        // Check ingredients
        $stmt = $conn->prepare("SELECT ingredient_name, status, description FROM ingredients WHERE LOWER(ingredient_name) = LOWER(?)");
        $has_avoid = false;
        foreach ($list as $ing) {
            $stmt->bind_param("s", $ing);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $results[] = $row;
                if ($row['status'] === 'Avoid') {
                    $has_avoid = true;
                }
            } else {
                $results[] = ['ingredient_name' => $ing, 'status' => 'Unknown', 'description' => 'Not found in database.'];
            }
        }
        $stmt->close();

        if ($high_severity && $has_avoid) {
            $warning_message = "Based on your recent high-severity symptoms, please be extra cautious with these 'Avoid' ingredients!";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredients Checker - FlareWise</title>
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
                <a href="ingredients_checker.php" class="active">Ingredients</a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_dashboard.php">Admin</a>
                <?php endif; ?>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="main">
        <h1>Ingredients Checker</h1>

        <div class="card">
            <h2>Check Skincare Product</h2>
            <p>Paste a comma-separated list of ingredients to analyze them against known eczema triggers.</p>
            <form method="POST">
                <textarea name="ingredients" rows="5" placeholder="e.g., Water, Glycerin, Fragrance, Ceramide..." required></textarea>
                <input type="submit" name="check" value="Analyze Ingredients">
            </form>
        </div>

        <?php if (isset($_POST['check'])): ?>
            <hr>
            <h2 class="section-title">Analysis Results</h2>
            <?php if ($warning_message): ?>
                <p class="notice warning">⚠️ <?php echo htmlspecialchars($warning_message); ?></p>
            <?php endif; ?>
            <div class="recent">
                <table>
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th>Status</th>
                            <th>Analysis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($r['ingredient_name']); ?></strong></td>
                                <td style="color: <?php echo $r['status'] === 'Avoid' ? '#f44336' : ($r['status'] === 'Safe' ? '#4caf50' : 'var(--muted)'); ?>">
                                    <strong><?php echo htmlspecialchars($r['status']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($r['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Firebase SDKs (compat) -->
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