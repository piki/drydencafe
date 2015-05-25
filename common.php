<?php
function days_in($month, $year) {
	$dim_array = array(31, 0, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	if ($month == 2)
		return ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0)) ? 29 : 28;
	else
		return $dim_array[$month-1];
}

function js_escape($str) {
	$str = preg_replace("/\\\\/", "\\\\\\\\", $str);
	$str = preg_replace("/'/", "\\'", $str);
	$str = preg_replace("/\r/", "", $str);
	$str = preg_replace("/\n/", "\\n", $str);
	return $str;
}

function sql_escape($str) {
	$str = preg_replace("/'/", "''", $str);
	return $str;
}

function hval($arr, $key, $dfl) {
	return array_key_exists($key, $arr) ? $arr[$key] : $dfl;
}
?>
