/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var client;
var widgets = {
  items: []
};

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
    'litepublisher.files.getthemes',
    'litepublisher.files.gettags'
    ]
    //callbackParamName: 'callback'
  });
}

widgets.load = function (node, id, sitebar) {
  var comment = findcomment(node, id);
  if (! comment) return alert('Widget not found');
  var i = widgets.add(node, comment);
  
  if (client == undefined) client = createclient();
  
  client.litepublisher.getwidget( {
    params:[id, sitebar],
    
    onSuccess:function(result){
      if (result && (result != 'false')) {
        var tmp = document.createElement("div");
        tmp.innerHTML =result;
        var content = tmp.firstChild;
        content.parentNode.removeChild(content);
        comment.parentNode.replaceChild(content, comment);
        widgets.items[i][1] = content;
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
  return widgets.items.length - 1;
}

widgets.setitem = function(node, value) {
  for (var i = widgets.items.length - 1; i >= 0; i--) {
    if (node == widgets.items[i][0]) {
      widgets.items[i][1] = value;
      return;
    }
  }
  widgets.add(node, value);
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
      widget.style.display= 'block';
      node.onclick = widgets.hide;
    } else {
      widget.style.visibility = 'hidden';
      widget.style.display= 'none';
      node.onclick = widgets.show;
    }
    
} catch (e) { alert(e.message); }
}

widgets.hide = function() {
  widgets.setvisible(this, false);
}

widgets.show = function() {
  widgets.setvisible(this, true);
}

function findnextnode(node, name, value) {
  while (node = node.nextSibling) {
    if ((node.nodeName == name) && (node.nodeValue == value)) return node;
  }
  return false;
}

function findcomment(node, id) {
  var name = String.fromCharCode(35) + 'comment';
  var value = 'widgetcontent-' + id;
  if (result = findnextnode(node, name, value)) return  result;
  if (result = findnextnode(node.parentNode,name, value)) return  result;
  if (result = findnextnode(node.parentNode.parentNode, name, value)) return  result;
  return false;
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

function videoplayerloaded() {
  if (ltoptions.videofile != undefined) {
    playvideofile(ltoptions.videofile[0],ltoptions.videofile[1]);
  } else {
    ltoptions.videofile = true;
  }
}

function playvideofile(id, filename) {
  if (ltoptions.videofile == undefined) {
    ltoptions.videofile = new Array(id, filename);
    loadjavascript('/js/flowplayer/flowplayer-3.1.4.min.js');
  } else {
    flowplayer(id, ltoptions.files + "/js/flowplayer/flowplayer-3.1.5.swf", {
      clip:  {
        autoPlay: true,
        autoBuffering: true,
        bufferLength : 5
      }
    });
  }
}