(function( $, document){
  $(document).ready(function() {
  var media = $("audio,video");
  if (media.length) {
  
  var player_loader = {
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
  
  player_loader.ready(function() {
  $audio = [<audio id="player-$id" src="$link" type="$mime" controls="controls"></audio>]
  $("audio", media).mediaelementplayer({
  pluginPath: ltoptions.files + "/js/mediaelement/",
audioWidth: 400,
audioHeight: 30,
    startVolume: 1,
        features: ['playpause','progress','current','volume']
  });
  
  });
  }
  });
})( jQuery, document);