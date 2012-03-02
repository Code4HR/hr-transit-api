A simple, CouchDB-based API for Hampton Roads transit data
==========================================================

Base API URL - http://127.0.0.1:5984/hrtransit

List all positions (current max is last 5 positions) for all routes:
http://127.0.0.1:5984/hrtransit/_design/app/_list/positions/all

List all positions (current max is last 5 positions) for a specific route:
http://127.0.0.1:5984/hrtransit/_design/app/_list/positions/all?route_id=2106

List latest positions for all routes:
http://127.0.0.1:5984/hrtransit/_design/app/_list/positions/latest

List latest position for a specific route:
http://127.0.0.1:5984/hrtransit/_design/app/_list/positions/latest?route_id=2106


Sample crontab for parsing Hampton Road transit CSV file
========================================================

<pre>
# Parse HRT CSV file and populate CouchDB. 
*/1 * * * * php path/to/hr-transit-api/index.php

# Periodically run compaction on CouchDB.
*/5 * * * * curl -s -H "Content-Type: application/json" -X POST http://127.0.01:5984/hrtransit/_compact > /dev/null
</pre>
