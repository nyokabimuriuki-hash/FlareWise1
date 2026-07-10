<?php
session_start();

// If a user is already logged in (has a PHP session), redirect them straight to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FlareWise - Manage Your Skin Health</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Segoe UI', 'Trebuchet MS', sans-serif;
		}

		body {
			background: linear-gradient(135deg, #f0f4f8 0%, #e0eafc 100%);
			min-height: 100vh;
			overflow-x: hidden;
			color: #333;
		}

		/* Navigation */
		nav {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			height: 80px;
			background: rgba(255, 255, 255, 0.6);
			backdrop-filter: blur(10px);
			border-bottom: 1px solid rgba(255, 255, 255, 0.2);
			z-index: 1000;
			display: flex;
			align-items: center;
			padding: 0 40px;
			box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
		}

		.nav-brand {
			font-size: 28px;
			font-weight: 800;
			background: linear-gradient(135deg, #0077B6 0%, #005A8D 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
			letter-spacing: -1px;
			margin-right: auto;
		}

		.nav-buttons {
			display: flex;
			gap: 15px;
			align-items: center;
		}

		.nav-buttons a {
			text-decoration: none;
			padding: 10px 24px;
			border-radius: 20px;
			font-weight: 600;
			font-size: 14px;
			transition: all 0.3s ease;
			white-space: nowrap;
		}

		.signin-btn {
			background: transparent;
			color: #005A8D;
			border: 1.5px solid #0077B6;
		}

		.signin-btn:hover {
			background: rgba(0, 119, 182, 0.1);
			border-color: #005A8D;
		}

		.signup-btn {
			background: linear-gradient(135deg, #0077B6 0%, #005A8D 100%);
			color: white;
			box-shadow: 0 4px 15px rgba(0, 90, 141, 0.2);
			border: none;
		}

		.signup-btn:hover {
			background: linear-gradient(135deg, #005A8D 0%, #004366 100%);
			box-shadow: 0 6px 20px rgba(0, 90, 141, 0.3);
			transform: translateY(-2px);
		}

		/* Hero Section */
		.hero {
			display: flex;
			align-items: center;
			justify-content: space-between;
			min-height: 100vh;
			padding: 80px 40px 40px;
			max-width: 1400px;
			margin: 0 auto;
			gap: 60px;
		}

		.hero-content {
			flex: 1;
			animation: fadeInLeft 0.8s ease-out;
		}

		.hero-visual {
			flex: 1;
			animation: fadeInRight 0.8s ease-out;
		}

		@keyframes fadeInLeft {
			from {
				opacity: 0;
				transform: translateX(-30px);
			}
			to {
				opacity: 1;
				transform: translateX(0);
			}
		}

		@keyframes fadeInRight {
			from {
				opacity: 0;
				transform: translateX(30px);
			}
			to {
				opacity: 1;
				transform: translateX(0);
			}
		}

		.hero h1 {
			font-size: 56px;
			font-weight: 800;
			color: #003366;
			margin-bottom: 20px;
			line-height: 1.2;
			letter-spacing: -1px;
		}

		.hero-subtitle {
			font-size: 19px;
			color: #555;
			margin-bottom: 30px;
			line-height: 1.8;
		}

		.hero-features {
			display: flex;
			flex-direction: column;
			gap: 20px;
			margin-bottom: 40px;
		}

		.feature-item {
			display: flex;
			align-items: center;
			gap: 20px;
			padding: 18px;
			background: rgba(255, 255, 255, 0.5);
			backdrop-filter: blur(10px);
			border: 1px solid rgba(255, 255, 255, 0.3);
			border-radius: 12px;
			transition: all 0.3s ease;
			position: relative;
		}

		.feature-item:hover {
			background: rgba(255, 255, 255, 0.8);
			transform: translateX(8px);
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
		}

		.feature-text h3 {
			color: #005A8D;
			font-size: 16px;
			margin-bottom: 5px;
			font-weight: 600;
		}

		.feature-text p {
			color: #444;
			font-size: 14px;
			line-height: 1.5;
		}

		.cta-button {
			display: inline-block;
			padding: 16px 40px;
			background: linear-gradient(135deg, #0077B6 0%, #005A8D 100%);
			color: white;
			text-decoration: none;
			border-radius: 20px;
			font-weight: 600;
			font-size: 16px;
			box-shadow: 0 4px 20px rgba(2, 136, 209, 0.3);
			transition: all 0.3s ease;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.cta-button:hover {
			background: linear-gradient(135deg, #005A8D 0%, #004366 100%);
			box-shadow: 0 6px 25px rgba(0, 90, 141, 0.35);
			transform: translateY(-2px);
		}

		/* Illustration */
		.illustration {
			width: 100%;
			max-width: 500px;
			height: auto;
			animation: float 3s ease-in-out infinite;
		}

		@keyframes float {
			0%, 100% {
				transform: translateY(0px);
			}
			50% {
				transform: translateY(-20px);
			}
		}

		.illustration-bg {
			width: 100%;
			height: 500px;
			background: rgba(255, 255, 255, 0.2);
			border-radius: 30px;
			display: flex;
			align-items: center;
			justify-content: center;
			overflow: hidden;
			position: relative;
			backdrop-filter: blur(5px);
			border: 1px solid rgba(255, 255, 255, 0.3);
		}

		.illustration-bg::after {
			content: '';
			position: absolute;
			width: 600px;
			height: 600px;
			background: radial-gradient(circle, rgba(0, 119, 182, 0.2) 0%, rgba(0, 119, 182, 0) 60%);
			top: 50%;
			left: 50%;
			animation: pulse 4s ease-in-out infinite;
		}

		@keyframes pulse {
			0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
			50% {
				transform: translate(-50%, -50%) scale(1.1);
				opacity: 1;
			}
			100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
		}

		/* Footer */
		footer {
			text-align: center;
			padding: 40px;
			color: #666;
			font-size: 14px;
			border-top: 1px solid rgba(255, 255, 255, 0.3);
		}

		footer a {
			color: #0077B6;
			text-decoration: none;
			margin: 0 10px;
			transition: color 0.3s ease;
		}

		footer a:hover {
			color: #005A8D;
		}

		/* Responsive */
		@media (max-width: 1024px) {
			.hero {
				padding: 80px 30px 40px;
				gap: 40px;
			}

			.hero h1 {
				font-size: 44px;
			}

			.hero-subtitle {
				font-size: 16px;
			}
		}

		@media (max-width: 768px) {
			nav {
				height: auto;
				padding: 15px 20px;
				flex-wrap: wrap;
			}

			.nav-brand {
				width: 100%;
				text-align: center;
				font-size: 24px;
				margin-right: 0;
				margin-bottom: 15px;
			}

			.nav-buttons {
				width: 100%;
				justify-content: center;
			}

			.hero {
				flex-direction: column;
				padding: 80px 20px 40px;
				gap: 40px;
			}

			.hero h1 {
				font-size: 36px;
			}

			.hero-subtitle {
				font-size: 16px;
			}

			.hero-visual {
				width: 100%;
			}

			.illustration-bg {
				height: 350px;
			}
		}
	</style>
</head>
<body>

	<nav>
		<div class="nav-brand">FlareWise</div>
		<div class="nav-buttons">
			<a href="login.html" class="signin-btn">Sign In</a>
			<a href="register.html" class="signup-btn">Sign Up</a>
		</div>
	</nav>

	<div class="hero">
		<div class="hero-content">
			<h1>Clarity and Control For Your Health Journey</h1>
			
			<p class="hero-subtitle">
				FlareWise is a private, personal companion for managing chronic skin and autoimmune conditions. Gain deeper insights by tracking symptoms, medications, and visual progress in one secure place.
			</p>

			<div class="hero-features">
				<div class="feature-item">
					<div class="feature-text">
						<h3>Symptom Analysis</h3>
						<p>Log daily data to uncover trends and potential triggers.</p>
					</div>
				</div>

				<div class="feature-item">
					<div class="feature-text">
						<h3>Medication Log</h3>
						<p>Organize your treatment schedule with simple reminders.</p>
					</div>
				</div>

				<div class="feature-item">
					<div class="feature-text">
						<h3>Visual Timeline</h3>
						<p>Privately document your skin's journey with a photo gallery.</p>
					</div>
				</div>

				<div class="feature-item">
					<div class="feature-text">
						<h3>Secure & Private</h3>
						<p>Your personal health data is encrypted and protected.</p>
					</div>
				</div>
			</div>
			<a href="register.html" class="cta-button">Create Your Account</a>
		</div>

		<div class="hero-visual">
			<div class="illustration-bg"></div>
		</div>
	</div>

	<footer>
		<p>
			FlareWise © 2026 | 
			<a href="about.php">About Us</a> | 
			<a href="login.html">Sign In</a>
		</p>
	</footer>

</body>
</html>