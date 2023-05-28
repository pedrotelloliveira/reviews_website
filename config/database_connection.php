<?php
  $host_name = 'db5009944948.hosting-data.io';
  $database = 'dbs8432013';
  $user_name = 'dbu2621891';
  $password = 'NgFdgHTrWDoSvxCfyjyVfXUvfwHBJI';
  
  try {
	$dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
  } catch (PDOException $e) {
	// echo "Error!:" . $e->getMessage() . "<br/>";
	die("Database connection error");
  }
?>