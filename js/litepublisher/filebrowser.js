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

document.getElementById("form").onsubmit = submitform;

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

function loadjavascript(filename) {
// check loaded scripts
if (ltoptions.scripts == undefined) ltoptions.scripts = '';
if (ltoptions.scripts.indexOf(filename) >= 0) return false;
ltoptions.scripts += filename + "\n";
      var head= document.getElementsByTagName('head')[0];
      var script= document.createElement('script');
      script.type= 'text/javascript';
      script.src= ltoptions.files + filename;
      head.appendChild(script);
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
var elem = document.getElementById("fileitem-" + id);
document.getElementById(ltoptions.idcurrentfiles).appendChild(elem.cloneNode(true));
}
}
}

post.delete= function() {
var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if((elems[i].type == 'checkbox') && (elems[i].checked == true)) {
var id = elems[i].value;
var elem = elems.getElementById("fileitem-" + id);
elem.parentNode.removeChild(elem);
}
}
}

post.getpage = function(page) {
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
var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if(elems[i].type == 'checkbox') {
elems[i].checked == true;
}
}

var elems = document.getElementById(ltoptions.idfilepage).getElementsByTagName("input");
for (var i =0, n = elems.length; i < n; i++) {
		if(elems[i].type == 'checkbox') {
elems[i].checked == false;
}
}

return true;
};
