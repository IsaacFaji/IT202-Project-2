<?php
$host = "sql1.njit.edu";
$user = "iof";
$pass = "YNzR;0Q;0aka0}p0n1c|";
$dbname = "iof";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>