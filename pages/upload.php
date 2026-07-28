<?php

session_start();
require_once __DIR__ . '/../config/database.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['upload'])) {
	$uploadDirectory = __DIR__ . '/../uploads';
	if(!is_dir($uploadDirectory)) {
		mkdir($uploadDirectory, 0755, true);
	}
	
	$image=$_FILES['image']['name'];
	$temp=$_FILES['image']['tmp_name'];
	
	if(!empty($image)) {
		$image_new = time() . '_' . $image;
		move_uploaded_file($temp, $uploadDirectory . '/' . $image_new);

		$stmt = $conn->prepare("INSERT INTO skin_images(user_id, image_name) VALUES (?, ?)");
		$stmt->bind_param("is", $user_id, $image_new);
		$stmt->execute();
		$stmt->close();
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Skin Images - FlareWise</title>
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
				<a href="upload.php" class="active">Images</a>
				<a href="profile.php">Profile</a>
				<a href="about.php">About Us</a>
			</div>
			<div class="nav-auth">
				<a id="signout-link" class="signout-btn">Sign Out</a>
			</div>
		</div>
	</nav>

	<div class="main">
		<h1>Skin Images</h1>
    
		<div class="card">
			<h2>Upload Skin Image</h2>
			<form method="POST" enctype="multipart/form-data">
				<label>Select Image</label>
				<input type="file" name="image" accept="image/*" required>
        
				<input type="submit" name="upload" value="Upload Image">
			</form>
		</div>
    
		<hr>

		<h2 class="section-title">Your Image Gallery</h2>

		<div class="recent">
			<div class="image-gallery">
				<?php
				$stmt = $conn->prepare("SELECT image_name FROM skin_images WHERE user_id = ? ORDER BY image_id DESC");
				$stmt->bind_param("i", $user_id);
				$stmt->execute();
				$result = $stmt->get_result();

				while($row = $result->fetch_assoc())
				{
					?>
					<img src="../uploads/<?php echo htmlspecialchars($row['image_name']);?>" alt="Skin image">
					<?php
				}
				?>
			</div>
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
