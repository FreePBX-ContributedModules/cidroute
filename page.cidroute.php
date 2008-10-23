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

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

$dispnum = "cidroute"; //used for switch on config.php

//if submitting form, update database
	print_r($_REQUEST);
if(isset($_POST['action'])) {
	print "I have a post\n";
	switch ($action) {
		case "add":
			cidroute_add($_POST);
			redirect_standard();
		break;
		case "delete":
			cidroute_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			cidroute_edit($itemid,$_POST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of callerid lookup sources
$cidsources = cidroute_list();
?>

</div> <!-- end content div so we can display rnav properly-->

<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Route Map")?></a></li>
<?php
if (isset($cidsources)) {
	foreach ($cidsources as $cidsource) {
		if ($cidsource['cidroute_id'] != 0)
			echo "<li><a id=\"".($itemid==$cidsource['cidroute_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($cidsource['cidroute_id'])."\">{$cidsource['description']}</a></li>";
	}
}
?>
</ul></div>

<div class="content">
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Route map").' '.$itemid.' '._("deleted").'!</h3>';
} else {
	if ($itemid){ 
		//get details for this source
		$thisItem = cidroute_get($itemid);
	} else {
		$thisItem = Array( 'description' => null, 'destid' => null);
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
				<input type=\"hidden\" name=\"action\" value=\"delete\">
				<input type=submit value=\""._("Delete Route Map")."\">
			</form>";
	
?>

	<h2><?php echo ($itemid ? _("Route:")." ". $itemid : _("Create Route")); ?></h2>



<?
#	<script type="text/javascript" src="modules/cidroute/js/autosuggest.js" charset="utf-8"></script>
#
#	<div>
#	<link rel="stylesheet" href="modules/cidroute/js/autosuggest.css" type="text/css" media="screen" charset="utf-8" />
#<form method="get" action="" class="asholder">
#	<label for="region">Enter Area</label>
#
#	<input style="width: 200px" type="text" id="testinput" value="" /> 
#	<input type="submit" value="submit" />
#</form>
#</div>
#	<script type="text/javascript"> var options = { script: "modules/cidroute/js/ajax.php?type=global&limit=10&", 
#			varname:"input",
#			json: true, 
#			shownowresults:true,
#			maxresults:10 };
#	var as = new bsn.AutoSuggest('region', options);
#	</script>
?>


	
	<p style="width: 80%"><?php echo ($itemid ? '' : _("This is where you maintain your Caller Location Routing interface. ")); ?></p>
	<p style="width: 80%"><?php echo ($itemid ? '' : _("Select a Name and Destination. You will be able to edit the mapping by clicking on the map name on the right.")); ?></p>

        <form name="cidroute" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="action" value="add">
<input name="Submit" type="submit" value="<?php echo _("Go"); ?>" tabindex="<?php echo ++$tabindex;?>">
</form>
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
//echo drawselects($dest,0);
?>


<?php		
} //end if action == delete
?>
