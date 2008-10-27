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


$tabindex = 0;
// What are we doing..
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

// Where we are
$dispnum = "cidroute"; //used for switch on config.php

$cidmaps = cidroute_list();

//if submitting form, update database
if(isset($_REQUEST['action'])) {
	print "I have an action\n";
	print_r($_REQUEST);
	switch ($action) {
		case "add":
			cidroute_add($_REQUEST);
			redirect_standard();
		break;
		case "alter":
			cidroute_alter($_REQUEST);
			showEdit($itemid,$cidmaps);
		break;
		case "delmaps":
			cidroute_delmaps($_REQUEST);
			showEdit($itemid,$cidmaps);
		break;
		case "edit":
			showEdit($itemid,$cidmaps);
		break;
		case "override":
			showOverride();
		break;
		case "*":
			showNew();
		break;
	}
} else {
	showNew();
}

//get list of callerid lookup sources

function showHeader() {
	global $itemid, $dispnum, $cidmaps;
	?>

</div> <!-- end content div so we can display rnav properly-->

<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Route Map")?></a></li>
<?php
if (isset($cidmaps)) {
	foreach ($cidmaps as $cidsource) {
		if ($cidsource['destid'] != 0) {
			echo "<li><a id=\"".($itemid==$cidsource['destid'] ? 'current':'')."\" href=\"config.php?display=";
			echo urlencode($dispnum)."&action=edit&itemid=".$cidsource['destid']."\">{$cidsource['name']}</a></li>";
		}
	}
}
?>
<li><a href="config.php?display=<?php echo urlencode($dispnum); ?>&action=override">Manage Overrides</a></li>
</ul></div>

<div class="content">
<?php
}
	
function showNew() {
	global $dispnum;
	showHeader();
?>
	<h3 id='title'><?php echo _("Caller Location Based Routing") ?></h2></td>
	<tr><td colspan=2><span id="instructions">
<?php
	echo "<p>"._("Caller Location Based Routing"); echo " ";
	echo _("lets you set the desination of the call based on the CallerID of the Caller."); echo " ";
	echo _(" This is used to provide caller-location-dependent services, such as may be used in a franchise of multiple stores that want to provide the one public phone number, and route calls dependant on the callers location."); echo "</p>";
	echo "<p><em>"._("Note that this module, unlike most others, requires the database to be available AT ALL TIMES."); echo "</em></p>";
	// Yes, this isn't translated. There's no use translating it yet, because Australia only speaks English.
	// Take it out when we've actually got some other exchanges!
	echo "<p>This module currently only supports AUSTRALIA. Other countries will follow as soon as the exchange mappings are provided</p>";
	echo "<p>"._("You enable or disable the Caller Location Based Routing on the Inbound Routes page.")." ";
	echo _("When that is enabled, calls are first checked against the list of CallerID Ranges here.")." ";
	echo _("If the Caller ID matches any one of the ranges, the destination of the call is changed to what is specified on each group.")." ";
	echo _("If the Caller ID of the call does not match any of the selected ones here, the call proceeds to the desination selected in Inbound Routes."); ?>
</p>

<form name="cidroutemap" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="action" value="add">
	<input type="hidden" name="display" value="<?php echo $dispnum; ?>">
        <table>
	<tr><td style="width:30%"></td><td style="width:70%"></td>
        <tr><td colspan="2"><h5><?php echo ($extdisplay ? _("Edit Route Destination") : _("Add Route Destination")) ?><hr></h5></td></tr>
        <tr>
                <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The name of this route map")?></span></a></td>
                <td><input size="15" type="text" name="name" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>

        <tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>
	</table>

<?php
//draw goto selects
echo drawselects(0,0);
?>

        <input name="Submit" type="submit" value="<?php echo _("Go"); ?>" tabindex="<?php echo ++$tabindex;?>">
	</form>

<?php		
}







