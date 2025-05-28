<?php
// Define server and DB
$hostname = "localhost";
$dbname = "test";
$usrname = "root";
$passwd = "";

// Define connection
$conn = new mysqli($hostname,$usrname,$passwd,$dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection Failed: ".$conn->connect_error);
}

// DB query
$sql = "DELETE FROM `logs`;";

// Send query
if ($conn->query($sql) === TRUE) {
	header('Location: /');
	return "Logs cleared successfully";
}
else {
	return "Error: ".$sql."<br>".$conn->error;
}
mysqli_close($conn);

?>