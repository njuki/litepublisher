var client;

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
'litepublisher.comments.getrecent',
'litepublisher.files.getbrowser',
'litepublisher.files.getpage',
'litepublisher.files.geticons'
]
//callbackParamName: 'callback'
}); 
}

	function loadwidget(name, idtag) {
		var widget = document.getElementById(idtag);
if (!client) client = createclient();

client.litepublisher.getwidget( {
params:[name],

                 onSuccess:function(result){                     
if (result) {
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
AudioPlayer.embed(ltoptions.audiofile[0], {   
    soundFile: ltoptions.audiofile[1],
    autostart: "yes"   
});   
} else {
ltoptions.audiofile = true;
}
}

function playaudiofile(id, filename) {
loadjavascript('/js/audio-player/audio-player.js');
if (ltoptions.audiofile == undefined) {
ltoptions.audiofile = new Array(id, filename);
} else {
AudioPlayer.embed(id, {   
    soundFile: filename,
    autostart: "yes"   
});   
}
}