function showEdit($itemid,$cidmaps) 
{

	global $db;
	showHeader();
	$delButton = "<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
	<input type=\"hidden\" name=\"display\" value=\"cidroute\">
	<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
	<input type=\"hidden\" name=\"action\" value=\"delete\">
	<input type=submit value=\""._("Delete Route Map")."\">
</form>";
?>
<style>
#loading
{
        position:absolute;
        top:0px;
        right:0px;
        background:#ff0000;
        color:#fff;
        font-size:14px;
        font-familly:Arial;
        padding:2px;
        display:none;
}
</style>

<div id="loading">Loading ...</div>
<table>
	<tr><td style="width:10%"></td><td style="width:90%"></td>
	<tr><td colspan=2><h2 id="title">Manage CID Groups</h2></td></tr>
	
<tr><td colspan=2>
<?php
	print "<h3 id='title'>"._("Route")." ". $cidmaps[$itemid-1]['name'].":</h2></td>";

?>
	<tr><td colspan=2><span id="instructions">Select the destination for the CID Groups below, then update the CallerID Groups to suit. Note that changes to the routing happen as soon as you submit the form. There is no need for a standard reload.</span></td></tr>
        <tr><td colspan=2><h5><?php echo _("Destination")?>:<hr></h5>
        <input type="hidden" name="itemid" value="<? echo $itemid ?>">
        <input type="hidden" name="action" value="alter">
	<input type="hidden" name="display" value="cidroute">

	<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" class="asholder">
<?php
//draw goto selects
	$dest=sql("select dest from cidroute_dests where destid='".$db->escapeSimple($itemid)."'", "getRow");
	if (isset($dest[0])) {
		echo drawselects($dest[0],0);
	} else {
		echo drawselects(0,0);
	}
		
?>
	</td></tr>
	<tr><td><input type="submit" name="updatedest" value="Change Destination" /></td></tr>
	<tr><td colspan=2><hr>
        <script type="text/javascript" src="modules/cidroute/js/comboselect.js" charset="utf-8"></script>
	<script type="text/javascript" src="modules/cidroute/js/chainedSelects.js" charset="utf-8"></script>
	<script type="text/javascript" src="modules/cidroute/js/autosuggest.js" charset="utf-8"></script>
 

	<link rel="stylesheet" href="modules/cidroute/js/autosuggest.css" type="text/css" media="screen" charset="utf-8" />
	<link rel="stylesheet" href="modules/cidroute/js/comboselect.css" type="text/css" media="screen" charset="utf-8" />


<script language="JavaScript" type="text/javascript">
$(function()
{
        $('#state').chainSelect('#region','modules/cidroute/js/ajax.php',
        {
                before:function (target) //before request hide the target combobox and display the loading message
                {
                        $("#loading").css("display","block");
                        $(target).css("display","none");
                },
                after:function (target) //after request show the target combobox and hide the loading message
                {
                       $("#loading").css("display","none");
                        $(target).css("display","inline");
                }
        });
        $('#region').chainSelect('#area','modules/cidroute/js/ajax.php',
        {
                before:function (target)
                {
                        $("#loading").css("display","block");
                        $(target).css("display","none");
                },
                after:function (target)
                {
                        $("#loading").css("display","none");
                        $(target).css("display","inline");
                }
        });
	$(document).ready(function() {
                $('#myselect').comboselect({ sort: 'both', addbtn: '&gt;&gt;',  rembtn: '&lt;&lt;' });
	});

});
	</script>
	<div>
	<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" class="asholder">
<? 
global $itemid;
global $dispnum;
global $db;
?> 
	<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" class="asholder">
        <input type="hidden" name="itemid" value="<? echo $itemid ?>">
        <input type="hidden" name="action" value="alter">
	<input type="hidden" name="display" value="<? echo $dispnum; ?>">
	</td></tr>
	<tr><td>Quick Select:</td><td>
	<input style="width: 200px" type="text" id="quick" name="quick" value="" /> &nbsp; <input type="submit" name="addquick" value="Add" /></td></tr>
	<tr><td>State:</td><td> <select id="state" name="state">
	<option id="0">Select State</option>
<?
	$res = sql("select distinct(state) from cidroute_cidlist order by state", "getAll", DB_FETCHMODE_ASSOC);
	$tmp = 0;
        if(is_array($res)){
                foreach($res as $result){
			print "<option value='".$tmp++."'>{$result['state']}</option>\n";
                }
        }
?>
	</select></td></tr>
	<tr><td>Region:</td><td>
	<!-- region is chained by state combobox-->
	<select name="region" id="region"><option value="0">--</option></select></td></tr>
       	<!-- area is chained by region combobox-->
	<tr><td>Area:</td><td>
       	<select name="area" id="area"><option value="0">--</option></select></td></tr>
	<tr><td><input type="submit" name="addselect" value="Submit" /></td></tr>
	<tr><td><?php echo _('Currently Selected Areas'); ?></td>
 		<td style="text-align: right"><i>Move areas to right hand side to remove</i></td></tr>
	
	<tr><td colspan=2>
	</form>
	
	
	<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" class="removestuff">
        <input type="hidden" name="itemid" value="<? echo $itemid ?>">
        <input type="hidden" name="action" value="delmaps">
	<input type="hidden" name="display" value="<? echo $dispnum; ?>">
	<select id="myselect" name="myselect[]" multiple="multiple">
<?
	$q = "select state, region, localarea, cidroute_cidlist.areacode, cidroute_matches.min_numb, cidroute_matches.max_numb ";
	$q .= "from cidroute_cidlist right join cidroute_matches using (min_numb,max_numb) where cidroute_matches.dest='";
	$q .= $db->escapeSimple($itemid)."'";
	$result = sql($q, "getAll", DB_FETCHMODE_ASSOC);
        if(is_array($result)){
                foreach($result as $res){
			$var = $res['min_numb']."|".$res['max_numb'];
			$desc = $res['state']."/".$res['region']."/".$res['localarea']." (".$res['min_numb']."-".$res['max_numb'].")";
			print "<option value='".$var."'>$desc</option>\n";
                }
        }
?>
	
	
	</select>
	</tr></td>
	<tr><td colspan=2 style="text-align: right"><input type="submit" name="delselect" value="Remove Selected" /></td></tr>
	</form>


	</table>
	</div>
	<script type="text/javascript">
 var options = { script: "modules/cidroute/js/ajax.php?type=global&limit=10&", 
	varname:"input", json: true, shownowresults:true, maxresults:10 };
 var as = new bsn.AutoSuggest('quick', options);
	</script>


<?php
} 



function showOverride() {
	global $dispnum;
	showHeader();
	
	print "<h2>Not implemented yet</h2>";
	
}
?>
