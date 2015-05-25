<?php
	include("common.php");
	define('DEBUG', 0);
	if (DEBUG) print file_get_contents("short-header.html");
	$_POST['title'] = preg_replace("/\\\\'/", "'", hval($_POST, 'title', ""));  # undo PHP5 ' => \' brain damage
	$_POST['desc'] = preg_replace("/\\\\'/", "'", hval($_POST, 'desc', ""));    # undo PHP5 ' => \' brain damage
	$_POST['title'] = preg_replace('/\\\\"/', '"', hval($_POST, 'title', ""));  # undo PHP5 ' => \' brain damage
	$_POST['desc'] = preg_replace('/\\\\"/', '"', hval($_POST, 'desc', ""));    # undo PHP5 ' => \' brain damage

	if (!($db = new SQLite3("db/new.db"))) {
		die("error opening db");
	}

	function html_escape($str) {
		$str = preg_replace("/&/", "&amp;", $str);
		$str = preg_replace("/</", "&lt;", $str);
		$str = preg_replace("/>/", "&gt;", $str);
		return $str;
	}

	function get_numeric($key, $dfl) {
		return array_key_exists($key, $_POST) ? preg_replace("/[^0-9]/", "", $_POST[$key]) : $dfl;
	}

	function delete_event() {
		global $db;
		if (DEBUG) {
			echo "<h2>Delete event</h2>\n";
			foreach ($_POST as $k => $v) {
				echo "$k => ".html_escape($v)."<br>\n";
			}
		}
		$id = get_numeric('id', 0);
		$query = "DELETE FROM events WHERE id=$id";
		if (DEBUG) print "$query<br>\n";
		$db->query($query);
		$query = "DELETE FROM dates WHERE event_id=$id";
		if (DEBUG) print "$query<br>\n";
		$db->query($query);
		$query = "DELETE FROM repeating WHERE event_id=$id";
		if (DEBUG) print "$query<br>\n";
		$db->query($query);
	}

	function add_event($preserve_id) {
		global $db;
		if (DEBUG) {
			echo "<h2>Add event</h2>\n";
			foreach ($_POST as $k => $v) {
				echo "$k => ".html_escape($v)."<br>\n";
			}
		}
		$query = "', '".sql_escape($_POST['desc'])
				."', ".(hval($_POST, 'in_list', "") == "on" ? "1" : "0")
				.", '" . $_POST['icon'] . "')";
		if ($preserve_id) {
			$rowid = get_numeric('id', 0);
			$query = "INSERT INTO events(id, title, description, in_events_list, icon) "
				."VALUES($rowid, '".sql_escape($_POST['title']).$query;
			if (DEBUG) print html_escape($query)."<br>\n";
			$db->query($query);
		}
		else {
			$query = "INSERT INTO events(title, description, in_events_list, icon) "
				."VALUES('".sql_escape($_POST['title']).$query;
			if (DEBUG) print html_escape($query)."<br>\n";
			$db->query($query);
			$rowid = "=row=";
			$rowid = $db->lastInsertRowID();
		}

		$year = get_numeric('year', 0);
		$month = get_numeric('month', 0);
		$day = get_numeric('day', 0);
		$endyear = get_numeric('endyear', 0);
		$endmonth = get_numeric('endmonth', 0);
		$endday = get_numeric('endday', 0);
		$frequency = get_numeric('frequency', 1);
		if (hval($_POST, 'repeating', "") == "on") {
			$query = "INSERT INTO repeating VALUES("
				."$year, $month, $day, $frequency, ";
			if (hval($_POST, 'ends', "") == "on")
				$query .= "$endyear, $endmonth, $endday, ";
			else
				$query .= "2037, 12, 31, ";
			$query .= "$rowid)";
			if (DEBUG) print html_escape($query)."<br>\n";
			$db->query($query);
		}
		else {
			$query = "INSERT INTO dates "
				."VALUES($year, $month, $day, $rowid)";
			if (DEBUG) print html_escape($query)."<br>\n";
			$db->query($query);
			for ($i=2; $i<=30; $i++)
				if (array_key_exists("day$i", $_POST) && array_key_exists("month$i", $_POST) && array_key_exists("year$i", $_POST)) {
					$query = "INSERT INTO dates "
						."VALUES(".$_POST["year$i"].", ".$_POST["month$i"].", ".$_POST["day$i"].", $rowid)";
					if (DEBUG) print html_escape($query)."<br>\n";
					$db->query($query);
				}
		}
	}

	function validate_ymd($year, $month, $day) {
		if ($year < 2008 || $year > 2037) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "invalid year: only 2008 to 2037 are valid";
			return false;
		}
		if ($month < 1 || $month > 12) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "invalid month: only 1 to 12 are valid";
			return false;
		}
		if ($day < 1 || $day > days_in($month, $year)) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "invalid day: only 1 to ".days_in($month, $year)." are valid";
			return false;
		}
		return true;
	}
		
	function validate_dates() {
		if (!validate_ymd($_POST['year'], $_POST['month'], $_POST['day'])) return false;
		for ($i=2; $i<=30; $i++)
			if (array_key_exists("year$i", $_POST) || array_key_exists("month$i", $_POST) || array_key_exists("day$i", $_POST))
				if (!validate_ymd($_POST["year$i"], $_POST["month$i"], $_POST["day$i"])) return false;
		if (hval($_POST, 'ends', "") == "on") {
			if (!validate_ymd($_POST['endyear'], $_POST['endmonth'], $_POST['endday'])) return false;
			if ($_POST['endyear'] < $_POST['year']
					|| ($_POST['endyear'] == $_POST['year'] && $_POST['endmonth'] < $_POST['month'])
					|| ($_POST['endyear'] == $_POST['year'] && $_POST['endmonth'] == $_POST['month'] && $_POST['endday'] <= $_POST['day'])) {
				if (!DEBUG) print file_get_contents("short-header.html");
				echo "repeating event ends before it begins";
				return false;
			}
		}

		if (array_key_exists('repeating', $_POST)) {
			if ($_POST['frequency'] < 1 || $_POST['frequency'] > 100) {
				if (!DEBUG) print file_get_contents("short-header.html");
				echo "invalid repeat frequency: only 1 to 100 are valid";
				return false;
			}
		}
		return true;
	}

	function validate_other() {
		if (!hval($_POST, 'title', "")) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "title cannot be empty";
			return false;
		}
		if (!hval($_POST, 'desc', "")) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "description cannot be empty";
			return false;
		}
		if (!array_key_exists('icon', $_POST)) {
			if (!DEBUG) print file_get_contents("short-header.html");
			echo "organization cannot be blank";
			return false;
		}
		return true;
	}

	function apply_changes() {
		if (DEBUG) echo "<h2>Apply changes</h2>\n";
		delete_event();
		add_event(true);
	}

	if (hval($_POST, 'del', "") == "Delete event") {
		delete_event();
		header("Location: caledit.php");
	}
	else if (hval($_POST, 'submit', "") == "Add event") {
		if (!validate_dates()) exit;
		if (!validate_other()) exit;
		add_event(false);
		header("Location: caledit.php");
	}
	else if (hval($_POST, 'submit', "") == "Apply changes") {
		if (!validate_dates()) exit;
		if (!validate_other()) exit;
		apply_changes();
		header("Location: caledit.php");
	}
	else {
		echo "Invalid operation: what button did you press?";
	}

	if (DEBUG) print file_get_contents("short-footer.html");
?>
