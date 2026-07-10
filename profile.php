<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Profile - FlareWise</title>
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
		<h1>User Profile</h1>

		<div class="card">
			<h2>Account Information</h2>
			<p><strong>Name:</strong> <span id="profile-name">-</span></p>
			<p><strong>Email:</strong> <span id="profile-email">-</span></p>
		</div>
	</div>

	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
	<script src="firebase-config.js"></script>
	<script>
		const auth = firebase.auth();
		auth.onAuthStateChanged(user => {
			if (!user) return window.location = 'login.html';
			document.getElementById('profile-name').textContent = user.displayName || '-';
			document.getElementById('profile-email').textContent = user.email || '-';
		});

		document.getElementById('signout-link').addEventListener('click', async (e) => {
			e.preventDefault();
			await auth.signOut();
			window.location = 'logout.php';
		});
	</script>

</body>
</html>