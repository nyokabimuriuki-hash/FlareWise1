<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FlareWise</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <div class="sidebar">
        <h2>FlareWise</h2>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="symptoms.php">🩹 Symptoms</a>
            <a href="medication.php">💊 Medication</a>
            <a href="upload.php">📷 Images</a>
            <a href="profile.php">👤 Profile</a>
            <a id="signout-link" href="#">🚪 Sign Out</a>
        </nav>
    </div>

    <div class="main">
        <h1 id="welcome">Welcome,</h1>

        <div class="cards">
            <div class="card">
                <h2>Today's Symptoms</h2>
                <h1 id="symptoms-count">0</h1>
                <p>No symptoms recorded today.</p>
            </div>

            <div class="card">
                <h2>Medications</h2>
                <h1 id="med-count">0</h1>
                <p>No reminders today.</p>
            </div>

            <div class="card">
                <h2>Weather Status</h2>
                <h1>24°C</h1>
                <p>Humidity 62%</p>
            </div>

            <div class="card">
                <h2>Flare Risk</h2>
                <h1>Low</h1>
                <p>Good weather today.</p>
            </div>
        </div>

        <div class="recent">
            <h2>Recent Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Today</td>
                        <td>Logged into FlareWise</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Firebase SDKs (compat) -->
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <script src="firebase-config.js"></script>

    <script>
        const auth = firebase.auth();

        // Redirect to login if not authenticated
        auth.onAuthStateChanged(user => {
            if (!user) {
                window.location = 'login.html';
                return;
            }
            // Show user name (displayName or email)
            const name = user.displayName || user.email;
            document.getElementById('welcome').textContent = `Welcome, ${name}`;
        });

        // Sign out handler
        document.getElementById('signout-link').addEventListener('click', async (e) => {
            e.preventDefault();
            await auth.signOut();
            window.location = 'logout.php';
        });
    </script>

</body>
</html>