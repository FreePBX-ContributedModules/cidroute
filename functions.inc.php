<?php /* $Id */
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


function cidroute_list() {
	$results = sql("SELECT * FROM cidroute_dests","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$allowed[] = $result;
		}
	}
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
	global $db;
	print_r($post);
	$command = "INSERT INTO cidroute_dests (name, dest) VALUES ('".$db->escapeSimple($post['name'])."','";
	$command .= $db->escapeSimple($post[$post['goto0'].'0'])."')";
	$res = sql($command);
}

function cidroute_alter($post){
	if (isset($post['addquick'])) {
		// Check for Range: Area: State:
		$defn = explode(":", $post['quick']);
		print_r($defn);
		if (strcasecmp($defn[0], 'Range') === 0) {
			print "<br>Adding range ".$defn[1]."\n";
		} elseif (strcasecmp($defn[0], "Area") === 0) {
			print "<br>Adding Area ".$defn[1]."\n";
		} elseif (strcasecmp($defn[0], "State") === 0) {
			print "<br>Adding State ".$defn[1]."\n";
		} elseif (strcasecmp($defn[0], "Region") === 0) {
			print "<br>Adding region ".$defn[1]."\n";
		}
	} elseif (isset($post['area'])) {
	}
}

// ensures post vars is valid
function cidroute_chk($post){
	// TODO: Add sanity checks on $_POST
	return true;
}
?>
