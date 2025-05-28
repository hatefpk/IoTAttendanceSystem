<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database Names</title>
</head>
<body>
    <h1>Database Names</h1>
    <br>
    <br>
    <?php
    // Define server and DB
    $hostname = "localhost";
    $dbname = "test";
    $usrname = "root";
    $passwd = "";

    // Define connection
    $conn = new mysqli($hostname, $usrname, $passwd, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, name FROM `stats`;";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Make table
        echo "<center>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<form method='get' action='namer.php'>";
            echo "<br><br>";
            echo "<label for='id'>ID:</label>
                <input type='text' id='id' name='id' value='" . $row['id'] . "' readonly>";
            echo "<label for='name'> Name:</label>
                <input type='text' id='name' name='name' value='" . $row['name'] . "'>";
            echo "  <input type='submit' value='Submit'>";
            echo "</form>";    
          }
        echo "</center>";
    } else {
        echo "0 results";
    }

    mysqli_close($conn);
    ?>
    <br/>
</body>
</html>
