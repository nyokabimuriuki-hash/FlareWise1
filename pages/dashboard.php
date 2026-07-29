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

// Seven-day symptom trend: average of the four eczema symptoms for each day.
$trend = [];
$stmt_trend = $conn->prepare("SELECT symptom_date, ROUND(AVG((itching + redness + dryness + irritation) / 4), 1) AS average_severity FROM symptoms WHERE user_id = ? AND symptom_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY symptom_date ORDER BY symptom_date ASC");
$stmt_trend->bind_param("i", $user_id); $stmt_trend->execute();
$trend_result = $stmt_trend->get_result();
while ($row = $trend_result->fetch_assoc()) { $trend[$row['symptom_date']] = (float)$row['average_severity']; }
$stmt_trend->close();

// Reminders are supplied to the browser to trigger an alert at the saved time.
$reminders = [];
$stmt_reminders = $conn->prepare("SELECT medication_id, medicine_name, dosage, reminder_time FROM medications WHERE user_id = ? ORDER BY reminder_time ASC");
$stmt_reminders->bind_param("i", $user_id); $stmt_reminders->execute();
$reminder_result = $stmt_reminders->get_result();
while ($row = $reminder_result->fetch_assoc()) { $reminders[] = ['id' => (int)$row['medication_id'], 'name' => $row['medicine_name'], 'dosage' => $row['dosage'], 'time' => $row['reminder_time']]; }
$stmt_reminders->close();

if (isset($_POST['save_city'])) {
    $city = trim($_POST['city']);
    if (!empty($city)) {
        $stmt_city = $conn->prepare("INSERT INTO weather_preferences (user_id, city) VALUES (?, ?) ON DUPLICATE KEY UPDATE city = VALUES(city)");
        $stmt_city->bind_param("is", $user_id, $city);
        $stmt_city->execute();
        $stmt_city->close();
    }
}

// Fetch user's city
$user_city = null;
$stmt_get_city = $conn->prepare("SELECT city FROM weather_preferences WHERE user_id = ?");
$stmt_get_city->bind_param("i", $user_id);
$stmt_get_city->execute();
$result_city = $stmt_get_city->get_result();
if ($row = $result_city->fetch_assoc()) {
    $user_city = $row['city'];
}
$stmt_get_city->close();

$weatherAlert = null;
if ($user_city) {
    // Open-Meteo API doesn't require an API key and accepts coordinates. We will use geocoding API to get coords first.
    // Geocoding API: https://geocoding-api.open-meteo.com/v1/search?name=CITY
    $geo_url = "https://geocoding-api.open-meteo.com/v1/search?name=" . urlencode($user_city) . "&count=1&language=en&format=json";
    $geo_response = @file_get_contents($geo_url);
    if ($geo_response) {
        $geo_data = json_decode($geo_response, true);
        if (isset($geo_data['results'][0])) {
            $lat = $geo_data['results'][0]['latitude'];
            $lon = $geo_data['results'][0]['longitude'];

            // Weather API: https://api.open-meteo.com/v1/forecast?latitude=LAT&longitude=LON&current_weather=true&hourly=relativehumidity_2m
            $weather_url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m";
            $weather_response = @file_get_contents($weather_url);
            if ($weather_response) {
                $weather_data = json_decode($weather_response, true);
                if (isset($weather_data['current'])) {
                    $temp = $weather_data['current']['temperature_2m'];
                    $humidity = $weather_data['current']['relative_humidity_2m'];

                    $weatherRisk = ($temp > 30 && $humidity < 40) ? 'High (Dryness)' : 'Low';

                    if ($weatherRisk === 'High (Dryness)') {
                        $weatherAlert = "Alert: High temperature ({$temp}°C) and low humidity ({$humidity}%) in {$user_city} may increase dryness. Remember to moisturize!";
                    } else {
                        $weatherAlert = "Current conditions in {$user_city}: {$temp}°C, {$humidity}% humidity. Flare risk is Low.";
                    }
                }
            }
        }
    }

    if (!$weatherAlert) {
        $weatherAlert = "Could not fetch weather data for {$user_city} at this time.";
    }
}

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
                <a href="ingredients_checker.php">Ingredients</a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') echo '<a href="admin_dashboard.php">Admin</a>'; ?>
                <a href="symptoms.php">Symptoms</a>
                <a href="medication.php">Medication</a>
                <a href="upload.php">Images</a>
                <a href="profile.php">Profile</a>
                <a href="about.php">About Us</a>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn" href="javascript:void(0);">Sign Out</a>
            </div>
</div>
    </nav>

    <div class="main">
        <h1 id="welcome">Welcome, <?php echo $userName; ?></h1>

        <div class="card" style="margin-bottom: 20px;">
            <h2>Weather & Environmental Alerts</h2>
            <?php if ($weatherAlert): ?>
                <p class="notice <?php echo strpos($weatherAlert, 'Alert:') !== false ? 'warning' : ''; ?>">
                    <?php echo htmlspecialchars($weatherAlert); ?>
                </p>
            <?php endif; ?>
            <form method="POST" style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                <input type="text" name="city" placeholder="Enter your city" value="<?php echo htmlspecialchars($user_city ?? ''); ?>" required style="max-width: 300px; margin-top: 0;">
                <input type="submit" name="save_city" value="Set City" style="margin-top: 0; width: auto;">
            </form>
        </div>

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