<?php

global $wa_connect;

try {

  // create wa_alerts table
  $alert_sql = "
  CREATE TABLE IF NOT EXISTS alerts (
	  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	  headline VARCHAR(500) NOT NULL,
	  severity VARCHAR(50),
	  urgency VARCHAR(50),
	  areas VARCHAR(250),
	  category VARCHAR(50),
	  event VARCHAR(50),
	  effective DATETIME NOT NULL,
	  expires DATETIME NOT NULL
  )";

  $wa_connect->exec($alert_sql);
  print_r("Table alerts created successfully");
}
catch( PDOException $e ) {
	print_r($e->getMessage());
}