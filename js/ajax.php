<?php

//Copyright (C) Rob Thomas <xrobau@gmail.com> -
//    Why Pay More 4 Less Pty Ltd (Australia) 2008
//
//       This is NOT OPEN SOURCE SOFTWARE.
//
//  Whilst the source is available for you to look at, you
//  do NOT have a licence to change or  re-distribute this
//  software.
//
//  This specific document, and the databases that accompany it
//  are licenced for the SOLE USE of Astrogen LLC, otherwise
//  known as FreePBX, to be distributed SOLEY with the FreePBX
//  software package.
//
//  If you wish to licence the redistribution of these
//  copyrighted documents, database, and database designes,
//  the ONLY company that is approved to do so is:

//    Why Pay More 4 Less Pty Ltd,
//    1 Grayson st,
//    Gladstone, QLD, 4680

//   If you do not have written permission from this company to
//   do so, you are violating international copyright laws, and
//   will be prosecuted to the full extent of the law.

//   You may be asking why this licence is so strict?  At the time
//   this was written, the Author believed that Fonatity was
//   involved in numerous GPL Violations with their Trixbox
//   product.  If and when that is ever resolved, this document
//   will be re-licenced under v2 of the GPL.


if (!function_exists('json_encode')) {
  function json_encode($a=false) {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)) {
      if (is_float($a)) { // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a)) {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      } else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    } else {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


$quietmode = true; // Turn off anything noisy in the included modules
include("../../../functions.inc.php"); // Load our common functions
$amp_conf = parse_amportal_conf('/etc/amportal.conf'); // Get the info from amportal.conf. FIXME
// FIXME - that line above should not be hard coded. How do I find out where it is?
include("../../../common/db_connect.php"); // Connect to DB
	

#$_GET['_id'] = 'region';
#$_GET['_value'] = 'NT|ALICE SPRINGS';
#$_GET['input'] = '7497';

if (isset($_GET['_id'])) {
  # This is a joined-list query..
  global $db;
  $json = array();
  if ($_GET['_id'] === 'state') {
	if (!isset($_GET['_value'])) { exit; }
	$q = sql("select distinct(state) from cidroute_cidlist order by state", "getAll", DB_FETCHMODE_ASSOC);
	$sname = $q[$_GET['_value']]['state'];
	$q = sql("select distinct(region) from cidroute_cidlist where state = '$sname' order by region", "getAll", DB_FETCHMODE_ASSOC);
	if (is_array($q)) {
		$i = 1;
		foreach ($q as $row) { 
			$json[] = array($sname.'|'.$row['region'] => $row['region']);	
		}
	} else { 
		$json[] = array(0 => 'No Regions'); 
	}
  } elseif ($_GET['_id'] === 'region') {
	if (!isset($_GET['_value'])) { exit; }
	$val = explode("|", $_GET['_value']);
	$val[0] = $db->escapeSimple($val[0]);
	$val[1] = $db->escapeSimple($val[1]);
	$q = sql("select localarea,min_numb,max_numb from cidroute_cidlist where region = '".$val[1]."' and state='".$val[0]."' order by localarea", "getAll", DB_FETCHMODE_ASSOC);
	if (is_array($q)) {
		$i = 1;
		foreach ($q as $row) { 
			$json[] = array($val[0]."|".$val[1]."|".$row['localarea']=> $row['localarea']." (".$row['min_numb']."-".$row['max_numb'].")");	
		}
	} else { 
		$json[] = array(0 => 'No Regions'); 
	}
  }	
  send_json($json);
} else {
  # This is a quickfind query..
	$input = strtolower( $_GET['input'] );
	$len = strlen($input);
	$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
	
	$json = array();
	$count = 0;
	$i = 0;

	// Have they typed in a number, looking for a range?
	if (is_numeric($input)) {
		$q= sql("select localarea,min_numb,max_numb from cidroute_cidlist where min_numb like '".$input."%' or max_numb like '".$input."%' limit 10", "getAll", DB_FETCHMODE_ASSOC);
		if (is_array($q)) {
			foreach($q as $row) {
				$json['results'][] = array( "id" => $i++, "value" => "Range:".$row['min_numb']."-".$row['max_numb'], "info" => "Area ".$row['localarea']);
			}
		}
	}
	// First. If the query is one or two characters, search for state..
	if (strlen($input) < 3) {
		$q = sql("select distinct(state) from cidroute_cidlist where state like '%".$db->escapeSimple($input)."%'","getAll", DB_FETCHMODE_ASSOC);
		if (is_array($q)) {
			foreach($q as $row) {
				$json['results'][] = array( "id" => $i++, "info" => "State", "value" => "State:".$row['state']);
			}
		}
	}

	// How about a region - don't return more than 5.

	$q = sql("select distinct(region) from cidroute_cidlist where region like '%".$db->escapeSimple($input)."%' limit 5","getAll", DB_FETCHMODE_ASSOC);

	if (is_array($q)) {
		foreach($q as $row) {
			$json['results'][] = array( "id" => $i++, "info" => "Region", "value" => "Region:".$row['region']);
		}
	}

	// Now, the exchanges
	$q = sql("select distinct(localarea) from cidroute_cidlist where localarea like '%".$db->escapeSimple($input)."%'","getAll", DB_FETCHMODE_ASSOC);

	if (is_array($q)) {
		foreach($q as $row) {
			$json['results'][] = array( "id" => $i++, "info" => "Local Area", "value" => "Area:".$row['localarea']);
		}
	}


	send_json($json);
}

function send_json($json) {

	header ("Expires: Mon, 23 Jul 1971 05:00:00 GMT"); // Send beer on this day!
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header ("Pragma: no-cache"); // HTTP/1.0
	header("Content-Type: application/json");
	

	print json_encode($json);
#	echo "{\"results\": [";
#	$arr = array();
#	for ($i=0;$i<count($json);$i++) {
#		$arr[] = "{\"id\": \"".$json[$i]['id']."\", \"value\": \"".$json[$i]['value']."\", \"info\": \"".$json[$i]['info']."\"}";
#	}
#	echo implode(", ", $arr);
#	echo "]}";
}
