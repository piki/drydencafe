<?php  print file_get_contents("calheader.html"); ?>
<!--[if lt IE 7.]>
<style>
#eventstable { width: 96%; }
</style>
<![endif]-->
<?php
$num_weeks = 5;
$num_days = 7*$num_weeks;
include("common.php");


function db_read_events() {
	global $start, $start_date, $end, $end_date;
	$db = new SQLite3("db/new.db");
	$ret = Array();

	$start_ymd = $start_date['year'] * 10000 + $start_date['mon'] * 100 + $start_date['mday'];
	$end_ymd = $end_date['year'] * 10000 + $end_date['mon'] * 100 + $end_date['mday'];

	#echo "start_ymd=$start_ymd end_ymd=$end_ymd<br>\n";

	$query = $db->query("SELECT * FROM dates,events WHERE year*10000+month*100+day >= $start_ymd AND year*10000+month*100+day <= $end_ymd AND event_id=id");
        #echo $query;
	if (!$query) die($db->lastErrorMsg());

	while ($row = $query->fetchArray()) {
		#var_dump($row);
		#echo "one-time: ".$row["month"]."/".$row["day"]." => ".$row["title"]."<br>\n";
		$ret[sprintf("%04d.%02d.%02d", $row["year"], $row["month"], $row["day"])][] =
			Array("title" => $row["title"],
				"desc" => $row["description"],
				"in_list" => $row["in_events_list"],
				"icon" => $row["icon"]);
	}
	#$query->finalize();

	$query = $db->query("SELECT * FROM repeating,events WHERE year*10000+month*100+day<=$end_ymd AND end_year*10000+end_month*100+end_day>=$start_ymd AND event_id=id");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		#var_dump($row);
		#echo "repeating: ".$row["month"]."/".$row["day"]."-".$row["end_month"]."/".$row["end_day"]."@".$row["frequency"]." => ".$row["title"]."<br>\n";
		$cur = mktime(12, 0, 0, $row["month"], $row["day"], $row["year"]);
		$event_end = mktime(15, 0, 0, $row["end_month"], $row["end_day"], $row["end_year"]);
		if ($end < $event_end) $event_end = $end;
		while ($cur < $start) { $cur += $row["frequency"]*86400; }
		while ($cur <= $event_end) {
			$when = getdate($cur);
			$ret[sprintf("%04d.%02d.%02d", $when['year'], $when['mon'], $when['mday'])][] =
				Array("title" => $row["title"],
					"desc" => $row["description"],
					"in_list" => $row["in_events_list"],
					"icon" => $row["icon"]);
			$cur += $row["frequency"]*86400;
		}
	}
	#$query->finalize();
	$db->close();
	ksort($ret);
	return $ret;
}

function print_day_box($year, $month, $day, $events, $month_string) {
	$key = sprintf("%04d.%02d.%02d", $year, $month, $day);
	echo "  <td id=\"$key\"";
	if (count($events) > 0) {
		if (hval($_GET, 'show', "") == $key)
			echo " class=\"calendar-highlight\">";
		else
			echo " class=\"calendar-link\">";
		echo "<a href=\"#\" onclick=\"popup('$key')\">$month_string$day";
		foreach ($events as $ev)
			echo "<img src=\"images/cal-logos/" . $ev['icon'] . ".png\">";
		echo "</a></td>\n";
	}
	else
		echo ">$month_string$day<p>&nbsp;</td>";
}

function get_day_popup($key) {
	global $events;
	$ret = "<h3 class=\"cal-pop-day\">".pretty_date($key)."</h3>";
	foreach ($events[$key] as $ev)
		$ret .= "<h3>" . $ev['title'] . "</h3>" . $ev['desc'] . "<p>";
	return $ret;
}

function pretty_date($key) {
	return preg_replace('/\d{4}\.0?(\d+)\.0?(\d+)/', '$1/$2', $key);
}

