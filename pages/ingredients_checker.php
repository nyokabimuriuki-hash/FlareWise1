<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// If user is not logged in via PHP session, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

$analysis_results = [];
if (isset($_POST['analyze_ingredients'])) {
    $ingredient_list_raw = trim($_POST['ingredient_list']);
    $ingredient_list_array = array_map('trim', explode(',', $ingredient_list_raw));
    $ingredient_list_array = array_filter($ingredient_list_array); // Remove empty entries

    if (!empty($ingredient_list_array)) {
        // Prepare a string for the IN clause
        $placeholders = implode(',', array_fill(0, count($ingredient_list_array), '?'));
        $types = str_repeat('s', count($ingredient_list_array));

        $stmt = $conn->prepare("SELECT ingredient_name, status, description FROM ingredients WHERE ingredient_name IN ($placeholders)");
        $stmt->bind_param($types, ...$ingredient_list_array);
        $stmt->execute();
        $result = $stmt->get_result();

        $found_ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $found_ingredients[strtolower($row['ingredient_name'])] = $row;
        }
        $stmt->close();

        foreach ($ingredient_list_array as $input_ingredient) {
            $lower_input = strtolower($input_ingredient);
            if (isset($found_ingredients[$lower_input])) {
                $analysis_results[] = $found_ingredients[$lower_input];
            } else {
                $analysis_results[] = ['ingredient_name' => $input_ingredient, 'status' => 'Unknown', 'description' => 'Not found in our database.'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredient Checker - FlareWise</title>
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
                <a href="ingredients_checker.php" class="active">Ingredients</a>
                <a href="profile.php">Profile</a>
                <a href="about.php">About Us</a>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="main">
        <h1>Skincare Ingredient Checker</h1>
        <p>Paste a list of ingredients (comma-separated) from your skincare products to check for potential irritants.</p>

        <div class="card">
            <h2>Analyze Ingredients</h2>
            <form method="POST">
                <label for="ingredient_list">Ingredient List (comma-separated)</label>
                <textarea id="ingredient_list" name="ingredient_list" rows="5" placeholder="e.g., Water, Glycerin, Alcohol Denat, Fragrance" required><?php echo htmlspecialchars($_POST['ingredient_list'] ?? ''); ?></textarea>
                <input type="submit" name="analyze_ingredients" value="Analyze">
            </form>
        </div>

        <?php if (!empty($analysis_results)): ?>
            <hr>
            <h2 class="section-title">Analysis Results</h2>
            <div class="recent">
                <table>
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis_results as $result): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($result['ingredient_name']); ?></strong></td>
                                <td style="color: <?php echo ($result['status'] == 'Avoid' ? '#f44336' : ($result['status'] == 'Safe' ? '#4caf50' : '#ff9800')); ?>;">
                                    <?php echo htmlspecialchars($result['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($result['description']); ?></td>
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