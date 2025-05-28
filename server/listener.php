<?php
// Take shamsi date and time
require_once './includes/jdf.php';
date_default_timezone_set("Iran");
$local_date = jdate('Y/m/d');
$local_time = date("H:i");

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
	$sql = "SELECT `name` FROM `stats` WHERE `id` = '$data';";
	$result = mysqli_query($conn, $sql);
	$ary = mysqli_fetch_assoc($result);
	$name = $ary["name"];

	echo $name;

	// DB send query
	$sqlSend = "INSERT INTO `logs` (`id`, `name`, `time`, `date`) VALUES ('$data', '$name', '$local_time', '$local_date');";

	// Send query
	if ($conn->query($sqlSend) === TRUE) {
	  return "New log recorded successfully";
	}
	else {
	  return "Error: ".$sqlSend."<br>".$conn->error;
	}

	mysqli_close($conn);
}