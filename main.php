<?php

class weatherapp {

	// declare properties for object
	public $key, $postcode, $days, $location, $ip_address, $hour;

	// construct object instance
	public function __construct ( $key, $postcode, $days, $location, $hour ) {
		$this->key = $key;
		$this->postcode = $postcode;
		$this->days = $days;
		$this->location = $location;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
		$this->hour = $hour;
	}

	// Check if alerts table exists
	public function check_table_exists ($connection, $table) {
		$sql = "SHOW TABLES LIKE $table";
		$res = $connection->query($sql);
		return $res;
	}

	// Run script that will create database if it doesn't exist
	public function create_table ($connection, $table) {
		$bool = ( $this->check_table_exists($connection, $table) > 0 ) ? true : false;
		if ( !$bool ) {
			include_once "sql/wa_${table}.php";
		}
		else {
			print_r(`table ${table} already exists`);
		}
	}

	// generate url with query string and send to api file
	public function run_api ($connection) {
		$k = $this->key;
		$pc = $this->postcode;
		$l = $this->location;
		$ia = $this->ip_address;
		$d = $this->days;
		$h = $this->hour;

		$q_string = "q=$pc&q=$l&q=$ia";
		$url = "http://api.weatherapi.com/v1/forecast.json?key=$k&$q_string&days=$d&hour=$h&alerts=yes";
		try {

			$file = file_get_contents($url);
			$data = json_decode($file);

			$forecast_obj_array = new ArrayObject($data->forecast->forecastday);
			$alerts_obj_array = new ArrayObject($data->alerts);


			// run returned JSON data object in loop for each item, store and prepare for database insert
			if ( $forecast_obj_array->count() ) {
				$this->insert_location_forecast($connection, $data);
			}
			else {
				print_r("No data has been fetched via the API.");
			}

			if ( $alerts_obj_array->count() > 0 ) {
				$this->insert_alerts($connection, $data);
			}

		}
		catch ( Exception $err ) {
			print_r($err->getMessage());
		}
	}

	// Insert location forecast into database
	public function insert_location_forecast ( $connection, $data ) {

		foreach ( $data->forecast->forecastday as $forecast_item ) {

			$lat = floatval($data->location->lat);
			$lon = floatval($data->location->lon);
			$location = $data->location->name;
			$date = $forecast_item->date;
			$condition = $forecast_item->day->condition->text;

			$params = array(
				'lat' 		=> $lat,
				'lon' 		=> $lon,
				'location' 	=> $location,
				'date' 		=> $date,
				'condition' => $condition
			);

			// Insert SQL
			$data_sql = "
				INSERT INTO location_history
					(
						lat,
						lon,
						name,
						date,
						condition
					)
				VALUES
					(
						:lat,
						:lon,
						:location,
						:date,
						:condition
					)
			";

			try {
				$prep = $connection->prepare($data_sql);
				$res =  $prep->execute($params);
			}
			catch ( PDOException $err ) {
				print_r($err->getMessage());
			}
		}

	}

	// insert weather alerts into the database
	public function insert_alerts ( $connection, $data ) {

		$alerts = $data->alerts;

		foreach ( $alerts as $alert ) {

			$headline = $alert->headline;
			$severity = $alert->severity;
			$urgency = $alert->urgency;
			$areas = $alert->areas;
			$category = $alert->category;
			$event = $alert->event;
			$effective = $alert->effective;
			$expires = $alert->expires;

			$params = array(
				'headline' 	=> $headline,
				'severity' 	=> $severity,
				'urgency' 	=> $urgency,
				'areas' 	=> $areas,
				'category' 	=> $category,
				'event' 	=> $event,
				'effective' => $effective,
				'expires'	=> $expires
			);

			$sql = "
				INSERT INTO alerts
					(
						headline,
						severity,
						urgency,
						areas,
						category,
						event,
						effective,
						expires
					)
				VALUES
					(
						:headline,
						:severity,
						:urgency,
						:areas,
						:category,
						:event,
						:effective,
						:expires
					)
			";
			try {
				$res = $connection->prepare($sql);
				$exe = $res->execute($params);
			}
			catch ( PDOException $err ) {
				print_r($err->getMessage());
			}

		}

	}

}