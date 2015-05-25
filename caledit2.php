
<?php
include("common.php");
print file_get_contents("short-header.html");
?>
<table cellspacing=4>   <!-- layout -->
<tr><td valign="top" width="39%">  <!-- events -->
<table cellpadding=8><tr><td bgcolor="#763712">
Click any event below to edit or delete it.<br>
Fill in the boxes and click "Add event" to add a new event.<br>
Click here to <a href="cleanup.php" target="_top">clean up old events</a>.<p>
</td></tr></table><p>
<?php
function db_read_events() {
	global $events, $dates, $repeating, $icons;
	if (!($db = new SQLite3("db/new.db"))) {
		die("error opening db");
	}

	$events = Array();
	$icons = Array();
	$query = $db->query("SELECT * FROM events");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		$events[] = $row;
		$icons[$row['icon']] = 1;    # !! doesn't find unused icons
	}
	#$query->finalize();

	$dates = Array();
	$query = $db->query("SELECT * FROM dates");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		$dates[$row['event_id']][] = $row;
	}
	#$query->finalize();

	$repeating = Array();
	$query = $db->query("SELECT * FROM repeating");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		$repeating[$row["event_id"]][] = $row;
	}
	#$query->finalize();
	$db->close();
}

db_read_events();
foreach ($events as $ev) {
	echo "<img src=\"images/cal-logos/" . $ev['icon'] . ".png\"><a href=\"javascript:popup(" . $ev['id'] . ")\">" . $ev['title'] . "</a> (";
	$first = true;
	if (array_key_exists($ev['id'], $dates)) {
		foreach ($dates[$ev['id']] as $dt) {
			if ($first) $first = false; else echo ", ";
			echo $dt['month'] . "/" . $dt['day'] . "/" . $dt['year'];
		}
	}
	if (array_key_exists($ev['id'], $repeating)) { 
		foreach ($repeating[$ev['id']] as $dt) {
			if ($first) $first = false; else echo ", ";
			if ($dt['end_year'] < 2037)
				echo "each " . $dt['frequency'] . " days from " . $dt['month'] . "/" . $dt['day'] . "/" . $dt['year'] . " to " . $dt['end_month'] . "/" . $dt['end_day'] . "/" . $dt['end_year'];
			else
				echo "each " . $dt['frequency'] . " days starting " . $dt['month'] . "/" . $dt['day'] . "/" . $dt['year'];
		}
	}
	echo ")<br>\n";
}
?>
</td>  <!-- events -->
<td width="2%">&nbsp;</td>
<td valign="top" width="59%">   <!-- editing form -->
<form name="eventdata" method="post" action="change.php">

Title: <input name="title" size=40><p>

Description:
(<a id="preview_link" href="javascript:preview()">Preview...</a>)<br>
<span id="desc"><textarea rows=15 cols=80 name="desc"></textarea></span>
<span id="preview"></span>
<p>

Show in events list? <input type="checkbox" name="in_list"><p>

Organization: <select name="icon">
<?php
$d = opendir("images/cal-logos");
$matches = Array();
while (($fn = readdir($d))) {
	if (preg_match('/(.*)\.png$/', $fn, $matches))
		echo "<option name=\"$matches[1]\">$matches[1]\n";
}
closedir($d);
?>
</select><p>

<table><tr><td valign="top">Date:</td>
<td><input size=2 name="month">/<input size=2 name="day">/<input size=4 name="year"><br>
<span id="outerdate2"></span>
<a href="javascript:more_dates()">add another date...</a></td></tr></table><p>

Repeating? <input type="checkbox" name="repeating" onchange="javascript:repeating_changed()">
each <input name="frequency" disabled="true" size=2> days<br>
Until? <input type="checkbox" disabled="true" name="ends" onchange="javascript:ends_changed()">
<input size=2 disabled="true" name="endmonth">/<input size=2 disabled="true" name="endday">/<input size=4 disabled="true" name="endyear"><br>

<p>
<input type="reset" value="Cancel" onclick="javascript:cleared()">
<input name="submit" type="submit" value="Add event">
<input name="del" type="submit" value="Delete event" disabled="true">
<input name="id" type="hidden">

</form>
</td></tr>
</table>

<script language="javascript">
var editing = null;
var nextdate = 2;

function preview() {
	win = window.open("", "previewwindow", "status=0,width=500,height=350,scrollbars=1");
	win.document.write('<link rel="stylesheet" type="text/css" href="style.css">');
	win.document.write("<h3>" + document.eventdata.title.value + "</h3>");
	win.document.write(document.eventdata.desc.value);
	win.document.close();
}

