	function loadwidget(idtag, link) {
		var cont = document.getElementById(idtag);
var ajax = new sack();
	ajax.method = 'get';
	ajax.onCompletion = function () {
//					cont.innerHTML = ajax.response; 
alert('hi');
document.getElementById("widgetcategories").innerHTML = ajax.response; 
				}

	ajax.runAJAX(link);
}