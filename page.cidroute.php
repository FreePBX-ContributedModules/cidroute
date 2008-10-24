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


$tabindex = 0;
// What are we doing..
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

// Where we are
$dispnum = "cidroute"; //used for switch on config.php

//if submitting form, update database
if(isset($_POST['action'])) {
	print "I have a post\n";
	print_r($_POST);
	switch ($action) {
		case "add":
			cidroute_add($_POST);
			redirect_standard();
		break;
		case "delete":
	#		cidroute_del($itemid);
	#		needreload();
	#		redirect_standard();
		break;
		case "edit":
	#		cidroute_edit($itemid,$_POST);
	#		needreload();
	#		redirect_standard('itemid');
		break;
	}
}

//get list of callerid lookup sources
$cidmaps = cidroute_list();
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
if ($action == 'delete') {
	echo '<br><h3>'._("Route map").' '.$itemid.' '._("deleted").'!</h3>';
} elseif ($action == 'edit' ) {

	if (isset($itemid)) { 
		showEdit($itemid, $cidmaps);
	} else {
		showNew();
	}
} else {
	showNew();
}


	
function showNew() {
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

function showEdit($itemid,$cidmaps) {

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
	<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
	<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
	<input type=\"hidden\" name=\"action\" value=\"delete\">
	<input type=submit value=\""._("Delete Route Map")."\">
</form>";
	
	print "<h2>"._("Route")." ". $cidmaps[$itemid-1]['name'].":</h2>";

?>
	<script type="text/javascript" src="modules/cidroute/js/chainedSelects.js" charset="utf-8"></script>
	<script type="text/javascript" src="modules/cidroute/js/autosuggest.js" charset="utf-8"></script>
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
	<form method="get" action="" class="asholder">
		<label for="quick">Quick Selection:</label>
		<input style="width: 200px" type="text" id="quick" value="" /> 
		<input type="submit" value="submit" />
	</form>
	</div>


	<div>
	<form name="formname" method="post" action="">
        <!-- country combobox -->
        <select id="state" name="state">
<?
	$res = sql("select distinct(state) from cidroute_cidlist order by state", "getAll", DB_FETCHMODE_ASSOC);
	$tmp = 0;
        if(is_array($res)){
                foreach($res as $result){
		print "<option value='".$tmp++."'>{$result['state']}</option>\n";
                }
        }
?>
        </select>
        <!-- state combobox is chained by country combobox-->
        <select name="region" id="region" style="display:none"></select>
        <!-- city combobox is chained by state combobox-->
        <select name="area" id="area" style="display:none"></select>
	</form>
	</div>

	</div>



	<script type="text/javascript">
 var options = { script: "modules/cidroute/js/ajax.php?type=global&limit=10&", 
	varname:"input", json: true, shownowresults:true, maxresults:10 };
 var as = new bsn.AutoSuggest('quick', options);
	</script>


<?php
} 
?>
