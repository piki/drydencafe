var slides = [
['<b>Our caf&eacute;.</b> The caf&eacute; is now open!  Come in Tue - Sat 8am-9pm for fresh coffee, tea, and baked goods.  Visit often and watch us grow.', '#5a3822', 'open-for-business.jpg'],
['<b>Our art gallery.</b> Students and local and regional artists can showcase their work. Shows change on a frequent basis so that you always have new works of art to see!', '#6f391a', 'art-wall.jpg'],
['<b>Comfortable tables and chairs</b>, located at the front so everyone can watch the constant stream of neighbors and traffic go by! Stop by and visit with a friend or meet some of your neighbors.', '#724e20', 'table-and-chairs.jpg'],
['<b>The community news area.</b> Local groups and organizations have bulletin boards to post news and information about upcoming events. There is a large <a href="calendar.html">community calendar</a> where important dates are posted.', '#495a3c', 'news-area.jpg'],
['<b>Our stage</b>, located at the front of the building. We offer live music from school groups and local artists. There will be open mic nights, poetry slams, talent shows, and even a puppet show or two.', '#495a3c', 'stage.jpg'],
['<b>A warm, inviting meeting place</b> in the back for reading, visiting with friends, or informal discussions. There are couches and chairs, and bookcases filled and periodicals and books to share. Children have a cozy place to play and read while their parents enjoy a cup of coffee.', '#5a2b33', 'couches.jpg'],
['<b>Learning</b>. In the afternoons, we hope to offer local students a tutoring service staffed by volunteers. Interested in history? The town <a href="http://www.odyssey.net/subscribers/drydenNYtownHistory/">historical society</a> will have Dryden exhibits on display.', '#7e4223', 'tutor.jpg'],
['<b>Becoming a member</b>. Individual memberships are only $10.  Membership dollars fund our programs and equipment.  Your membership allows you to vote on upcoming projects.', '#724e20', 'member.jpg']
];
var current_slide;
var slide_sel_str = '<a href="#" onclick="prev_slide()">&lt;</a>&nbsp;&nbsp;&nbsp;';
for (i=0; i<slides.length; i++) {
	slide_sel_str += "<a id=\"slide" + i + "\" href=\"#\" onclick=\"show_slide("
		+ i + ")\"> " + (i+1) + " </a>&nbsp;&nbsp;&nbsp;";
}
slide_sel_str += '<a href="#" onclick="next_slide()">&gt;</a>';
document.getElementById("imagine_sel").innerHTML = slide_sel_str;
show_slide(Math.floor(Math.random() * slides.length));

function show_slide(n) {
	if (current_slide != null)
		document.getElementById("slide"+current_slide).className = "";
	current_slide = n;
	document.getElementById("imagine").innerHTML = slides[current_slide][0];
	document.getElementById("imagine_table").style.background = slides[current_slide][1];
	document.getElementById("imagine_img").src = "images/imagine/" + slides[current_slide][2];
	document.getElementById("slide"+n).className = "imagine-highlight";
}

function prev_slide(n) {
	show_slide((current_slide + slides.length - 1) % slides.length);
}
function next_slide(n) {
	show_slide((current_slide + 1) % slides.length);
}
