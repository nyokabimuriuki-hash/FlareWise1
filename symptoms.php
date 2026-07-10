<?php

session_start();
include("database.php");

$user=$_SESSION['user_id'];

if(isset($_POST['save']))
{

$itching=$_POST['itching'];
$redness=$_POST['redness'];
$dryness=$_POST['dryness'];
$irritation=$_POST['irritation'];
$notes=$_POST['notes'];
$date=$_POST['date'];

$sql="INSERT INTO symptoms(user_id,itching,redness,dryness,irritation,notes,symptom_date)
VALUES('$user','$itching','$redness','$dryness','$irritation','$notes','$date')";

mysqli_query($conn,$sql);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Symptom Tracker - FlareWise</title>
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
		<h1>Symptom Tracker</h1>
    
		<div class="card">
			<h2>Log Your Symptoms</h2>
			<form method="POST">
				<label>Itching (1-10)</label>
				<input type="number" name="itching" min="1" max="10" required>
        
				<label>Redness (1-10)</label>
				<input type="number" name="redness" min="1" max="10">
        
				<label>Dryness (1-10)</label>
				<input type="number" name="dryness" min="1" max="10">
        
				<label>Irritation (1-10)</label>
				<input type="number" name="irritation" min="1" max="10">
        
				<label>Notes</label>
				<textarea name="notes" style="width: 100%; padding: 12px; margin-top: 10px; border-radius: 10px; border: 1px solid rgba(2, 119, 189, 0.3); background: rgba(255, 255, 255, 0.25); color: #01579b;"></textarea>
        
				<label>Date</label>
				<input type="date" name="date" required>
        
				<input type="submit" name="save" value="Save Symptom">
			</form>
		</div>

<hr>

<h2>Previous Records</h2>

<table>

<tr>

<th>Date</th>
<th>Itching</th>
<th>Redness</th>
<th>Dryness</th>
<th>Irritation</th>

</tr>

<?php

$result=mysqli_query($conn,"SELECT * FROM symptoms WHERE user_id='$user' ORDER BY symptom_date DESC");

while($row=mysqli_fetch_assoc($result))
{

echo"

<tr>

<td>".$row['symptom_date']."</td>

<td>".$row['itching']."</td>

<td>".$row['redness']."</td>

<td>".$row['dryness']."</td>

<td>".$row['irritation']."</td>

</tr>

";

}

?>

</table>

</div>

</body>

</html>