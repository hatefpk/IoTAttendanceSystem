<?php
// Define server and DB
$hostname = "localhost";
$dbname = "test";
$usrname = "root";
$passwd = "";

if (isset($_GET['id'], $_GET['name'])){
	// Get data
	$data = $_GET['id'];
	$name = $_GET['name'];

	// Define connection
	$conn = new mysqli($hostname,$usrname,$passwd,$dbname);

	// Check connection
	if ($conn->connect_error) {
	  die("Connection Failed: ".$conn->connect_error);
	}

	// DB query
	$sql = "UPDATE `stats` SET `name` = '$name' WHERE `stats`.`id` = '$data'; ";

	// Send query
	if ($conn->query($sql) === TRUE) {
		header('Location: /name.php');
	  return "New record creatd successfully";
	}
	else {
	  return "Error: ".$sql."<br>".$conn->error;
	}
	mysqli_close($conn);
}
?>