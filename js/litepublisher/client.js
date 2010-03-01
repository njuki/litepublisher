var client;
var widgets = { items: [] };

function createclient() {
return new rpc.ServiceProxy(ltoptions.pingback, {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.getwidget',
'litepublisher.moderate',
'litepublisher.deletecomment', 
'litepublisher.comments.setstatus',
'litepublisher.comments.add',
'litepublisher.comments.get',
'litepublisher.comments.edit',
'litepublisher.comments.reply',
'litepublisher.comments.getrecent',
'litepublisher.files.getbrowser',
'litepublisher.files.getpage',
'litepublisher.files.geticons',
'litepublisher.files.getthemes'
]
//callbackParamName: 'callback'
}); 
}

widgets.load = function (node, name, idtag) {
		var widget = resolvetag(idtag, 'ul');
if (! widget) return alert('Widget not found');
widgets.add(node, widget);

if (client == undefined) client = createclient();

client.litepublisher.getwidget( {
params:[name],

                 onSuccess:function(result){                     
if (result && (result != 'false')) {
widget.innerHTML = result;
} else {
                    //alert('problem');
}
},

                  onException:function(errorObj){ 
//                    alert('error'.notdeleted);
},

onComplete:function(responseObj){ }
} );

}

widgets.add = function(node, widget) {
node.onclick = widgets.hide;
widgets.items.push([node, widget]);
}

widgets.setvisible = function(node, value) {
try {
for (var i = widgets.items.length - 1; i >= 0; i--) {
if (node == widgets.items[i][0]) {
var widget = widgets.items[i][1];
break;
}
}

if (value) {
widget.style.visibility = 'visible'; 
node.onclick = widgets.hide;
} else {
widget.style.visibility = 'hidden'; 
node.onclick = widgets.show;
}

} catch (e) { alert(e.message); }
}

widgets.hide = function() {
widgets.setvisible(this, false);
}

widgets.show = function(node) {
widgets.setvisible(this, true);
}

function findnexttag (node, tag) {
while (node = node.nextSibling) {
if (node.tagName.toLowerCase() == tag) return node;
}
return false;
}

function resolvetag(id, tag) {
try {
if (typeof(id) == 'string') {
return document.getElementById(id);
} else {
if (result = findnexttag(id, tag)) return  result;
if (result = findnexttag(id.parentNode,tag)) return  result;
if (result = findnexttag(id.parentNode.parentNode, tag)) return  result;
}
return false;
} catch (e) {
return false;
}
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
return true;
   }

function audioplayerloaded() {
            AudioPlayer.setup(ltoptions.files + "/js/audio-player/player.swf", {   
                width: 290   
            });   

if (ltoptions.audiofile != undefined) {
playaudiofile(ltoptions.audiofile[0],ltoptions.audiofile[1]);
} else {
ltoptions.audiofile = true;
}
}

function playaudiofile(id, filename) {
if (ltoptions.audiofile == undefined) {
ltoptions.audiofile = new Array(id, filename);
loadjavascript('/js/audio-player/audio-player.js');
} else {
AudioPlayer.embed(id, {   
    soundFile: filename,
    autostart: "yes",
 initialvolume : 100
});   
}
}