function repeating_changed() {
	if (document.eventdata.repeating.checked) {
		document.eventdata.frequency.disabled = false;
		document.eventdata.ends.disabled = false;
		document.eventdata.endmonth.disabled = !document.eventdata.ends.checked;
		document.eventdata.endday.disabled = !document.eventdata.ends.checked;
		document.eventdata.endyear.disabled = !document.eventdata.ends.checked;
		document.getElementById("outerdate2").innerHTML = "";
		nextdate = 2;
	}
	else {
		document.eventdata.frequency.disabled = true;
		document.eventdata.ends.disabled = true;
		document.eventdata.endmonth.disabled = true;
		document.eventdata.endday.disabled = true;
		document.eventdata.endyear.disabled = true;
	}
}

function ends_changed() {
	document.eventdata.endmonth.disabled
		= document.eventdata.endday.disabled
		= document.eventdata.endyear.disabled
		= !document.eventdata.ends.checked;
}

function cleared() {
	document.eventdata.submit.value = "Add event";
	document.eventdata.id.value = "";
	document.eventdata.del.disabled = true;
	editing = null;
	nextdate = 2;
	document.getElementById("outerdate2").innerHTML = "";
}

function more_dates() {
	document.getElementById("outerdate"+nextdate).innerHTML += '<span id="date'+nextdate+'"><input size=2 name="month'+nextdate+'">/<input size=2 name="day'+nextdate+'">/<input size=4 name="year'+nextdate+'"> (<a href="javascript:remove_date('+nextdate+')">remove</a>)<br></span><span id="outerdate'+(nextdate+1)+'"></span>';
	nextdate++;
	document.eventdata.repeating.checked = false;
	document.eventdata.frequency.disabled
		= document.eventdata.ends.disabled
		= document.eventdata.endmonth.disabled
		= document.eventdata.endday.disabled
		= document.eventdata.endyear.disabled
		= true;
}

function remove_date(n) {
	document.getElementById("date"+n).innerHTML = "";
}

function popup(id) {
	document.getElementById("outerdate2").innerHTML = "";
	document.eventdata.submit.value = "Apply changes";
	document.eventdata.del.disabled = false;
	nextdate = 2;
	n = 1;
	switch (id) {
<?php foreach($events as $ev) { ?>
		case <?=$ev['id']?>:
			document.eventdata.id.value = '<?=js_escape($ev['id'])?>';
			document.eventdata.title.value = '<?=js_escape($ev['title'])?>';
			document.eventdata.desc.value = '<?=js_escape($ev['description'])?>';
			document.eventdata.in_list.checked = <?=js_escape($ev['in_events_list'])?> ? true : false;
			document.eventdata.icon.value = '<?=$ev['icon']?>';
<?php if (array_key_exists($ev['id'], $repeating) && count($repeating[$ev['id']]) > 0) { ?>
			document.eventdata.repeating.checked = true;
			document.eventdata.frequency.value = <?=$repeating[$ev['id']][0]['frequency']?>;
<?php   if ($repeating[$ev['id']][0]['end_year'] >= 2037) { ?>
			document.eventdata.ends.checked = false;
<?php   } else { ?>
			document.eventdata.ends.checked = true;
<?php   } ?>
			document.eventdata.year.value = <?=$repeating[$ev['id']][0]['year']?>;
			document.eventdata.month.value = <?=$repeating[$ev['id']][0]['month']?>;
			document.eventdata.day.value = <?=$repeating[$ev['id']][0]['day']?>;
			document.eventdata.endyear.value = <?=$repeating[$ev['id']][0]['end_year']?>;
			document.eventdata.endmonth.value = <?=$repeating[$ev['id']][0]['end_month']?>;
			document.eventdata.endday.value = <?=$repeating[$ev['id']][0]['end_day']?>;
<?php } else {
		for ($i=0; $i<count($dates[$ev['id']]); $i++) {
			if ($i == 0) $n = ""; else {
				$n = $i+1;
				echo "			more_dates();\n";
			} ?>
			document.eventdata.year<?=$n?>.value = <?=$dates[$ev['id']][$i]['year']?>;
			document.eventdata.month<?=$n?>.value = <?=$dates[$ev['id']][$i]['month']?>;
			document.eventdata.day<?=$n?>.value = <?=$dates[$ev['id']][$i]['day']?>;
<?php   } ?>
			document.eventdata.repeating.checked = false;
<?php } ?>
			break;
<?php } ?>
	}
	document.eventdata.frequency.disabled
		= document.eventdata.ends.disabled
		= !document.eventdata.repeating.checked;
	document.eventdata.endmonth.disabled
		= document.eventdata.endday.disabled
		= document.eventdata.endyear.disabled
		= !document.eventdata.repeating.checked || !document.eventdata.ends.checked;
}
</script>

<?php print file_get_contents("short-footer.html"); ?>