$now = hval($_GET, 't', time());
$now_date = getdate($now);
$start = $now - $now_date['wday'] * 86400;
$start_date = getdate($start);
$end = mktime(12, 0, 0, $start_date['mon'], $start_date['mday'], $start_date['year']) + ($num_days-1)*86400;
$end_date = getdate($end);
$t_month = $mon_string = $start_date['month'];
echo "<!-- start=$start end=$end -->\n";
for ($t=$start; $t<$end; $t+=86400) {
	$t_date = getdate($t);
	if ($t_month != $t_date['month']) {
		$t_month = $t_date['month'];
		$mon_string .= ", " . $t_date['month'];
	}
}
$events = db_read_events();
?>
<table width="98%" class="calendar" cellpadding=2 cellspacing=0>
<tr><th colspan=7>
<a href="cal2.php?t=<?=$start-86400*$num_days?>">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?=$mon_string?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="cal2.php?t=<?=$start+86400*$num_days?>">&gt;&gt;</a>
</th>
<tr>
  <td class="weekday">Sun</td>
  <td class="weekday">Mon</td>
  <td class="weekday">Tue</td>
  <td class="weekday">Wed</td>
  <td class="weekday">Thu</td>
  <td class="weekday">Fri</td>
  <td class="weekday">Sat</td>
</tr>
<?php
$cur = $start;
for ($row=0; $row<$num_weeks; $row++) {
	echo "<tr>\n";
	for ($col=0; $col<7; $col++) {
		$cur_date = getdate($cur);
		print_day_box($cur_date['year'], $cur_date['mon'], $cur_date['mday'],
			hval($events, sprintf("%04d.%02d.%02d", $cur_date['year'], $cur_date['mon'], $cur_date['mday']), Array()),
			(($row == 0 && $col == 0) || $cur_date['mday'] == 1) ?  "<b>" . $cur_date['month'] . "</b> " : "");
		$cur += 86400;
	}
	echo "</tr>\n";
}
?>
</table>
<center>
<span class="cal-logo-legend"><a href="index.html"><img src="images/cal-logos/cafe.png"> Caf&eacute;</a></span>
<span class="cal-logo-legend"><a href="http://www.drydennyhistory.org/"><img src="images/cal-logos/dths.png"> Historical Society</a></span>
<span class="cal-logo-legend"><a href="http://southworthlibrary.org"><img src="images/cal-logos/library.png"> Southworth Library</a></span>
<span class="cal-logo-legend"><img src="images/cal-logos/music.png"> Local music</span>
<span class="cal-logo-legend"><a href="http://www.dryden.rotary-site.org/"><img src="images/cal-logos/rotary.png"> Rotary</a></span>
<span class="cal-logo-legend"><a href="http://www.dryden.k12.ny.us/"><img src="images/cal-logos/schools.png"> Dryden Schools</a></span>
<span class="cal-logo-legend"><img src="images/cal-logos/sertoma.png"> Sertoma</span>
<span class="cal-logo-legend"><img src="images/cal-logos/grange.png"> Grange</span>
<span class="cal-logo-legend"><a href="http://dryden-ny.org"><img src="images/cal-logos/village.png"> Village of Dryden</a></span>
</center>

<table width="100%" id="eventstable"><tr>
<td valign="top" width="40%" id="eventslist"><div id="events">
<ul class="calendar">
<?php foreach ($events as $key => $ev_arr)
		foreach ($ev_arr as $ev)
			if ($ev['in_list']) { ?>
<li><a href="javascript:popup('<?=$key?>')">
<b><?=pretty_date($key)?></b> - <?=$ev['title']?></a>
<?php } ?>
</ul>
</div></td>
<td valign="top" width="60%"><div id="pop">
<?php
	if (array_key_exists('show', $_GET) && array_key_exists($_GET['show'], $events))
		print get_day_popup($_GET['show']);
	else { ?>
<h3>Community Calendar</h3>
Welcome to the Dryden Community Center Caf&eacute;
Community Calendar!  Using your mouse, click on any event in the calendar
(above) or the list (left) to see details.
<?php } ?></div>
</td></tr></table>

<script language="javascript">
var popup_blank = document.getElementById("pop").innerHTML;
var highlight<?= array_key_exists('show', $_GET) ? " = document.getElementById('" . $_GET['show'] . "')" : ""?>;
function popup_found(id, txt) {
	document.getElementById("pop").innerHTML = txt;
	if (highlight != null)
		highlight.className = "calendar-link";
	highlight = document.getElementById(id);
	highlight.className = "calendar-highlight";
}
function popup(id) {
	if (0) {}
<?php foreach ($events as $key => $arr) { ?>
	else if (id == "<?=$key?>") popup_found(id, '<?= js_escape(get_day_popup($key)) ?>');
<?php } ?>
	else document.getElementById("pop").innerHTML = popup_blank;
}
</script>

<?php print file_get_contents("footer.html"); ?>
