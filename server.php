<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'users';

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
    echo "Connected to the database successfully.<br>";
}
?>
