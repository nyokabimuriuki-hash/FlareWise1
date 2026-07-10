<?php

session_start();

include("database.php");

$user=$_SESSION['user_id'];

if(isset($_POST['save']))
{

$name=$_POST['medicine'];
$dosage=$_POST['dosage'];
$time=$_POST['time'];

$sql="INSERT INTO medications(user_id,medicine_name,dosage,reminder_time)
VALUES('$user','$name','$dosage','$time')";

mysqli_query($conn,$sql);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Medications - FlareWise</title>
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
		<h1>Medication Reminders</h1>
    
		<div class="card">
			<h2>Add Medication</h2>
			<form method="POST">
				<input type="text" name="medicine" placeholder="Medicine Name" required>
				<input type="text" name="dosage" placeholder="Dosage (e.g., 2 tablets)" required>
				<label>Reminder Time</label>
				<input type="time" name="time" required>
				<input type="submit" name="save" value="Save Medication">
			</form>
		</div>

<hr>

<table>

<tr>

<th>Medicine</th>

<th>Dosage</th>

<th>Reminder</th>

</tr>

<?php

$result=mysqli_query($conn,"SELECT * FROM medications WHERE user_id='$user'");

while($row=mysqli_fetch_assoc($result))
{

echo"

<tr>

<td>".$row['medicine_name']."</td>

<td>".$row['dosage']."</td>

<td>".$row['reminder_time']."</td>

</tr>

";

}

?>

</table>

</div>

</body>

</html>