var Xoffset = -22;
var Yoffset = 0;
if (document.all) {  // IE
	Xoffset = -33;
	Yoffset = 0;
}
Xoffset=50;
var showing;
var timeoutHandle;
function show(id) {
	var chld = document.getElementById(id);
	if (showing) {
		if (showing != chld) {
			hide();
		}
		else {
			clearTimeout(timeoutHandle);
			return;
		}
	}
	showing = chld;

	var pnt = document.getElementById(id+'-parent');
	chld.style.left = pnt.offsetLeft + pnt.offsetWidth;
	chld.style.top = pnt.offsetParent.offsetTop + pnt.offsetTop;
	chld.style.display = "";
	chld.onmouseover = keepshowing;
	chld.onmouseout = maketimeout;
	pnt.onmouseout = maketimeout;
}
function maketimeout(evt) {
	clearTimeout(timeoutHandle);
	timeoutHandle = setTimeout("hide()", 500);
}
function hide() {
	if (showing) {
		showing.style.display = "none";
		showing = null;
	}
	clearTimeout(timeoutHandle);
}
function keepshowing(evt) {
	clearTimeout(timeoutHandle);
}
