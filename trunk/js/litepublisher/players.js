function playaudiofile(id, filename) {
if (ltoptions.audiomutex == undefined) {
    ltoptions.audiomutex = 'loading';
jQuery.getScript(ltoptions.files + '/js/audio-player/audio-player.js', function() {
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

function playallaudio() {
if (ltoptions.audiomutex == undefined) {
    ltoptions.audiomutex = 'loading';
jQuery.getScript(ltoptions.files + '/js/audio-player/audio-player.js', function() {
  AudioPlayer.setup(ltoptions.files + "/js/audio-player/player.swf", {
    width: 290
  });
    ltoptions.audiomutex = 'loaded';  
playallaudio();
});
} else if (ltoptions.audiomutex == 'loaded') {
ltoptions.audiomutex = 'all';
    $("*[rel~='audio']").each(function() {
var id = this.id;
var filename = $(this).children('a').attr("href");
    AudioPlayer.embed(id, {
      soundFile: filename,
      autostart: "no",
      initialvolume : 100
    });
});
  }
}

function playvideofile(q, filename) {
if (ltoptions.videomutex == undefined) {
    ltoptions.videomutex = 'loading';
jQuery.getScript(ltoptions.files + '/js/flowplayer/flowplayer-3.2.4.min.js', function() {
    ltoptions.videomutex = 'loaded';
playvideofile(q, filename);
});
} else if (ltoptions.videomutex == 'loaded') {
    $(q).flowplayer(ltoptions.files + '/js/flowplayer/flowplayer-3.2.5.swf', filename);
  }
}

//$(document).ready(function() { playallaudio(); });
