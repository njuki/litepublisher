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

function playvideofile(q, filename) {
  if (ltoptions.videomutex == undefined) {
    ltoptions.videomutex = 'loading';
    $.load_script(ltoptions.files + '/js/flowplayer/flowplayer-3.2.4.min.js', function() {
      ltoptions.videomutex = 'loaded';
      playvideofile(q, filename);
    });
  } else if (ltoptions.videomutex == 'loaded') {
    $(q).flowplayer(ltoptions.files + '/js/flowplayer/flowplayer-3.2.5.swf', filename);
    $(q).flowplayer(0).load();
  }
}