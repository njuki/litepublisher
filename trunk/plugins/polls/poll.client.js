var pollclient = {
client: null,
cookie: ''
};

pollclient.create= function () {
this.client= new rpc.ServiceProxy(ltoptions.pingback, {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.poll.sendvote',
'litepublisher.poll.getcookie'
]
//callbackParamName: 'callback'
}); 
}

pollclient.sendvote = function (name, idtag) {
		var widget = resolvetag(idtag, 'ul');
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

function findnexttag(node, tag) {
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
