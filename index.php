<?php

$servername = "localhost";
$dbname = "";
$username = "";
$password = "";

try {
	$wa_connect = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password, array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => true,
			PDO::ERRMODE_EXCEPTION => true
	));
}
catch ( PDOException $err ) {
	print_r($err->getMessage());
}

include_once "main.php";

// declare variables to be used for API
$key = "";
$postcode = "";
$days = 10;
$location = "";
$ip_address = $_SERVER['REMOTE_ADDR'];
$hour = 12;

// Run app
$app = new weatherapp($key, $postcode, $days, $location, $hour);

$app->create_table($wa_connect, "alerts");
$app->create_table($wa_connect, "location_forecast");

$app->run_api($wa_connect);
$wa_connect = null;