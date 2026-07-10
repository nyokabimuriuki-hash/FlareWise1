<?php

$host="localhost";
$user="root";
$password="";
$database="flarewise";

$conn=mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Connection Failed");
}