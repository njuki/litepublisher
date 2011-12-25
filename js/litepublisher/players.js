/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function playaudiofile(id, filename) {
  if (ltoptions.audiomutex == undefined) {
    ltoptions.audiomutex = 'loading';
    $.load_script(ltoptions.files + '/js/audio-player/audio-player.js', function() {
      AudioPlayer.setup(ltoptions.files + "/js/audio-player/player.swf", {
        width: 290
      });
      ltoptions.audiomutex = 'loaded';
      playaudiofile(id, filename);
    });
  } else if (ltoptions.audiomutex == 'loaded') {
    AudioPlayer.embed(id, {
      soundFile: filename,
      autostart: "yes",
      initialvolume : 100
    });
  }
}

/*
function playvideofile(q, filename) {
if (ltoptions.videomutex == 'loaded') {
$(q).off("click");
    $(q).flowplayer(ltoptions.files + '/js/flowplayer/flowplayer-3.2.7.swf', filename);
    $(q).flowplayer(0).load();
  } else if (ltoptions.videomutex == "loading") {
  if (ltoptions.videomutex == undefined) {
return;
} else {
    ltoptions.videomutex = 'loading';
    $.load_script(ltoptions.files + '/js/flowplayer/flowplayer-3.2.6.min.js', function() {
      playvideofile(q, filename);
      ltoptions.videomutex = "loaded";
    });
}
}

function play_video_clicked() }{
//  if (ltoptions.videomutex == "loading") return false;
var parent = $(this).parent();
//playvideofile(parent, parent.data("link"));
return false;
}
*/
$(document).ready(function() {
$(".videofile").one("click", function() {
  var comment = widget_findcomment(this, false);
if (comment) {
var content = comment.nodeValue;
$(comment).remove();
  $(this).replaceWith(content);
}
return false;
});
});
