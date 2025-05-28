<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>DataBase Server</title>
  </head>
  <body>
    <h1>DataBase Server</h1>
    <br>
    <br>
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
      
      $sql = "SELECT id, name, time, date FROM `logs`;";
      $result = mysqli_query($conn, $sql);
	  
      if (mysqli_num_rows($result) > 0) {
        // Make table
		echo "<center>";
		echo "<table align='center' border='1'>
			<tr>
			<th>Id</th>
			<th>Name</th>
			<th>Time</th>
			<th>Date</th>
			</tr>";
        while($row = mysqli_fetch_array($result))
		  {
		  echo "<tr>";
		  echo "<td align='center'>" . $row['id'] . "</td>";
		  echo "<td align='center'>" . $row['name'] . "</td>";
		  echo "<td align='center'>" . $row['time'] . "</td>";
		  echo "<td align='center'>" . $row['date'] . "</td>";
		  echo "</tr>";
		  }
		echo "</table>";
    echo "<br/>";
    echo '<button onclick="location.href=\'clear.php\';">Clear Logs</button>';
		echo "</center>";
      } else {
        echo "0 results";
      }
      
      mysqli_close($conn);
    ?>
  </body>
</html>