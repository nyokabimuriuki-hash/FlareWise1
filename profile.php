<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Profile - FlareWise</title>
	<link rel="stylesheet" href="dashboard.css">
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
				<a href="profile.php" class="active">Profile</a>
				<a href="about.php">About Us</a>
			</div>
			<div class="nav-auth">
				<a id="signout-link" class="signout-btn">Sign Out</a>
			</div>
		</div>
	</nav>

	<div class="main">
		<h1>Your Profile</h1>

		<div class="card">
			<h2>Account Information</h2>
			<div style="line-height: 2; font-size: 16px;">
				<p>
					<strong style="color: #0277bd;">Full Name:</strong> 
					<span id="profile-name" style="color: #01579b; font-weight: 500;">-</span>
				</p>
				<p>
					<strong style="color: #0277bd;">Email Address:</strong> 
					<span id="profile-email" style="color: #01579b; font-weight: 500;">-</span>
				</p>
				<p>
					<strong style="color: #0277bd;">Member Since:</strong> 
					<span id="profile-created" style="color: #01579b; font-weight: 500;">Today</span>
				</p>
				<p>
					<strong style="color: #0277bd;">Account Status:</strong> 
					<span style="color: #4caf50; font-weight: 500;">✓ Active</span>
				</p>
			</div>
		</div>

		<hr>

		<div class="card">
			<h2>Account Settings</h2>
			<p style="color: #666; margin-bottom: 20px;">Your account is securely connected to Firebase Authentication.</p>
			<p style="color: #999; font-size: 14px;">For account security and password changes, please use your email provider's account settings.</p>
		</div>
	</div>

	<!-- Firebase SDKs (compat) -->
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
	<script src="firebase-config.js"></script>
	<script>
		const auth = firebase.auth();
		auth.onAuthStateChanged(user => {
			if (!user) {
				window.location = 'login.html';
				return;
			}
			document.getElementById('profile-name').textContent = user.displayName || 'Not Set';
			document.getElementById('profile-email').textContent = user.email || '-';
			
			const createdDate = new Date(user.metadata.creationTime);
			document.getElementById('profile-created').textContent = createdDate.toLocaleDateString();
		});

		document.getElementById('signout-link').addEventListener('click', async (e) => {
			e.preventDefault();
			// First, destroy the PHP session, then sign out from Firebase for consistency.
			await fetch('logout_session.php');
			await auth.signOut();
			window.location.href = 'index.php';
		});
	</script>

</body>
</html>