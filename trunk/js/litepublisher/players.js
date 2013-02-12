;(function( $, document, window){
  $(document).ready(function() {
var audios = $("audio");
var videos = $(".videofile");  
if (audios.length || videos.length) {
//see end of file to init
  litepubl.Mediaplayer= Class.extend({
  tml: {
  video: '<video src="%%siteurl%%/files/%%file.filename%%" type="%%file.mime%%" controls="controls" autoplay="autoplay"></video>',
	          pretty: '<div id="pretty-video-holder"></div>',
	          holder: "#pretty-video-holder"
  },
  
  width: '500',
  height: '344',
  clicked: false,
  tmplink: false,
  script: false,
  
    ready: function(callback) {
  if (this.script) {
  this.script.done(callback);
  } else {
  $.load_css(ltoptions.files + "/js/mediaelement/css/mediaelementplayer.min.css");
  this.script = $.load_script(ltoptions.files + "/js/mediaelement/videoplayer.min.js", callback);
  }
  },

  init: function(audio, video) {
if (audio.length) {
var self = this;
this.ready(function() {
self.init_audio(audios);
});
}

if (videos.length) this.init_video(videos);
},

init_audio: function(links) {
links.videoplayer({
audioWidth: 400,
audioHeight: 30,
    startVolume: 1
  });
  },
  
  init_video: function(links) {
    this.tmplink = $('<a href="#custom=true&width=' + this.width + '&height=' + this.height + '"></a>').appendTo($('<div class="hidden"></div>').appendTo("body").hide());

  var self = this;
  var tml = this.tml;
  links.on("click.playvideo", function(event) {
  self.clicked = $(this);
  event.preventDefault();
  self.tmplink.click();
  });
  
    this.tmplink.prettyPhoto({
            custom_markup: tml.pretty,
      default_width: this.width,
            default_height: this.height,
      opacity: 0.80, /* Value between 0 and 1 */
      modal: true, /* If set to true, only the close button will close the window */
      deeplinking: false, /* Allow prettyPhoto to update the url to enable deeplinking. */
      keyboard_shortcuts: false, /* Set to false if you open forms inside prettyPhoto */
      show_title: false, /* true/false */
      social_tools: false,
      //hideflash: true,
     
           /* Called when prettyPhoto is closed */
      callback: function(){
        $(document).off('keydown.onEscape');
        self.clicked = false;
      },

       changepicturecallback: function(){
           $.onEscape($.prettyPhoto.close);
  self.ready(function() {
  self.init_mediaelement();
  });
  }
  });
  },
  
    init_mediaelement: function() {
    var tml = this.tml;
  var html = $.simpletml(tml.video, {
  file: this.clicked.data("file"),
siteurl: ltoptions.files
  });
  
  $(html).appendTo(tml.holder).videoplayer({
  /*
    success: function(mediaElement, domObject) {
        if (mediaElement.pluginType == 'flash') {
            mediaElement.addEventListener('canplay', function() {
                // Player is ready
                mediaElement.play();
            }, false);
        }
        else             $('.mejs-overlay-button').trigger('click');
    },
    
    error: function() {
        alert('Error setting media!');
    }
*/
});//mediaelement
}
    
});//class

  litepubl.mediaplayer = new litepubl.Mediaplayer(audios, videos);
  }
  });
})( jQuery, document, window);