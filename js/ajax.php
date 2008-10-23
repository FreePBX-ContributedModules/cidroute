<?php

$quietmode = true; // Turn off anything noisy in the included modules
include("../../../functions.inc.php"); // Load our common functions
$amp_conf = parse_amportal_conf('/etc/amportal.conf'); // Get the info from amportal.conf. FIXME
// FIXME - that line above should not be hard coded. How do I find out where it is?
include("../../../common/db_connect.php"); // Connect to DB
	
$input = strtolower( $_GET['input'] );
//$input = 'B';
$len = strlen($input);
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
	
$json = array();
$count = 0;
$i = 0;

// First. If the query is one or two characters, search for state..
if (strlen($input) < 3) {
	$q = sql("select distinct(state) from cidroute_cidlist where state like '%".$db->escapeSimple($input)."%'","getAll", DB_FETCHMODE_ASSOC);
	if (is_array($q)) {
		foreach($q as $row) {
			$json[] = array( "id" => $i++, "info" => "State", "value" => "State:".$row['state']);
		}
	}
}

// How about a region - don't return more than 5.

$q = sql("select distinct(region) from cidroute_cidlist where region like '%".$db->escapeSimple($input)."%' limit 5","getAll", DB_FETCHMODE_ASSOC);

if (is_array($q)) {
	foreach($q as $row) {
		$json[] = array( "id" => $i++, "info" => "Region", "value" => "Region:".$row['region']);
	}
}

// Now, the exchanges
$q = sql("select distinct(localarea) from cidroute_cidlist where localarea like '%".$db->escapeSimple($input)."%'","getAll", DB_FETCHMODE_ASSOC);

if (is_array($q)) {
	foreach($q as $row) {
		$json[] = array( "id" => $i++, "info" => "Local Area", "value" => "Area:".$row['localarea']);
	}
}

header ("Expires: Mon, 23 Jul 1971 05:00:00 GMT"); // Send beer on this day!
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header ("Pragma: no-cache"); // HTTP/1.0
header("Content-Type: application/json");
	
echo "{\"results\": [";
$arr = array();
for ($i=0;$i<count($json) & $i < $limit;$i++) {
	$arr[] = "{\"id\": \"".$json[$i]['id']."\", \"value\": \"".$json[$i]['value']."\", \"info\": \"".$json[$i]['info']."\"}";
}
echo implode(", ", $arr);
echo "]}";
?>
