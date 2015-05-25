<?php
include("common.php");
print file_get_contents("short-header.html");
?>
<table cellspacing=4>   <!-- layout -->
<tr><td valign="top" widht="39%">  <!-- events -->
<iframe src="caledit-frame.php" width="100%" height="580" style="border-width:0"></iframe>
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
<input type="hidden" name="nextdate">
<input name="id" type="hidden">

</form>
</td></tr>
</table>

<script language="javascript">
var editing = null;
document.eventdata.nextdate.value = 2;

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
		document.eventdata.nextdate.value = 2;
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
	document.eventdata.nextdate.value = 2;
	document.getElementById("outerdate2").innerHTML = "";
}

function more_dates() {
	document.getElementById("outerdate"+document.eventdata.nextdate.value).innerHTML += '<span id="date'+document.eventdata.nextdate.value+'"><input size=2 name="month'+document.eventdata.nextdate.value+'">/<input size=2 name="day'+document.eventdata.nextdate.value+'">/<input size=4 name="year'+document.eventdata.nextdate.value+'"> (<a href="javascript:remove_date('+document.eventdata.nextdate.value+')">remove</a>)<br></span><span id="outerdate'+(document.eventdata.nextdate.value*1+1)+'"></span>';
	document.eventdata.nextdate.value++;
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
</script>

<?php print file_get_contents("short-footer.html"); ?>
