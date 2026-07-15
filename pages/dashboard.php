<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// If user is not logged in via PHP session, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch user's name from session to personalize the welcome message
$userName = htmlspecialchars($_SESSION['user_fullname'] ?? 'User');

// --- Fetch data for dashboard cards ---

// Count symptoms logged today
$stmt_symptoms_count = $conn->prepare("SELECT COUNT(*) as count FROM symptoms WHERE user_id = ? AND symptom_date = CURDATE()");
$stmt_symptoms_count->bind_param("i", $user_id);
$stmt_symptoms_count->execute();
$result_symptoms_count = $stmt_symptoms_count->get_result();
$today_symptoms_count = $result_symptoms_count->fetch_assoc()['count'] ?? 0;
$stmt_symptoms_count->close();

// Count total active medications
$stmt_med_count = $conn->prepare("SELECT COUNT(*) as count FROM medications WHERE user_id = ?");
$stmt_med_count->bind_param("i", $user_id);
$stmt_med_count->execute();
$result_med_count = $stmt_med_count->get_result();
$med_count = $result_med_count->fetch_assoc()['count'] ?? 0;
$stmt_med_count->close();

// Fetch latest symptom for flare risk calculation
$flare_risk = "Good";
$flare_color = "#4caf50"; // green
$flare_message = "No recent symptoms logged.";

// Assuming `symptom_id` is the auto-incrementing primary key for the symptoms table
$stmt_latest_symptom = $conn->prepare("SELECT itching, redness, dryness, irritation FROM symptoms WHERE user_id = ? ORDER BY symptom_date DESC, symptom_id DESC LIMIT 1");
$stmt_latest_symptom->bind_param("i", $user_id);
$stmt_latest_symptom->execute();
$result_latest_symptom = $stmt_latest_symptom->get_result();

if ($latest_symptom = $result_latest_symptom->fetch_assoc()) {
    $total_severity = $latest_symptom['itching'] + $latest_symptom['redness'] + $latest_symptom['dryness'] + $latest_symptom['irritation'];
    // Average severity on a scale of 1-10
    $avg_severity = $total_severity / 4;

    if ($avg_severity > 6) {
        $flare_risk = "High";
        $flare_color = "#f44336"; // red
        $flare_message = "High severity symptoms logged.";
    } elseif ($avg_severity > 3) {
        $flare_risk = "Moderate";
        $flare_color = "#ff9800"; // orange
        $flare_message = "Moderate symptoms logged.";
    } else {
        $flare_message = "Symptoms appear to be mild.";
    }
}
$stmt_latest_symptom->close();

// Fetch latest image for Skin Health card
$latest_image = null;
// Assuming `image_id` is the auto-incrementing primary key for the skin_images table
$stmt_image = $conn->prepare("SELECT image_name FROM skin_images WHERE user_id = ? ORDER BY image_id DESC LIMIT 1");
$stmt_image->bind_param("i", $user_id);
$stmt_image->execute();
$result_image = $stmt_image->get_result();
if ($row = $result_image->fetch_assoc()) {
    $latest_image = '../uploads/' . htmlspecialchars($row['image_name']);
}
$stmt_image->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FlareWise</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <style>
        .latest-image-container {
            width: 100%;
            height: 120px; /* Or other fixed height */
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #e0eafc;
        }
        .latest-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card h2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <div class="nav-brand">FlareWise</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="symptoms.php">Symptoms</a>
                <a href="medication.php">Medication</a>
                <a href="upload.php">Images</a>
                <a href="profile.php">Profile</a>
                <a href="about.php">About Us</a>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="main">
        <h1 id="welcome">Welcome, <?php echo $userName; ?></h1>

        <div class="cards">
            <div class="card">
                <h2>Today's Symptoms</h2>
                <h1><?php echo $today_symptoms_count; ?></h1>
                <p>entries logged today</p>
            </div>

            <div class="card">
                <h2>Active Medications</h2>
                <h1><?php echo $med_count; ?></h1>
                <p>medications in your log</p>
            </div>

            <div class="card">
                <h2>Flare Risk</h2>
                <h1 style="color: <?php echo $flare_color; ?>;"><?php echo $flare_risk; ?></h1>
                <p><?php echo $flare_message; ?></p>
            </div>

            <div class="card">
                <h2>Skin Health</h2>
                <?php if ($latest_image): ?>
                    <div class="latest-image-container">
                        <a href="<?php echo $latest_image; ?>" target="_blank" title="View full image">
                            <img src="<?php echo $latest_image; ?>" alt="Latest skin image">
                        </a>
                    </div>
                    <p>Latest photo uploaded.</p>
                <?php else: ?>
                    <h1 style="color: #2196f3; font-size: 24px; margin-top: 20px;">No Images</h1>
                    <p>Upload a photo to track progress.</p>
                <?php endif; ?>
            </div>
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
                // This is a fallback check. If the PHP session is valid but Firebase is not,
                // it will redirect to login.
                window.location = 'login.html';
            }
        });

        // Sign out handler
        document.getElementById('signout-link').addEventListener('click', async (e) => {
            e.preventDefault();
            // First, destroy the PHP session
            await fetch('../api/logout_session.php');
            // Then, sign out from Firebase
            await auth.signOut();
            // Finally, redirect to the main page
            window.location.href = '../index.php';
        });
    </script>

</body>
</html>
