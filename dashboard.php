<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FlareWise</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <nav>
        <div class="nav-container">
            <div class="nav-brand">FlareWise</div>
            <div class="nav-links">
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="symptoms.php">🩹 Symptoms</a>
                <a href="medication.php">💊 Medication</a>
                <a href="upload.php">📷 Images</a>
                <a href="profile.php">👤 Profile</a>
                <a href="about.php">ℹ️ About Us</a>
            </div>
            <div class="nav-auth">
                <a id="signout-link" class="signout-btn">🚪 Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="main">
        <h1 id="welcome">Welcome to FlareWise</h1>

        <div class="cards">
            <div class="card">
                <h2>Today's Symptoms</h2>
                <h1 id="symptoms-count">0</h1>
                <p>Tracked symptoms today</p>
            </div>

            <div class="card">
                <h2>Active Medications</h2>
                <h1 id="med-count">0</h1>
                <p>Medications to take</p>
            </div>

            <div class="card">
                <h2>Flare Risk</h2>
                <h1 style="color: #4caf50;">Low</h1>
                <p>Good conditions today</p>
            </div>

            <div class="card">
                <h2>Skin Health</h2>
                <h1 style="color: #2196f3;">Good</h1>
                <p>Keep up the routine</p>
            </div>
        </div>

        <div class="recent">
            <h2>📊 Your Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Today</td>
                        <td>Logged into FlareWise</td>
                        <td><span style="color: #4caf50;">✓ Active</span></td>
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
            window.location = 'login.html';
        });
    </script>

</body>
</html>