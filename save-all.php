<?php

// Include Sag library.
require 'classes/sag/Sag.php';

// FTP URI to transit data.
define("TRANSIT_FTP_URI", "ftp://216.54.15.3/Anrd/hrtrtf.txt");

// CouchDB settings
define("COUCHDB_HOST", "hrt.iriscouch.com");
define("COUCHDB_PORT", "5984");
define("COUCHDB_USER", "");
define("COUCHDB_PASS", "");
define("COUCHDB_NAME", "hrtransit");

// Get the transit data.
$data = file_get_contents(TRANSIT_FTP_URI);
$rows = explode("\n", $data);

// Set up new Sag object.
$sag = new Sag(COUCHDB_HOST, COUCHDB_PORT);
$sag->setDatabase(COUCHDB_NAME);

// Format the lat / lon more cleanly.
function splitLatLong($item) {
	$coordinates = explode("/", $item);
	for($i=0; $i<count($coordinates); $i++) {
		$prefix = $coordinates[$i] < 0 ? 3 : 2;
		$coordinates[$i] = substr($coordinates[$i], -10, $prefix) . "." . substr($coordinates[$i], -7);
	}
	return $coordinates;
}

foreach($rows as $row) {

	$record = explode(",", $row);

	// Skip the header row.
	if(!is_numeric($record[2])) {
		continue;
	}

	// Store it in CouchDB.
	else {
		$crossing = array("time" => trim($record[0]),
					"date" => trim($record[1]), 
					"vehicle" => trim($record[2]),
					"lat/lon" => splitLatLong(trim($record[3]))	, 
					"loc_valid" => trim($record[4]), 
					"adherence" => trim($record[5]), 
					"adh_valid" => trim($record[6]), 
					"route" => trim($record[7]), 
					"description" => trim($record[8]), 
					"stopid" => trim($record[9])
		);

		$sag->post($crossing);
	}

}

?>