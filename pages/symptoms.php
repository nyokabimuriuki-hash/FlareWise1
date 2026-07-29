<?php

session_start();
require_once __DIR__ . '/../config/database.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['save'])) {
	$itching=$_POST['itching'];
	$redness=$_POST['redness'];
	$dryness=$_POST['dryness'];
	$irritation=$_POST['irritation'];
	$notes=$_POST['notes'];
	$date=$_POST['date'];

	// Use prepared statements to prevent SQL injection
	$stmt = $conn->prepare("INSERT INTO symptoms(user_id, itching, redness, dryness, irritation, notes, symptom_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("iiiiiss", $user_id, $itching, $redness, $dryness, $irritation, $notes, $date);
	$stmt->execute();
	$stmt->close();
}

if (isset($_POST['delete_symptom_id'])) {
	$stmt = $conn->prepare('DELETE FROM symptoms WHERE symptom_id = ? AND user_id = ?');
	$stmt->bind_param('ii', $_POST['delete_symptom_id'], $user_id);
	$stmt->execute();
	$stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Symptom Tracker - FlareWise</title>
	<link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

	<nav>
		<div class="nav-container">
			<div class="nav-brand">FlareWise</div>
			<div class="nav-links">
				<a href="dashboard.php">Dashboard</a>
                <a href="ingredients_checker.php">Ingredients</a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') echo '<a href="admin_dashboard.php">Admin</a>'; ?>
				<a href="symptoms.php" class="active">Symptoms</a>
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
		<h1>Symptom Tracker</h1>
    
		<div class="card">
			<h2>Log Your Symptoms</h2>
			<form method="POST">
				<label>Itching Severity (1-10)</label>
				<input type="number" name="itching" min="1" max="10" required>
        
				<label>Redness Level (1-10)</label>
				<input type="number" name="redness" min="1" max="10" required>
        
				<label>Dryness Level (1-10)</label>
				<input type="number" name="dryness" min="1" max="10" required>
        
				<label>Irritation Level (1-10)</label>
				<input type="number" name="irritation" min="1" max="10" required>
        
				<label>Additional Notes</label>
				<textarea name="notes" placeholder="Any observations or triggers?"></textarea>
        
				<label>Date</label>
				<input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
        
				<input type="submit" name="save" value="Save Symptom Record">
			</form>
		</div>

		<hr>

		<h2 class="section-title">📋 Your Symptom History</h2>

		<div class="recent">
			<table>
				<thead>
					<tr>
						<th>Date</th>
						<th>Itching</th>
						<th>Redness</th>
						<th>Dryness</th>
						<th>Irritation</th>
						<th>Notes</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Use prepared statements for selecting data
					$stmt = $conn->prepare("SELECT symptom_id, symptom_date, itching, redness, dryness, irritation, notes FROM symptoms WHERE user_id = ? ORDER BY symptom_date DESC LIMIT 20");
					$stmt->bind_param("i", $user_id);
					$stmt->execute();
					$result = $stmt->get_result();

					while($row = $result->fetch_assoc())
					{
						echo "<tr>
							<td>".htmlspecialchars(date("M d, Y", strtotime($row['symptom_date'])))."</td>
							<td><strong>".htmlspecialchars($row['itching'])."</strong>/10</td>
							<td><strong>".htmlspecialchars($row['redness'])."</strong>/10</td>
							<td><strong>".htmlspecialchars($row['dryness'])."</strong>/10</td>
							<td><strong>".htmlspecialchars($row['irritation'])."</strong>/10</td>
							<td>".htmlspecialchars(strlen($row['notes']) > 30 ? substr($row['notes'], 0, 30).'...' : $row['notes'])."</td>
							<td>
								<form method='POST' style='display:inline;'>
									<input type='hidden' name='delete_symptom_id' value='".htmlspecialchars($row['symptom_id'])."'>
									<button type='submit' class='btn-danger'>Delete</button>
								</form>
							</td>
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
