<link rel="stylesheet" type="text/css" href="style.css">
<table cellpadding=0 cellspacing=0 width="100%"  bgcolor="#A64B1A"><tr><td height="111" class="events">
<h3>Events at the Caf&eacute;</h3>

<?php
$num_days = 30;
include("common.php");
function db_read_events() {
	global $start, $start_date, $end, $end_date;
	if (!($db = new SQLite3("db/new.db"))) {
		die("error opening db");
	}
	$ret = Array();

	$start_ymd = $start_date['year'] * 10000 + $start_date['mon'] * 100 + $start_date['mday'];
	$end_ymd = $end_date['year'] * 10000 + $end_date['mon'] * 100 + $end_date['mday'];

	#echo "start_ymd=$start_ymd end_ymd=$end_ymd<br>\n";

	$query = $db->query("SELECT * FROM dates,events WHERE year*10000+month*100+day >= $start_ymd AND year*10000+month*100+day <= $end_ymd AND event_id=id AND in_events_list=1 AND (icon='cafe' OR icon='music')");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
		#echo "one-time: ".$row["month"]."/".$row["day"]." => ".$row["title"]."<br>\n";
		$ret[sprintf("%04d.%02d.%02d", $row["year"], $row["month"], $row["day"])][] =
			Array("title" => preg_replace('/at the cafe/i', '', $row["title"]),
				"icon" => $row["icon"], "desc" => $row["description"]);
	}
	#$query->finalize();
// Uncomment this block if you want to see recurring events
/*
	$query = $db->query("SELECT * FROM repeating,events WHERE year*10000+month*100+day<=$end_ymd AND end_year*10000+end_month*100+end_day>=$start_ymd AND event_id=id AND in_events_list=1 AND (icon='cafe' OR icon='music')");
	if (!$query) die($db->lastErrorMsg());
	while ($row = $query->fetchArray()) {
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
*/





	#$query->finalize();
	$db->close();
	ksort($ret);
	return $ret;
}

function pretty_date($key) {
	return preg_replace('/\d{4}\.0?(\d+)\.0?(\d+)/', '$1/$2', $key);
}

function print_day_box($year, $month, $day, $events, $month_string) {
	$key = sprintf("%04d.%02d.%02d", $year, $month, $day);
	if (count($events) > 0) {
		foreach ($events as $ev) {
			echo "<p>\n<strong><a target=\"_top\" href=\"cal2.php?show=$key\">" . pretty_date($key) . "</a> - " . $ev['title']. "</strong> - ". $ev['desc'] . "\n";
		}
	}
}

$start = time();
$start_date = getdate($start);
$end = mktime(12, 0, 0, $start_date['mon'], $start_date['mday'], $start_date['year']) + ($num_days-1)*86400;
$end_date = getdate($end);
$events = db_read_events();
$cur = $start;
for ($d=0; $d<$num_days; $d++) {
	$cur_date = getdate($cur);
	print_day_box($cur_date['year'], $cur_date['mon'], $cur_date['mday'],
		hval($events, sprintf("%04d.%02d.%02d", $cur_date['year'], $cur_date['mon'], $cur_date['mday']), Array()),
		($d == 0 || $cur_date['mday'] == 1) ? ("<b>" . $cur_date['month'] . "</b> ") : "");
	$cur += 86400;
}

?>

</td>
</tr></table>
