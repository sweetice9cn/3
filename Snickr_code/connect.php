<?php 
$host_db="127.0.0.1";
$port_db=3306;
$socket_db="";
$user_db="root";
$password_db="password";
$dbname_db="snickr";

$con = new mysqli($host_db, $user_db, $password_db, $dbname_db, $port_db, $socket_db)
	or die ('Could not connect to the database server' . mysqli_connect_error());

//$con->close();

?>
