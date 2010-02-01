function browsefiles() {
var div = document.getElementById("browsefiles");
div.innerHTML  = '		<div id="divSWFUploadUI">' +
'			<div class="fieldset  flash" id="fsUploadProgress">'+
'			<span class="legend">Upload Queue</span>' +
'			</div>' +
'			<p id="divStatus">0 Files Uploaded</p>' +
'			<p>' +
'				<span id="spanButtonPlaceholder"></span>' +
'				<input id="btnCancel" type="button" value="Cancel All Uploads" disabled="disabled" style="margin-left: 2px; height: 22px; font-size: 8pt;" /> ' +
'				<br />' +
'			</p>' +
'		</div>';

var span = document.getElementById("spanButtonPlaceholder");
span.innerHTML  = "inner";

createswfu();
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

