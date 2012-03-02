<?php

// Include Sag library.
require 'classes/sag/Sag.php';

// FTP URI to transit data.
define("TRANSIT_FTP_URI", "ftp://216.54.15.3/Anrd/hrtrtf.txt");

// CouchDB settings
define("COUCHDB_HOST", "127.0.0.1");
define("COUCHDB_PORT", "5984");
define("COUCHDB_USER", "");
define("COUCHDB_PASS", "");
define("COUCHDB_NAME", "hrtransit");

// Maximum number of locations to store.
define("MAX_LOCATIONS", 5);

// Get the transit data.
$data = file_get_contents(TRANSIT_FTP_URI);
$rows = explode("\n", $data);

$sag = new Sag(COUCHDB_HOST, COUCHDB_PORT);
$sag->setDatabase(COUCHDB_NAME);

foreach($rows as $row) {

	$record = explode(",", $row);
	if(!is_numeric($record[2])) {
		continue;
	}
	else {

		// Construct object containing position information.
		$positions = array("time" => trim($record[0]), 
					"date" => trim($record[1]), 
					"lat/lon" => trim($record[3]), 
					"loc_valid" => trim($record[4]), 
					"adherence" => trim($record[5]), 
					"adh_valid" => trim($record[6]), 
					"route" => trim($record[7]), 
					"description" => trim($record[8]), 
					"stopid" => trim($record[9])
		);

		// Get the document for the specified route.
		try {

			$doc = $sag->get($record[2])->body;
			if($doc->positions) {
				if(count($doc->positions) >= MAX_LOCATIONS) {
					$pos_slice = array_slice($doc->positions, 1);
					array_push($pos_slice, $positions);
					$doc->positions = $pos_slice;
				}
				else {
					array_push($doc->positions, $positions);
				}
				
			}
			else {
				$doc->positions = array($positions);
			}
			$sag->put($doc->_id, $doc);

		}

		// If no document exists for this route, create one.
		catch (Exception $ex) {
			$trace = $ex->getTrace();
			$doc->_id = $trace[1]["args"][0];
			$doc->positions = array($positions);
			$sag->put($doc->_id, $doc);
		}
	}
}

?>