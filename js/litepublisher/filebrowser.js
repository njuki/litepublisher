function filebrowser() {
if (ltoptions.filebrowser != undefined) return;
ltoptions.filebrowser = true;
loadjavascript('/js/swfupload/swfupload.js');
//loadjavascript('/js/swfupload/

if (client == undefined) client = createclient();
client.litepublisher.files.getbrowser( {
params:['', '', ltoptions.idpost],

                 onSuccess:function(result){                     
var div = document.getElementById("filebrowser");
div.innerHTML  = result;

var form = document.getElementById("form");
form.onsubmit = submitform;
      var hidden = document.createElement('input');
      hidden.type= 'hidden';
      hidden.name = 'fileschanged';
hidden.value = '1';
      form.appendChild(hidden);

ltoptions.idfilepages = "filepages";
ltoptions.idfilepage = "filepage";
ltoptions.idcurrentfiles = "currentfiles";

createswfu();
},

                  onException:function(errorObj){ 
                    alert("Server error");
},

onComplete:function(responseObj){ }
} );

}

function getcookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

var post = {
id: ltoptions.idpost
};

post.add= function(html) {
document.getElementById(ltoptions.idcurrentfiles).innerHTML += html;
}

post.addfrompage = function() {
var elems = document.getElementById(ltoptions.idfilepage).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if((elems[i].type == 'checkbox') && (elems[i].checked == true)) {
elems[i].checked = false;
var id = elems[i].value;
if (elem = document.getElementById("fileitem-curr-" + id)) continue;
var elem = document.getElementById("fileitem-pages-" + id);
var li = elem.cloneNode(true);
li.id = "fileitem-curr-" + id;
var check = li.getElementsByTagName("input")[0];
check.id = "filecheckbox-" + id;
document.getElementById(ltoptions.idcurrentfiles).appendChild(li);
}
}
}

post.deletechecked = function() {
var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if((elems[i].type == 'checkbox') && (elems[i].checked == true)) {
var id = elems[i].value;
var elem = document.getElementById("fileitem-curr-" + id);
elem.parentNode.removeChild(elem);
}
}
}

post.getpage = function (page) {
if (client == undefined) client = createclient();
client.litepublisher.files.getpage( {
params:['','', page],

                 onSuccess:function(result){                     
var div = document.getElementById(ltoptions.idfilepages);
div.innerHTML  = result;
},

                  onException:function(errorObj){ 
                    alert("Server error");
},

onComplete:function(responseObj){ }
} );

}

var submitform = function() {
//disable delete button
document.getElementById("deletechecked").disabled = "disabled";
var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if(elems[i].type == 'checkbox') {
elems[i].checked = true;
}
}

var elems = document.getElementById(ltoptions.idfilepage).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if(elems[i].type == 'checkbox') {
elems[i].checked = false;
}
}

return true;
};
