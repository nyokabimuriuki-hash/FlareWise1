<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>About Us - FlareWise</title>
	<link rel="stylesheet" href="../assets/css/app.css">
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
				<a href="profile.php">Profile</a>
				<a href="about.php" class="active">About Us</a>
			</div>
			<div class="nav-auth">
				<?php if(isset($_SESSION['user_id'])): ?>
					<a id="signout-link" class="signout-btn">Sign Out</a>
				<?php else: ?>
					<a href="login.html" class="signin-btn">Sign In</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<div class="main">
		<h1>Our Mission</h1>

		<div class="card">
			<h2>Empowering Your Health Journey</h2>
			<p>
				Living with a chronic skin or autoimmune condition is a personal journey that requires resilience and understanding. FlareWise was born from a desire to bring clarity and control to that journey. We believe that by providing elegant, intuitive tools, we can empower you to understand your body's patterns, manage your care with confidence, and feel more in control of your health every single day.
			</p>
		</div>

		<div class="card">
			<h2>A Private, Personal Health Companion</h2>
			<p>
				FlareWise is more than just an app; it's a secure space for your most sensitive health information. Our core philosophy is built on three pillars:
			</p>
			<p>
				<strong>Clarity through Data:</strong> Effortlessly track symptoms, medications, and visual progress. Our platform helps you connect the dots between your daily life and your health, revealing insights that were previously hidden.
			</p>
			<p>
				<strong>Simplicity in Design:</strong> We've designed a clean, calming interface that reduces stress, not adds to it. Managing your health shouldn't feel like a chore, and our streamlined design ensures a seamless experience.
			</p>
			<p>
				<strong>Uncompromising Privacy:</strong> Your trust is our highest priority. Your health data is yours alone. We are committed to enterprise-grade security to ensure your personal information remains private and protected, always.
			</p>
		</div>

		<div class="card">
			<h2>Designed for You</h2>
			<p>
				Every feature in FlareWise is thoughtfully designed with the user's experience in mind. We are dedicated to creating a tool that not only serves a practical purpose but also provides a sense of calm and partnership in your path to wellness.
			</p>
		</div>

		<div class="card">
			<h2>Get Started</h2>
			<p>
				Ready to take the next step in your health journey? <a href="register.html" style="color: #0288d1; font-weight: 600;">Create an account</a> 
				or <a href="login.html" style="color: #0288d1; font-weight: 600;">sign in</a> to begin.
			</p>
		</div>
	</div>

	<!-- Firebase SDKs (compat) -->
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
	<script src="../assets/js/firebase-config.js"></script>

	<script>
		const auth = firebase.auth();

		// Sign out handler
		const signoutLink = document.getElementById('signout-link');
		if(signoutLink) {
			signoutLink.addEventListener('click', async (e) => {
				e.preventDefault();
				await fetch('../api/logout_session.php');
				await auth.signOut();
			window.location.href = '../index.php';
			});
		}
	</script>

</body>

</html>
