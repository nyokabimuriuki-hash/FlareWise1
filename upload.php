<?php

session_start();

include("database.php");

$user=$_SESSION['user_id'];

if(isset($_POST['upload']))
{

$image=$_FILES['image']['name'];

$temp=$_FILES['image']['tmp_name'];

move_uploaded_file($temp,"uploads/".$image);

mysqli_query($conn,"INSERT INTO skin_images(user_id,image_name)
VALUES('$user','$image')");

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Skin Images - FlareWise</title>
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
			<a href="logout.php">🚪 Logout</a>
		</nav>
	</div>

<div class="main">
		<h1>Skin Images</h1>
    
		<div class="card">
			<h2>Upload Image</h2>
			<form method="POST" enctype="multipart/form-data">
				<input type="file" name="image" accept="image/*" required>
				<input type="submit" name="upload" value="Upload Image">
			</form>
		</div>
    
		<div class="recent">
			<h2>Your Images</h2>
			<div class="image-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">

<hr>

<?php

$result=mysqli_query($conn,"SELECT * FROM skin_images WHERE user_id='$user'");

while($row=mysqli_fetch_assoc($result))
{

?>

<img
src="uploads/<?php echo $row['image_name'];?>"
width="200"
style="margin:15px;">

<?php

}

?>

</div>

</body>

</html>