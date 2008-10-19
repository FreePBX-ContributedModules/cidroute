<?php /* $Id */
//Copyright (C) Rob Thomas <xrobau@gmail.com> - Why Pay More 4 Less Pty Ltd (Australia) 2008
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of version 2 of the GNU General Public 
//License as published by the Free Software Foundation.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

function cidroute_hook_core($viewing_itemid, $target_menuid) {
	$html = '';
	if ($target_menuid == 'did')	{
		global $db;
		// Figure out if CIDroute is enabled for this trunk here...
		$results = sql("SELECT * FROM cidroute_config WHERE trunk = '".$db->escapeSimple($viewing_itemid)."'","getRow");
		$cidroute = $results[1];

		// Draw the HTML
		$html = '<tr><td colspan="2"><h5>';
		$html .= _("Caller Location Routing");
		$html .= '<hr></h5></td></tr>';
		$html .= '<tr><td>';
		$html .= 'Note that this OVERRIDES the destination below if selected<br>';
		$html .= "</td></tr>";
		$html .= '<td><a href="#" class="info">';
		$html .= _("Enabled").'<span>'._("If enabled, this will set the destination of the call to what is specified in the Caller Location Routing page, and will over-ride any further settings. If it does NOT match, it will proceed as per normal.").".</span></a>:</td>\n";
		$html .= '<td><select name="cidroute"><option value="0" '.($cidroute == '0' ? 'SELECTED' : '').">"._("No")."</option>\n";
		$html .= '<option value="1" '.($cidroute == '1' ? 'SELECTED' : '').'>'._("Yes")."</option>\n</select></td>";
		$html .= '</td></tr>';
	}

	return $html;
	
}


function cidroute_hookProcess_core($viewing_itemid, $request) {
	
	if (!isset($request['action']))
		return;
	global $db;
	switch ($request['action'])	{
		case 'addIncoming':
			$results = sql(sprintf('INSERT INTO cidroute_config (trunk, enabled) VALUES ("%s", %d)', 
				$db->escapeSimple($request['extdisplay']), $request['cidroute']));
		break;
		case 'delIncoming':
			$results = sql(sprintf("DELETE FROM cidroute_config WHERE trunk = '%s'",
				$db->escapeSimple($request['extdisplay']))); 
		break;
		case 'edtIncoming':	// deleting and adding as in core module
			$results = sql(sprintf("DELETE FROM cidroute_config WHERE trunk = '%s'",
				$db->escapeSimple($request['extdisplay']))); 
			$results = sql(sprintf('INSERT INTO cidroute_config (trunk, enabled) VALUES ("%s", %d)', 
				$db->escapeSimple($request['extdisplay']), $request['cidroute']));
		break;
	}
}


function cidroute_hookGet_config($engine) {
	global $ext;  
//	switch($engine) {
//		case "asterisk":
//			$pairing = cidroute_did_list();
//			if(is_array($pairing)) {
//				foreach($pairing as $item) {
//					if ($item['cidroute_id'] != 0) {
//
//						// Code from modules/core/functions.inc.php core_get_config inbound routes
//						$exten = trim($item['extension']);
//						$cidnum = trim($item['cidnum']);
//						
//						if ($cidnum != '' && $exten == '') {
//							$exten = '_.';
//							$pricid = ($item['pricid']) ? true:false;
//						} else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
//							$pricid = true;
//						} else {
//							$pricid = false;
//						}
//						$context = ($pricid) ? "ext-did-0001":"ext-did-0002";
//
//						$exten = (empty($exten)?"s":$exten);
//						$exten = $exten.(empty($cidnum)?"":"/".$cidnum); //if a CID num is defined, add it
//
//						$ext->splice($context, $exten, 1, new ext_gosub('1', 'cidroute_'.$item['cidroute_id'], 'cidroute'));
//					
//					}
//				}
//			}
//		break;
//	}
}

/*
// 	Generates dialplan for cidroute, call from retrieve_conf
*/

