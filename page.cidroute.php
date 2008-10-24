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
			cidroute_add($_POST);
			redirect_standard();
		break;
		case "alter":
			cidroute_alter($_POST);
			showEdit();
	#		needreload();
	#		redirect_standard();
		break;
		case "edit":
			showEdit();
	#		needreload();
	#		redirect_standard('itemid');
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
		if ($cidsource['destid'] != 0)
			echo "<li><a id=\"".($itemid==$cidsource['destid'] ? 'current':'')."\" href=\"config.php?display=$dispnum&action=edit&itemid=".$cidsource['destid']."\">{$cidsource['name']}</a></li>";
	}
}
?>
</ul></div>

<div class="content">
<?php
}
	
function showNew() {
	showHeader();
?>
	<p style="width: 80%"><?php echo ($itemid ? '' : _("This is where you maintain your Caller Location Routing interface. ")); ?></p>
	<p style="width: 80%"><?php echo ($itemid ? '' : _("Select a Name and Destination. You will be able to edit the mapping by clicking on the map name on the right.")); ?></p>

<form name="cidroutemap" action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="action" value="add">
	<input type="hidden" name="display" value="<? echo $dispnum; ?>">
        <table>
        <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Route Destination") : _("Add Route Destination")) ?><hr></h5></td></tr>
        <tr>
                <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The name of this route map")?></span></a></td>
                <td><input size="15" type="text" name="name" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>

        <tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>
	</table>

<?php
//draw goto selects
echo drawselects($dest,0);
?>

        <input name="Submit" type="submit" value="<?php echo _("Go"); ?>" tabindex="<?php echo ++$tabindex;?>">
	</form>

<?php		
}







function showEdit($itemid,$cidmaps) 
{

	showHeader();
	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
	<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
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
	<tr><td colspan=2><h2 id="title">Manage CID Groups</h2></td></tr>
	
<tr><td colspan=2>
<?php
	print "<h3 id='title'>"._("Route")." ". $cidmaps[$itemid-1]['name'].":</h2></td>";

?>
	<tr><td colspan=2><span id="instructions">Select the destination for the CID Groups below, then update the CallerID Groups to suit. Note that changes happen <em>immediately</em> without a need for a reload. (PS: No, they don't)</span></td></tr>
        <tr><td colspan=2><h5><?php echo _("Destination")?>:<hr></h5>

<?php
//draw goto selects
echo drawselects($dest,0);
?>
	</td></tr>
	<tr><td colspan=2><hr></td></tr>
	<tr><td>
	<script type="text/javascript" src="modules/cidroute/js/chainedSelects.js" charset="utf-8"></script>
	<script type="text/javascript" src="modules/cidroute/js/autosuggest.js" charset="utf-8"></script>


	<link rel="stylesheet" href="modules/cidroute/js/autosuggest.css" type="text/css" media="screen" charset="utf-8" />

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
});
	</script>
	<div>
	<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" class="asholder">
<? 
global $itemid;
global $dispnum;
?> 
        <input type="hidden" name="itemid" value="<? echo $itemid ?>">
        <input type="hidden" name="action" value="alter">
	<input type="hidden" name="display" value="<? echo $dispnum; ?>">

	Quick Select:</td><td>
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
	<tr><td><input type="submit" name="addselect" value="submit" /></td></tr>
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
?>
