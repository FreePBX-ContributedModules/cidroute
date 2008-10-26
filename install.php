<?php

global $db;
global $amp_conf;

if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

// create the tables
$sql = "CREATE TABLE IF NOT EXISTS cidroute_cidlist (
  country varchar(2) NOT NULL default '',
  state varchar(10) NOT NULL default '',
  region varchar(50) NOT NULL default '',
  localarea varchar(50) NOT NULL default '',
  areacode int(11) unsigned NOT NULL,
  min_numb int(11) unsigned NOT NULL,
  max_numb int(11) unsigned NOT NULL,
  postcode varchar(12) default '',
  KEY idx_country (country),
  KEY idx_numb (min_numb,max_numb)
);";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `cid` table: " . $check->getMessage() .  "\n");
}


$sql = "CREATE TABLE IF NOT EXISTS cidroute_matches (
  name varchar(50) default '',
  country varchar(2) NOT NULL default '',
  areacode int(11) unsigned NOT NULL,
  min_numb int(11) unsigned NOT NULL,
  max_numb int(11) unsigned NOT NULL,
  dest varchar(12) default '',
  KEY idx_country (country),
  KEY idx_numb (min_numb,max_numb)

);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `cidroute_matches` table: " . $check->getMessage() .  "\n");
}

$sql = "CREATE TABLE IF NOT EXISTS cidroute_override (
  name varchar(20) NOT NULL default '',
  number int(11) unsigned NOT NULL,
  dest varchar(12) default ''
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `cidroute_override` table: " . $check->getMessage() .  "\n");
}

$sql = "CREATE TABLE IF NOT EXISTS cidroute_config (
  trunk varchar(20) NOT NULL default '',
  enabled boolean NOT NULL
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `cidroute_override` table: " . $check->getMessage() .  "\n");
}

$sql = "CREATE TABLE IF NOT EXISTS cidroute_dests (
   destid int(11) not null auto_increment,    
   name varchar(50) NOT NULL default '',    
   dest varchar(50) NOT NULL default '',    
   primary key (destid) 
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `cidroute_override` table: " . $check->getMessage() .  "\n");
}

?>
