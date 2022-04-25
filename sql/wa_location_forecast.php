<?php

global $wa_connect;

try {

  // create location_forecast table
  $location_sql = "
  CREATE TABLE IF NOT EXISTS location_forecast (
	  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	  lat DECIMAL,
	  lon DECIMAL,
	  name VARCHAR(50) NOT NULL,
	  date DATETIME,
	  condition VARCHAR(100) NOT NULL
  )";

  $wa_connect->exec($location_sql);
  print_r("Table location_history created successfully");
}
catch( PDOException $e ) {
	print_r($e->getMessage());
}