<?php

session_start();
require_once __DIR__ . '/../config/database.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

// The app also works with the original database, but the optional upgrade adds
// a category so users can distinguish medication from a skincare routine.
$type_column = $conn->query("SHOW COLUMNS FROM medications LIKE 'reminder_type'")->num_rows > 0;
$saved_message = '';

if (isset($_POST['save'])) {
	$name=trim($_POST['medicine']);
	$dosage=trim($_POST['dosage']);
	$time=$_POST['time'];
	$type=($_POST['reminder_type'] ?? 'Medication') === 'Skincare' ? 'Skincare' : 'Medication';

	if ($name !== '' && $dosage !== '') {
		if ($type_column) {
			$stmt = $conn->prepare("INSERT INTO medications(user_id, medicine_name, dosage, reminder_type, reminder_time) VALUES (?, ?, ?, ?, ?)");
			$stmt->bind_param("issss", $user_id, $name, $dosage, $type, $time);
		} else {
			$stmt = $conn->prepare("INSERT INTO medications(user_id, medicine_name, dosage, reminder_time) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("isss", $user_id, $name, $dosage, $time);
		}
		$stmt->execute(); $stmt->close();
		$saved_message = 'Reminder saved. Keep this page or the dashboard open to receive alerts.';
	}
}

if (isset($_POST['delete_medication_id'])) {
	$stmt = $conn->prepare('DELETE FROM medications WHERE medication_id = ? AND user_id = ?');
	$stmt->bind_param('ii', $_POST['delete_medication_id'], $user_id);
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
				<a href="symptoms.php">Symptoms</a>
				<a href="medication.php" class="active">Medication</a>
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
		<h1>Medication Reminders</h1>
		<?php if ($saved_message): ?><p class="notice"><?php echo htmlspecialchars($saved_message); ?></p><?php endif; ?>
		<p class="notice">FlareWise shows an in-app alert at the scheduled time. Select <strong>Enable browser notifications</strong> to also receive a browser notification while FlareWise is open.</p>
    
		<div class="card">
			<h2>Add New Medication</h2>
			<form method="POST">
				<label>Medicine Name</label>
				<input type="text" name="medicine" placeholder="e.g., Hydrocortisone Cream" required>
        
				<label>Dosage</label>
				<input type="text" name="dosage" placeholder="e.g., 2 tablets, 1 application" required>

				<label>Reminder Type</label>
				<select name="reminder_type">
					<option value="Medication">Medication</option>
					<option value="Skincare">Skincare routine</option>
				</select>
        
				<label>Reminder Time</label>
				<input type="datetime-local" name="time" required>
        
				<input type="submit" name="save" value="Save Reminder">
				<button type="button" class="button-secondary" onclick="enableFlarewiseNotifications()">Enable browser notifications</button>
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
						<th>Type</th>
						<th>Reminder Time</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Use prepared statements for selecting data
					$select_columns = $type_column ? 'medication_id, medicine_name, dosage, reminder_type, reminder_time' : 'medication_id, medicine_name, dosage, reminder_time';
					$stmt = $conn->prepare("SELECT $select_columns FROM medications WHERE user_id = ? ORDER BY reminder_time ASC");
					$stmt->bind_param("i", $user_id);
					$stmt->execute();
					$result = $stmt->get_result();

					$reminders = [];
					while($row = $result->fetch_assoc())
					{
						$reminders[] = ['id' => (int)$row['medication_id'], 'name' => $row['medicine_name'], 'dosage' => $row['dosage'], 'time' => $row['reminder_time']];
						echo "<tr>
							<td><strong>".htmlspecialchars($row['medicine_name'])."</strong></td>
							<td>".htmlspecialchars($row['dosage'])."</td>
							<td>".htmlspecialchars($row['reminder_type'] ?? 'Medication')."</td>
							<td>".htmlspecialchars(date("M d, Y g:i A", strtotime($row['reminder_time'])))."</td>
							<td>
								<form method='POST' style='display:inline;'>
									<input type='hidden' name='delete_medication_id' value='".htmlspecialchars($row['medication_id'])."'>
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
	<script>window.flarewiseReminders = <?php echo json_encode($reminders ?? []); ?>;</script>
	<script src="../assets/js/reminders.js"></script>

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
