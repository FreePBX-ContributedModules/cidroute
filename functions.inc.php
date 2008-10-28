<?php /* $Id */
//Copyright (C) Rob Thomas <xrobau@gmail.com> - 
//    Why Pay More 4 Less Pty Ltd (Australia) 2008
//
//       THIS IS NOT OPEN SOURCE SOFTWARE.
//
//  Whilst the source is available for you to look at, you 
//  do NOT have a licence to change or re-distribute this 
//  software. 

//  This specific document, all other associated files, any 
//  databases, and the database designs that accompany it
//  are Copyright 2008, and  are licenced for the SOLE USE of 
//  Astrogen LLC, otherwise known as FreePBX, to be 
//  distributed SOLEY with the FreePBX software package.

//  If you wish to licence the redistribution of these
//  copyrighted documents, database, and database designs, 
//  the ONLY company that is able to do so is:

//    Why Pay More 4 Less Pty Ltd,
//    1 Grayson st,
//    Gladstone, QLD, 4680, AUSTRALIA
//
//   PHYSICAL MAIL only (Electronic mail is not acceptable)

//   If you do not have written permission from this company to
//   redistribute this copyrighted package and do so, you are 
//   violating international copyright laws, and will be 
//   prosecuted to the FULL EXTENT of the law.

//   You may be asking why this licence is so strict?  At the time
//   this was written, the Author believed that Fonality was
//   involved in numerous GPL Violations with their Trixbox
//   product.  If and when that is ever resolved, this document
//   will be re-licenced under v2 of the GPL.


// Stick this in the DID page.
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

function cidroute_hookGet_config($engine) {
	global $ext;  
	switch($engine) {
		case "asterisk":
			$trunks = cidroute_get_trunks();
			// Returns ("12345/", "23456/", "/");
			if(is_array($trunks)) {
				foreach($trunks as $trunk) {
					$arr = explode("/", $trunk[0]);
					print_r($arr);
					if (empty($arr[0])) {
						$dest = "_.";
					} else {
						$dest = $arr[0];
					}
					if (empty($arr[1])) {
						$cid = ""; 
					} else {
						$cid = "/".$arr[1];
					}
					$context = "ext-did-0002";
                                        $exten = "$dest$cid";
					$ext->splice($context, $exten, 1, new ext_agi('cidrouting.agi'));

				}
			}
		break;
	}
}

function cidroute_get_trunks() {
	$res = sql("select trunk from cidroute_config where enabled='1'", "getAll");
	if (is_array($res)) {
		foreach ($res as $r) {
			$arr[]=$r[0];
		}
		return $res;
	}
	return null;
}
	
	

function cidroute_list() {
	$results = sql("SELECT * FROM cidroute_dests","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$dests[] = $result;
		}
	}
	return isset($dests)?$dests:null;
}

function cidroute_del($post){
	global $db;
	$itemid = $db->escapeSimple($post['itemid']);
	// Delete any references to the map in matches...
	$results = sql("DELETE FROM cidroute_matches WHERE dest = '$itemid'","query");
	// Delete any references to the map in overrides...
	$results = sql("DELETE FROM cidroute_override WHERE dest = '$itemid'","query");
	// Delete the map..
	$results = sql("DELETE FROM cidroute_dests WHERE destid = '$itemid'","query");
	// All gone!
}

function cidroute_add($post){
	global $db;
	print_r($post);
	$command = "INSERT INTO cidroute_dests (name, dest) VALUES ('".$db->escapeSimple($post['name'])."','";
	$command .= $db->escapeSimple($post[$post['goto0'].'0'])."')";
	$res = sql($command);
}

function cidroute_alter($post){
	global $db;
	if (isset($post['addquick'])) {
		// Check for Range: Area: State:
		$defn = explode(":", $post['quick']);
		print_r($defn);
		if (strcasecmp($defn[0], 'Range') === 0) {
			$res = explode("-", $defn[1]);
			print "<br>Adding range ".$defn[1]."\n";
			$q = "select count(min_numb) from cidroute_cidlist where min_numb = '".$db->escapeSimple($res[0])."'";
			print "Doing $q<br>\n";
			$ret = sql($q,"getRow");
			print_r($ret);
			if ($ret[0] == 0) {
				// This is a manual range entry.. So don't know the area code
				$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) values ";
				$q .= "('au', '99', '".$db->escapeSimple($res[0])."', '".$db->escapeSimple($res[1])."', ";
				$q .= $db->escapeSimple($post['itemid']).", 'Manual Range')";
			} else {
				$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) select ";
				$q .= "country, areacode, min_numb, max_numb, ".$db->escapeSimple($post['itemid']).", ";
				$q .= "localarea as name from cidroute_cidlist where min_numb = '".$db->escapeSimple($res[0]);
				$q .= "' and max_numb = '".$db->escapeSimple($res[1])."'";
			}
			print "Doing $q<br>\n";
			sql($q);
		} elseif (strcasecmp($defn[0], "Area") === 0) {
			$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) select ";
			$q .= "country, areacode, min_numb, max_numb, ".$db->escapeSimple($post['itemid']).", localarea ";
			$q .= "as name from cidroute_cidlist where localarea = '".$db->escapeSimple($defn[1])."'";
			sql($q);
		} elseif (strcasecmp($defn[0], "State") === 0) {
			print "<br>Adding State ".$defn[1]."\n";
			$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) select ";
			$q .= "country, areacode, min_numb, max_numb, ".$db->escapeSimple($post['itemid']).", localarea ";
			$q .= "as name from cidroute_cidlist where state = '".$db->escapeSimple($defn[1])."'";
			sql($q);
		} elseif (strcasecmp($defn[0], "Region") === 0) {
			print "<br>Adding region ".$defn[1]."\n";
			$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) select ";
			$q .= "country, areacode, min_numb, max_numb, ".$db->escapeSimple($post['itemid']).", localarea ";
			$q .= "as name from cidroute_cidlist where region = '".$db->escapeSimple($defn[1])."'";
			sql($q);
		}
	} elseif (isset($post['updatedest'])) {
		$command = "update cidroute_dests set dest = '";
		$command .= $db->escapeSimple($post[$post['goto0'].'0'])."' where destid='";
		$command .= $db->escapeSimple($post['itemid'])."'";
		sql($command);
	} elseif (isset($post['area'])) {
		$reg = explode("|", $post['area']);
		$q = "insert into cidroute_matches (country, areacode, min_numb, max_numb, dest, name) select ";
		$q .= "country, areacode, min_numb, max_numb, ".$db->escapeSimple($post['itemid']).", localarea ";
		$q .= "as name from cidroute_cidlist where state = '".$db->escapeSimple($reg[0])."' and region = '";
		$q .= $db->escapeSimple($reg[1])."' and localarea='".$db->escapeSimple($reg[2])."'";
		print "Doing $q<br>\n";
		sql($q);
	}
}

function cidroute_delmaps($post){ 
	global $db; 
	if (isset($post['myselect'])) { 
		// Javascript combo box didn't work.. 
			$res = $post['myselect']; 
		} elseif (isset($post['myselect_right'])) { 
			$res = $post['myselect_right']; 
		} else { 
			return; 
		} 
	foreach ($res as $r) { 
		$arr = explode("|", $r); 
		$q = "delete from cidroute_matches where min_numb='".$arr[0]."' and max_numb='".$arr[1]."'"; 
		sql($q); 
	}        
} 
?>

