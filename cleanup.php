<?php
include("common.php");

if (!($db = new SQLite3("db/new.db"))) {
	die("error opening db");
}

$now = getdate();
$now_ymd = $now['year'] * 10000 + $now['mon'] * 100 + $now['mday'];

echo "Today is $now_ymd.<p>\n";

# get all events in the past into 'to_del'
$to_del = Array();
$query = $db->query("SELECT DISTINCT * FROM dates WHERE year*10000+month*100+day < $now_ymd");
if (!$query) die($db->lastErrorMsg());
while ($row = $query->fetchArray()) {
	$to_del[] = $row;
}
# get all repeaters with an end date in the past into 'to_del'
$query = $db->query("SELECT DISTINCT * FROM repeating WHERE end_year*10000+end_month*100+end_day < $now_ymd");
if (!$query) die($db->lastErrorMsg());
while ($row = $query->fetchArray()) {
	$to_del[] = $row;
}

# delete everything from to_del that doesn't also have an event in the future
foreach ($to_del as $ev) {
	if (dates_after("dates", "", $now_ymd, $ev['event_id']) == 0) {
		if (dates_after("repeating", "end_", $now_ymd, $ev['event_id']) == 0) {
			echo "Deleting old event #" . $ev['event_id'] . "<br>\n";
			$db->exec("DELETE FROM events WHERE id=" . $ev['event_id']);
			$db->exec("DELETE FROM dates WHERE event_id=" . $ev['event_id']);
			$db->exec("DELETE FROM repeating WHERE event_id=" . $ev['event_id']);
		}
	}
}
$db->exec("DELETE FROM dates WHERE year*10000+month*100+day < $now_ymd");
$db->exec("DELETE FROM repeating WHERE end_year*10000+end_month*100+end_day < $now_ymd");

# move all repeaters up to today/future
$to_advance = Array();
$query = $db->query("SELECT * FROM repeating WHERE year*10000+month*100+day < $now_ymd");
while ($row = $query->fetchArray()) {
	$to_advance[] = $row;
}
echo "<p>\n";
foreach ($to_advance as $ev) {
	$y = $ev['year'];
	$m = $ev['month'];
	$d = $ev['day'];
	while ($y * 10000 + $m * 100 + $d < $now_ymd) {
		$d += $ev['frequency'];
		while ($d > days_in($m, $y)) {
			$d -= days_in($m, $y);
			$m++;
			if ($m > 12) {
				$m -= 12;
				$y++;
			}
		}
	}
	$db->exec("UPDATE repeating SET year=$y, month=$m, day=$d WHERE year=" . $ev['year'] . " AND month=" . $ev['month'] . " AND day=" . $ev['day'] . " AND event_id=" . $ev['event_id']);
	echo "Moving repeating event #" . $ev['event_id'] . " from " . $ev['year'] . "-" . $ev['month'] . "-" . $ev['day'] . " to $y-$m-$d (every " . $ev['frequency'] . " days)<br>\n";
}

function dates_after($table, $col_pfx, $now_ymd, $id) {
	global $db;
	$query = $db->query("SELECT COUNT(*) AS ct FROM $table WHERE ${col_pfx}year*10000+${col_pfx}month*100+${col_pfx}day >= $now_ymd AND event_id=$id");
	if (!$query) die($db->lastErrorMsg());
	$row = $query->fetchArray();
	return $row['ct'];
}
?>

<p>
Done.  Click your <b>back</b> button.
