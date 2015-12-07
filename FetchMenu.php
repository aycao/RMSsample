<?php
$configs = include('config.php');

$conn=mysqli_connect($configs['host'],$configs['username'],$configs['password'],$configs['dbname']);

if (mysqli_connect_errno($conn))
{
   echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// $username = $_POST['username'];
// $password = $_POST['password'];

$result = mysqli_query($conn,"SELECT dish,price FROM menu");

$encode = array();
while($row = mysqli_fetch_assoc($result)){
    $encode[] = $row;
}

$jsonobj = new stdClass();
$jsonobj->fooditems = $encode;

echo json_encode($jsonobj);

mysqli_close($conn);
?>