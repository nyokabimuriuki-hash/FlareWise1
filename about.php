<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>About Us - FlareWise</title>
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
				<?php if(isset($_SESSION['user_id'])): ?>
					<a id="signout-link" class="signout-btn">🚪 Sign Out</a>
				<?php else: ?>
					<a href="login.html" class="signin-btn">Sign In</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<div class="main">
		<h1>About FlareWise</h1>

		<div class="card">
			<h2>What is FlareWise?</h2>
			<p>
				FlareWise is a comprehensive digital health application designed to help individuals manage skin conditions and autoimmune diseases. 
				Our mission is to empower users with tools to track symptoms, manage medications, and monitor skin health with ease and confidence.
			</p>
		</div>

		<div class="card">
			<h2>Our Features</h2>
			<p>
				<strong>📊 Symptom Tracking:</strong> Log and monitor your daily symptoms to identify patterns and triggers.
			</p>
			<p>
				<strong>💊 Medication Management:</strong> Keep track of your medications, dosages, and reminder times.
			</p>
			<p>
				<strong>📷 Skin Image Gallery:</strong> Document your skin health progress with timestamped images.
			</p>
			<p>
				<strong>👤 Personal Profile:</strong> Manage your account information securely.
			</p>
		</div>

		<div class="card">
			<h2>Why Choose FlareWise?</h2>
			<p>
				FlareWise combines a modern, intuitive interface with secure cloud-based storage. Our glass-morphic design provides 
				a soothing, professional experience while you manage your health journey. All your data is protected with enterprise-grade security.
			</p>
		</div>

		<div class="card">
			<h2>Get Started</h2>
			<p>
				Ready to take control of your health? <a href="register.html" style="color: #0288d1; font-weight: 600;">Create an account</a> 
				or <a href="login.html" style="color: #0288d1; font-weight: 600;">sign in</a> to get started with FlareWise.
			</p>
		</div>
	</div>

	<!-- Firebase SDKs (compat) -->
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
	<script src="firebase-config.js"></script>

	<script>
		const auth = firebase.auth();

		// Sign out handler
		const signoutLink = document.getElementById('signout-link');
		if(signoutLink) {
			signoutLink.addEventListener('click', async (e) => {
				e.preventDefault();
				await auth.signOut();
				window.location = 'login.html';
			});
		}
	</script>

</body>

</html>
