<?php
// Define server and DB
$hostname = "localhost";
$dbname = "test";
$usrname = "root";
$passwd = "";

if (isset($_GET['id'])){
	// Get data
	$data = $_GET['id'];

	// Define connection
	$conn = new mysqli($hostname,$usrname,$passwd,$dbname);

	// Check connection
	if ($conn->connect_error) {
	  die("Connection Failed: ".$conn->connect_error);
	}

	// DB query
	$sql = "INSERT INTO `stats` (`id`, `name`) VALUES ('$data', NULL);";

	// Send query
	if ($conn->query($sql) === TRUE) {
	  return "New record creatd successfully";
	}
	else {
	  return "Error: ".$sql."<br>".$conn->error;
	}
	mysqli_close($conn);
}
?>