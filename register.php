<?php

include("database.php");

$fullname=$_POST['fullname'];
$email=$_POST['email'];
$password=password_hash($_POST['password'],PASSWORD_DEFAULT);

$sql="INSERT INTO users(fullname,email,password)
VALUES('$fullname','$email','$password')";

if(mysqli_query($conn,$sql))
{
    header("Location: ../login.html");
}
else
{
    echo "Registration Failed";
}

?>