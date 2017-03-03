<?php

$host = isset($_GET['host']) ? $_GET['host'] : 'localhost';
$name = isset($_GET['name']) ? $_GET['name'] : 'uname';
$pass = isset($_GET['pass']) ? $_GET['pass'] : 'pass';
$data = isset($_GET['data']) ? $_GET['data'] : 'db';

$mysqli = new mysqli($host, $name, $pass, $data);
echo "Starting Clear ... <br> ";

$mysqli->query('SET foreign_key_checks = 0');
if ($result = $mysqli->query("SHOW TABLES"))
{
    while($row = $result->fetch_array(MYSQLI_NUM))
    {
        $mysqli->query('DROP TABLE IF EXISTS '.$row[0]);
    }
}

$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
echo "DB Cleared";
