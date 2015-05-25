<?php
include("common.php");
print file_get_contents("short-header.html");
?>
<table cellpadding=8><tr><td bgcolor="#763712">
Click any event below to edit or delete it.<br>
Fill in the boxes and click "Add event" to add a new event.<br>
Click here to <a href="cleanup.php" target="_top">clean up old events</a>.<p>
</td></tr></table><p>
<?php
function db_read_events() {
	global $events, $dates, $repeating;
	if (!($db = new SQLite3("db/new.db"))) {
		die("error opening db");
	}

	$events = Array();
	$query = $db->query("SELECT * FROM events");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		$events[] = $row;
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

<script language="javascript">
function popup(id) {
	parent.document.getElementById("outerdate2").innerHTML = "";
	parent.document.eventdata.submit.value = "Apply changes";
	parent.document.eventdata.del.disabled = false;
	parent.document.eventdata.nextdate.value = 2;
	n = 1;
	switch (id) {
<?php foreach($events as $ev) { ?>
		case <?=$ev['id']?>:
			parent.document.eventdata.id.value = '<?=js_escape($ev['id'])?>';
			parent.document.eventdata.title.value = '<?=js_escape($ev['title'])?>';
			parent.document.eventdata.desc.value = '<?=js_escape($ev['description'])?>';
			parent.document.eventdata.in_list.checked = <?=js_escape($ev['in_events_list'])?> ? true : false;
			parent.document.eventdata.icon.value = '<?=$ev['icon']?>';
			for (i=0; i<parent.document.eventdata.icon.options.length; i++) {
				if (parent.document.eventdata.icon.options[i].name == '<?=$ev['icon']?>') {
					parent.document.eventdata.icon.selectedIndex = i;
					break;
				}
			}
<?php if (array_key_exists($ev['id'], $repeating) && count($repeating[$ev['id']]) > 0) { ?>
			parent.document.eventdata.repeating.checked = true;
			parent.document.eventdata.frequency.value = <?=$repeating[$ev['id']][0]['frequency']?>;
<?php   if ($repeating[$ev['id']][0]['end_year'] >= 2037) { ?>
			parent.document.eventdata.ends.checked = false;
<?php   } else { ?>
			parent.document.eventdata.ends.checked = true;
<?php   } ?>
			parent.document.eventdata.year.value = <?=$repeating[$ev['id']][0]['year']?>;
			parent.document.eventdata.month.value = <?=$repeating[$ev['id']][0]['month']?>;
			parent.document.eventdata.day.value = <?=$repeating[$ev['id']][0]['day']?>;
			parent.document.eventdata.endyear.value = <?=$repeating[$ev['id']][0]['end_year']?>;
			parent.document.eventdata.endmonth.value = <?=$repeating[$ev['id']][0]['end_month']?>;
			parent.document.eventdata.endday.value = <?=$repeating[$ev['id']][0]['end_day']?>;
<?php } else {
		for ($i=0; $i<count($dates[$ev['id']]); $i++) {
			if ($i == 0) $n = ""; else {
				$n = $i+1;
				echo "			more_dates();\n";
			} ?>
			parent.document.eventdata.year<?=$n?>.value = <?=$dates[$ev['id']][$i]['year']?>;
			parent.document.eventdata.month<?=$n?>.value = <?=$dates[$ev['id']][$i]['month']?>;
			parent.document.eventdata.day<?=$n?>.value = <?=$dates[$ev['id']][$i]['day']?>;
<?php   } ?>
			parent.document.eventdata.repeating.checked = false;
<?php } ?>
			break;
<?php } ?>
	}
	parent.document.eventdata.frequency.disabled
		= parent.document.eventdata.ends.disabled
		= !parent.document.eventdata.repeating.checked;
	parent.document.eventdata.endmonth.disabled
		= parent.document.eventdata.endday.disabled
		= parent.document.eventdata.endyear.disabled
		= !parent.document.eventdata.repeating.checked || !parent.document.eventdata.ends.checked;
}

function more_dates() {
	parent.document.getElementById("outerdate"+parent.document.eventdata.nextdate.value).innerHTML += '<span id="date'+parent.document.eventdata.nextdate.value+'"><input size=2 name="month'+parent.document.eventdata.nextdate.value+'">/<input size=2 name="day'+parent.document.eventdata.nextdate.value+'">/<input size=4 name="year'+parent.document.eventdata.nextdate.value+'"> (<a href="javascript:remove_date('+parent.document.eventdata.nextdate.value+')">remove</a>)<br></span><span id="outerdate'+(parent.document.eventdata.nextdate.value*1+1)+'"></span>';
	parent.document.eventdata.nextdate.value++;
	parent.document.eventdata.repeating.checked = false;
	parent.document.eventdata.frequency.disabled
		= parent.document.eventdata.ends.disabled
		= parent.document.eventdata.endmonth.disabled
		= parent.document.eventdata.endday.disabled
		= parent.document.eventdata.endyear.disabled
		= true;
}
</script>

<?php print file_get_contents("short-footer.html"); ?>
