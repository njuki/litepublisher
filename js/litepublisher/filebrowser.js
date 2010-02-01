function filebrowser() {
if (ltoptions.filebrowser != undefined) return;
ltoptions.filebrowser = true;
loadjavascript('/js/swfupload/swfupload.js');
//loadjavascript('/js/swfupload/

if (client == undefined) client = createclient();
client.litepublisher.getfilebrowser( {
params:['', '', ltoptions.idpost],

                 onSuccess:function(result){                     
var div = document.getElementById("filebrowser");
div.innerHTML  = result;

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

