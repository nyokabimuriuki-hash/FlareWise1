<?php

session_start();
require_once 'db_connect.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['save'])) {
	$name=$_POST['medicine'];
	$dosage=$_POST['dosage'];
	$time=$_POST['time'];

	// Use prepared statements to prevent SQL injection
	$stmt = $conn->prepare("INSERT INTO medications(user_id, medicine_name, dosage, reminder_time) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("isss", $user_id, $name, $dosage, $time);
	$stmt->execute();
	$stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Medications - FlareWise</title>
	<link rel="stylesheet" href="dashboard.css">
</head>
<body>

	<nav>
		<div class="nav-container">
			<div class="nav-brand">FlareWise</div>
			<div class="nav-links">
				<a href="dashboard.php">Dashboard</a>
				<a href="symptoms.php">Symptoms</a>
				<a href="medication.php" class="active">Medication</a>
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
		<h1>Medication Reminders</h1>
    
		<div class="card">
			<h2>Add New Medication</h2>
			<form method="POST">
				<label>Medicine Name</label>
				<input type="text" name="medicine" placeholder="e.g., Hydrocortisone Cream" required>
        
				<label>Dosage</label>
				<input type="text" name="dosage" placeholder="e.g., 2 tablets, 1 application" required>
        
				<label>Reminder Time</label>
				<input type="time" name="time" required>
        
				<input type="submit" name="save" value="Add Medication">
			</form>
		</div>

		<hr>

		<h2 class="section-title">📋 Your Medications</h2>

		<div class="recent">
			<table>
				<thead>
					<tr>
						<th>Medicine Name</th>
						<th>Dosage</th>
						<th>Reminder Time</th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Use prepared statements for selecting data
					$stmt = $conn->prepare("SELECT medicine_name, dosage, reminder_time FROM medications WHERE user_id = ? ORDER BY reminder_time ASC");
					$stmt->bind_param("i", $user_id);
					$stmt->execute();
					$result = $stmt->get_result();

					while($row = $result->fetch_assoc())
					{
						echo "<tr>
							<td><strong>".htmlspecialchars($row['medicine_name'])."</strong></td>
							<td>".htmlspecialchars($row['dosage'])."</td>
							<td>".htmlspecialchars(date("g:i A", strtotime($row['reminder_time'])))."</td>
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
	<script src="firebase-config.js"></script>

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
			await fetch('logout_session.php');
			await auth.signOut();
			// Redirect to main page, not login, as index.php handles routing
			window.location.href = 'index.php';
		});
	</script>

</body>

</html>