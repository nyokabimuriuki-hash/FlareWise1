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
                <a href="ingredients_checker.php">Ingredients</a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') echo '<a href="admin_dashboard.php">Admin</a>'; ?>
				<a href="symptoms.php">Symptoms</a>
				<a href="medication.php">Medication</a>
				<a href="upload.php">Images</a>
				<a href="profile.php">Profile</a>
				<a href="about.php" class="active">About Us</a>
			</div>
			<div class="nav-auth">
				<?php if(isset($_SESSION['user_id'])): ?>
					<a id="signout-link" class="signout-btn" href="javascript:void(0);">Sign Out</a>
				<?php else: ?>
					<a href="login.html" class="signin-btn">Sign In</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<div class="main">
		<div class="card" style="text-align: center; margin-bottom: 30px; padding: 40px 20px;">
			<h1 style="font-size: 2.8rem; margin-bottom: 15px; background: linear-gradient(135deg, #1476ca, #16a8ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Our Mission</h1>
			<p style="font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.6;">FlareWise brings clarity and control to your health journey. We empower you to understand your body's patterns through elegant, intuitive tools designed to reduce stress.</p>
		</div>

		<div class="cards">
			<div class="card" style="text-align: center; padding: 30px 20px;">
				<div style="font-size: 3.5rem; margin-bottom: 15px; filter: drop-shadow(0 10px 15px rgba(22,168,255,0.2));">📊</div>
				<h2 style="font-size: 1.2rem; margin-bottom: 10px; color: var(--ink);">Track & Reveal</h2>
				<p style="font-size: 0.95rem;">Effortlessly log symptoms and medications to uncover hidden insights and flare triggers.</p>
			</div>

			<div class="card" style="text-align: center; padding: 30px 20px;">
				<div style="font-size: 3.5rem; margin-bottom: 15px; filter: drop-shadow(0 10px 15px rgba(22,168,255,0.2));">✨</div>
				<h2 style="font-size: 1.2rem; margin-bottom: 10px; color: var(--ink);">Simple & Calm</h2>
				<p style="font-size: 0.95rem;">A streamlined, icy-clean interface designed to make managing your health feel effortless.</p>
			</div>

			<div class="card" style="text-align: center; padding: 30px 20px;">
				<div style="font-size: 3.5rem; margin-bottom: 15px; filter: drop-shadow(0 10px 15px rgba(22,168,255,0.2));">🔒</div>
				<h2 style="font-size: 1.2rem; margin-bottom: 10px; color: var(--ink);">Total Privacy</h2>
				<p style="font-size: 0.95rem;">Your data is yours alone, protected by enterprise-grade security and encryption.</p>
			</div>
		</div>

		<div class="card" style="text-align: center; margin-top: 30px; padding: 40px 20px;">
			<h2 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--ink);">Ready to take control?</h2>
			<div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
				<a href="register.html" class="cta-button" style="margin-top: 0;">Create Account</a>
				<a href="login.html" class="button-secondary" style="display:inline-flex; align-items:center; justify-content:center; padding: .82rem 1.2rem; border-radius: 13px; font-weight: 750; text-decoration: none; margin-top: 0;">Sign In</a>
			</div>
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
