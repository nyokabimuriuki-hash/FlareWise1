<?php
// This script is intended to be run periodically (e.g., via cron job)
// to fetch weather data for all users' preferred cities and store it.

require_once __DIR__ . '/../config/database.php';

// Replace with your actual API key and chosen weather API endpoint
define('WEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY'); // Get this from OpenWeatherMap or similar
define('WEATHER_API_URL', 'http://api.openweathermap.org/data/2.5/weather');

// Fetch all unique cities from user preferences
$cities_stmt = $conn->prepare("SELECT DISTINCT city FROM weather_preferences");
$cities_stmt->execute();
$cities_result = $cities_stmt->get_result();
$cities = [];
while ($row = $cities_result->fetch_assoc()) {
    $cities[] = $row['city'];
}
$cities_stmt->close();

foreach ($cities as $city) {
    $api_url = WEATHER_API_URL . "?q=" . urlencode($city) . "&appid=" . WEATHER_API_KEY . "&units=metric"; // Use metric for Celsius

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $weather_data = json_decode($response, true);

    if ($weather_data && $weather_data['cod'] == 200) {
        $temperature = $weather_data['main']['temp'];
        $humidity = $weather_data['main']['humidity'];

        // Insert into weather_history
        $insert_stmt = $conn->prepare("INSERT INTO weather_history (city, temperature, humidity, flare_risk) VALUES (?, ?, ?, ?)");
        // For simplicity, flare_risk is determined on the dashboard. Here we can store a default or null.
        $default_flare_risk = 'Unknown';
        $insert_stmt->bind_param("sdds", $city, $temperature, $humidity, $default_flare_risk);
        $insert_stmt->execute();
        $insert_stmt->close();
        echo "Fetched weather for $city: Temp=$temperature, Humidity=$humidity\n";
    } else {
        error_log("Failed to fetch weather for $city: " . ($weather_data['message'] ?? 'Unknown error'));
        echo "Failed to fetch weather for $city\n";
    }
}

$conn->close();
?>