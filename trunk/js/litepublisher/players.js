;(function( $, document, window){
  $(document).ready(function() {
var audios = $(".audiofile");
var videos = $(".videofile");  
if (audio.length || videos.length) {
//see end of file to init
  litepubl.Mediaplayer= Class.extend({
  script: false;
  ready: function(callback) {
  if (this.script) {
  this.script.done(callback);
  } else {
  $.load_css(ltoptions.files + "/js/mediaelement/css/mediaelementplayer.min.css");
  this.script = $.load_script(ltoptions.files + "/js/mediaelement/mediaelement-and-player.min.js", callback);
  }
  }
  };
  
init: function(audio, video) {
if (audio.length) {
var self = this;
this.ready(function() {
self.init_audio(audios);
};
}

if (videos.length) this.init_video(videos);
},

init_audio: function(links) {
  $audio = [<audio id="player-$id" src="$link" type="$mime" controls="controls"></audio>]
  $("audio", media).mediaelementplayer({
  pluginPath: ltoptions.files + "/js/mediaelement/",
audioWidth: 400,
audioHeight: 30,
    startVolume: 1,
        features: ['playpause','progress','current','volume']
  });
  },
  
  init_video: function(links) {
  }
  
  };
  
  litepubl.mediaplayer = new litepubl.Mediaplayer(audios, videos);
  }
  });
})( jQuery, document, window);