var CAL_events = new Array();
var popup_blank = document.getElementById("pop").innerHTML;
var highlight;
var today = new Date();

function add_event(month, day, in_event_list, icon, title, description) {
	var past = false;
	if (month == today.getMonth()+1 && day < today.getDate())
		in_event_list = false;
	
	var key = month + "." + day;
	if (CAL_events[key] == null)
		CAL_events[key] = '<h3 class="cal-pop-day">'+month+'/'+day+'</h3>';
	CAL_events[key] += '<p><h3>'+title+'</h3>'+description;
	var td = document.getElementById(key);
	if (td == null)
		alert("Date "+month+"/"+day+" does not seem to be visible");
	else {
		td.className = 'calendar-link';
		td.innerHTML = '<a id="list'+key+'" href="#" onclick="popup(\''+key+'\')" >'+day
			+ '<br><img src="images/cal-logos/'+icon+'.png"></a>';
	}
	if (in_event_list) {
		var div = document.getElementById("ev"+month);
		if (div != null)
			div.innerHTML += '<li><a href="javascript:popup(\''+key+'\')"'
				+ (past ? 'style="color:#cccccc"' : "") + "><b>"
				+ month + "/" + day + "</b> - " + title + "</a>";
	}
}

function add_event_repeating(month, day, gap, end_month, end_day, in_event_list, icon, title, description) {
	d = new Date();
	d.setFullYear(d.getFullYear(), month-1, day);
	while (true) {
		add_event(d.getMonth()+1, d.getDate(), in_event_list, icon, title, description);
		if (d.getMonth()+1 == end_month && d.getDate() == end_day) break;
		d.setTime(d.getTime() + 1000 * 86400 * gap);  // 'gap' days later
	}
}

function popup(name) {
	if (name == null)
		document.getElementById("pop").innerHTML = popup_blank;
	else {
		document.getElementById("pop").innerHTML = CAL_events[name];
		if (highlight != null)
			highlight.className = "calendar-link";
		highlight = document.getElementById(name);
		highlight.className = "calendar-highlight";
	}
}

function show_month(month, year, target, list_target) {
	var name;
	var days;
	var d;
	d = new Date(year, month-1, 1);
	var dow = d.getDay();
	switch (month) {
		case  1:  name = "January";    days=31;  break;
		case  2:  name = "February";
			days = (year % 400 == 0) ? 29 :
				(year % 100 == 0) ? 28 :
				(year % 4 == 0) ? 29 :
				28;
			break;
		case  3:  name = "March";      days=31;  break;
		case  4:  name = "April";      days=30;  break;
		case  5:  name = "May";        days=31;  break;
		case  6:  name = "June";       days=30;  break;
		case  7:  name = "July";       days=31;  break;
		case  8:  name = "August";     days=31;  break;
		case  9:  name = "September";  days=30;  break;
		case 10:  name = "October";    days=31;  break;
		case 11:  name = "November";   days=30;  break;
		case 12:  name = "December";   days=31;  break;
	}
	var text =
		'<table width="98%" class="calendar" cellpadding=2 cellspacing=0><tr><th colspan=7>'+name+'</th></tr>'
		+'<tr><td class="weekday">Sun</td><td class="weekday">Mon</td>'
		+'<td class="weekday">Tue</td><td class="weekday">Wed</td>'
		+'<td class="weekday">Thu</td><td class="weekday">Fri</td>'
		+'<td class="weekday">Sat</td></tr><tr>';
	var i;
	for (i=0; i<dow; i++) text += '<td>&nbsp;</td>';
	var mday = 1;
	for (i=dow; i<=6; i++)
		text += '  <td id="'+month+'.'+mday+'">'+(mday++)+'<p>&nbsp;</td>';
	text += '</tr>';
	while (mday <= days) {
		text += '<tr>';
		for (i=0; i<=6; i++)
			if (mday <= days)
				text += '  <td id="'+month+'.'+mday+'">'
					+(mday++)+'<p>&nbsp;</td>\n';
			else
				text += '  <td></td>';
		text += '</tr>';
	}
	text += '</table>';
	if (target == null)
		document.write(text);
	else {
		var element = document.getElementById(target);
		if (element == null)
			alert('No target element: "'+target+'"');
		else
			element.innerHTML = text;
	}

	if (list_target != null) {
		var element = document.getElementById(list_target);
		if (element == null)
			alert('No target element: "'+target+'"');
		else
			element.innerHTML += '<h3 class="calendar-events-month">'+name+'</h3>'
				+ '<ul class="calendar" id="ev' + month + '"></ul>';
	}
}

function check_show() {
	var idx = document.location.href.indexOf("?show=");
	if (idx > 0) popup(document.location.href.substr(idx+6));
}
