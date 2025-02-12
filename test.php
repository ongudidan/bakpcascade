 <?php
$servername = "localhost";
$username = "cswift_parrot";
$password = "secured@2012";
$dbname = "cswift_farida";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}else{
    echo("connected");
}

$sql = "SELECT * FROM afrxx_assets ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
      echo($row);
    //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
  }
} else {
  echo "0 results";
}
$conn->close();
?> 


<?php
$servername = "localhost";  // Change if accessing remotely
$username = "cswift_parrot"; // Your MySQL username
$password = "secured@2012"; // Your MySQL password
$database = "cswift_farida"; // Your database name
$table = "afrxx_assets"; // Table to export

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File name for download
$file_name = $table . "_backup_" . date("Y-m-d_H-i-s") . ".sql";

// Fetch table structure
$sql = "SHOW CREATE TABLE `$table`";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$table_structure = $row['Create Table'] . ";\n\n";

// Fetch table data
$sql = "SELECT * FROM `$table`";
$result = $conn->query($sql);
$data_dump = "";

while ($row = $result->fetch_assoc()) {
    $values = array_map([$conn, 'real_escape_string'], array_values($row));
    $values = "'" . implode("', '", $values) . "'";
    $data_dump .= "INSERT INTO `$table` VALUES ($values);\n";
}

// Full SQL dump
$sql_dump = "SET FOREIGN_KEY_CHECKS=0;\n\n";
$sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
$sql_dump .= $table_structure;
$sql_dump .= "\n\n$sql_dump";
$sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n\n";
$sql_dump .= $data_dump;

// Close the connection
$conn->close();

// Send file for download
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
echo $sql_dump;
exit;
?>