function cidroute_get_config($engine) {
	global $ext; 
	global $asterisk_conf;
//	switch($engine) {
//		case "asterisk":
//			$sources = cidroute_list();
//			if(is_array($sources)) {
//				foreach($sources as $item) {
//
//					// Search for number in the cache, if found lookupcidnum and return
//					if ($item['cidroute_id'] != 0)	{
//						if ($item['cache'] == 1 && $item['sourcetype'] != 'internal') {
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_gotoif('$[${DB_EXISTS(cidname/${CALLERID(num)})} = 1]', 'cidroute,cidroute_return,1'));
//						}
//					}
//
//					switch($item['sourcetype']) {
//
//						case "internal":
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_lookupcidname(''));
//						break;
//
//						case "enum":
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_txtcidname('${CALLERID(num)}'));
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_setvar('CALLERID(name)', '${TXTCIDNAME}'));
//						break;
//
//						case "http":
//							if (!empty($item['http_username']) && !empty($item['http_password']))
//								$auth = sprintf('%s:%s@', $item['http_username'], $item['http_password']);
//							else
//								$auth = '';
//								
//							if (!empty($item['http_port']))
//								$host = sprintf('%s:%d', $item['http_host'], $item['http_port']);
//							else
//								$host = $item['http_host'].':80';
//
//							if (substr($item['http_path'], 0, 1) == '/')
//								$path = substr($item['http_path'], 1);
//							else
//								$path = $item['http_path'];
//								
//							$query = str_replace('[NUMBER]', '${CALLERID(num)}', $item['http_query']);
//							$url = sprintf('http://%s%s/%s?%s', $auth, $host, $path, $query);
//							$curl = sprintf('${CURL(%s)}', $url);
//							
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_setvar('CALLERID(name)', $curl));
//						break;
//
//						case "mysql":
//							//Escaping MySQL query - thanks to http://www.asteriskgui.com/index.php?get=utilities-mysqlscape
//
//							$replacements = array (
//							  	'\\' => '\\\\',
//							  	'"' => '\\"',
//							  	'\'' => '\\\'',
//							  	' ' => '\\ ',
//							  	',' => '\\,',
//							  	'(' => '\\(',
//							  	')' => '\\)',
//							  	'.' => '\\.',
//							  	'|' => '\\|'
//							);
//							
//							$query = str_replace(array_keys($replacements), array_values($replacements), $item['mysql_query']);
//							$query = str_replace('[NUMBER]', '${CALLERID(num)}', $query);
//
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_mysql_connect('connid', $item['mysql_host'],  $item['mysql_username'],  $item['mysql_password'],  $item['mysql_dbname']));							
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_mysql_query('resultid', 'connid', $query));
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_mysql_fetch('fetchid', 'resultid', 'CALLERID(name)'));
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_mysql_clear('resultid'));							
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_mysql_disconnect('connid'));
//						break;
//
//						// TODO: implement SugarCRM lookup, look at code snippet at http://nerdvittles.com/index.php?p=82
//						case "sugarcrm":
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_noop('SugarCRM not yet implemented'));
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_return(''));
//						break;
//					}
//
//					// Put numbers in the cache
//					if ($item['cidroute_id'] != 0)	{
//						if ($item['cache'] == 1 && $item['sourcetype'] != 'internal') {
//							$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_db_put('cidname', '${CALLERID(num)}', '${CALLERID(name)}' ));
//						}
//						$ext->add('cidroute', 'cidroute_'.$item['cidroute_id'], '', new ext_return(''));
//					}
//				}
//
//				$ext->add('cidroute', 'cidroute_return', '', new ext_lookupcidname(''));
//				$ext->add('cidroute', 'cidroute_return', '', new ext_return(''));
//			}
//		break;
//	}
}


function cidroute_did_get($did){
//	$extarray = explode('/', $did, 2);
//	if(count($extarray) == 2)	{ // differentiate beetween '//' (Any did / any cid and '' empty string)
//		$sql = sprintf("SELECT cidroute_id FROM cidroute_incoming WHERE extension = '%s' AND cidnum = '%s'", $extarray[0], $extarray[1]);
//		$result = sql($sql, "getRow", DB_FETCHMODE_ASSOC);
//		if(is_array($result)){
//			return $result['cidroute_id'];
//		} else
//			return null;
//	} else { // $did is an empty string (for example when adding a new did)
		return 0;
//	}
}

function cidroute_did_list() {
//	$sql = "
//	SELECT cidroute_id, a.extension extension, a.cidnum cidnum, pricid FROM cidroute_incoming a 
//	INNER JOIN incoming b
//	ON a.extension = b.extension AND a.cidnum = b.cidnum
//	";
//
//	//$results = sql("SELECT * FROM cidroute_incoming","getAll",DB_FETCHMODE_ASSOC);
//	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	return is_array($results)?$results:null;
}

function cidroute_list() {
	// TODO: discuss department isolation of sources
//	$allowed = array(array('cidroute_id' => 0, 'description' => _("None"), 'sourcetype' => null));
//	$results = sql("SELECT * FROM cidroute","getAll",DB_FETCHMODE_ASSOC);
//	if(is_array($results)){
//		foreach($results as $result){
//			// check to see if we have a dept match for the current AMP User.
//			if (checkDept($result['deptname'])){
//				// return this item
//				$allowed[] = $result;
//			}
//		}
//	}
	return isset($allowed)?$allowed:null;
}

function cidroute_get($id){
//	$results = sql("SELECT * FROM cidroute WHERE cidroute_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function cidroute_del($id){
	// Deleting source and its associations
//	$results = sql("DELETE FROM cidroute WHERE cidroute_id = '$id'","query");
//	$results = sql("DELETE FROM cidroute_incoming WHERE cidroute_id = '$id'","query");
}

function cidroute_add($post){
//	if(!cidroute_chk($post))
//		return false;
//	extract($post);
//	if (!isset($cache))
//		$cache = 0;
//	$results = sql("
//		INSERT INTO cidroute
//			(description, sourcetype, cache, deptname, http_host, http_port, http_username, http_password, http_path, http_query, mysql_host, mysql_dbname, mysql_query, mysql_username, mysql_password)
//		VALUES 
//			(\"$description\", \"$sourcetype\", \"$cache\", \"$deptname\", \"$http_host\", \"$http_port\", \"$http_username\", \"$http_password\", \"$http_path\", \"$http_query\", \"$mysql_host\", \"$mysql_dbname\", \"$mysql_query\", \"$mysql_username\", \"$mysql_password\")
//		");
}

function cidroute_edit($id,$post){
//	if(!cidroute_chk($post))
//		return false;
//	extract($post);
//	if ($cache != 1)
//		$cache = 0;
//	$results = sql("
//		UPDATE cidroute 
//		SET 
//			description = \"$description\", 
//			deptname = \"$deptname\", 
//			sourcetype = \"$sourcetype\" ,
//			cache = \"$cache\",
//			http_host = \"$http_host\",
//			http_port = \"$http_port\",
//			http_username = \"$http_username\",
//			http_password = \"$http_password\",
//			http_path = \"$http_path\",
//			http_query = \"$http_query\",
//			mysql_host = \"$mysql_host\",
//			mysql_dbname = \"$mysql_dbname\",
//			mysql_query = \"$mysql_query\",
//			mysql_username = \"$mysql_username\",
//			mysql_password  = \"$mysql_password\"
//		WHERE cidroute_id = \"$id\"");
}

// ensures post vars is valid
function cidroute_chk($post){
	// TODO: Add sanity checks on $_POST
	return true;
}
?>
