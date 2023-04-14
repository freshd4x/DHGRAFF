<?php
$host = 'sdb-v.hosting.stackcp.net';
$user = 'DHGraff-3231328df0';
$password = "ZzgSOJA17,'=";
$dbname = 'DHGraff-3231328df0';


//Set DSN
$dsn = 'mysql:host='.$host.';dbname='.$dbname;

//Create PDO instance
$pdo = new PDO($dsn, $user, $password);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
?>
