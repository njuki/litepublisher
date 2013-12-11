/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  $.fn.popimage = function(options) {
    options = $.extend({
      title: "",
      cursorclass: "cursor-loading",
      width: 40,
      height: 30
    }, options);
    
    //create circle for preload
    var prevlink = false;
    // regexp test image extension in url
    var re = /\.(jpg|jpeg|png|bmp)$/;
    return this.each(function(){
      var link = $(this);
      if (prevlink) {
        link.data("prevlink", prevlink);
        prevlink.data("nextlink", link);
      }
      prevlink = link;
      
      link.one("mouseenter.popinit focus.popinit click.popinit", function(e) {
        var self = $(this);
        self.off(".popinit");
        self.addClass(options.cursorclass);
        var url = self.attr("href");
        if (re.test(url)) {
          var clicktrigger = " click";
        } else {
          // follow by link if it clicked
          if (e.type == "click") return;
          var clicktrigger = "";
          url = self.data("image");
        }
        
//after load image check  is focused or inhover
self.data("focused", e.type).on((e.type == "mouseenter" ? "mouseleave" : "blur") + ".popinit", function() {
$(this).data("focused", false).off(".popinit");
});

        var img = new Image();
        img.onload = function(){
          self.removeClass(options.cursorclass);
          //calc size
          if (options.width < 100) {
            var w = Math.floor($(window).width() * options.width / 100);
            var h = Math.floor(w * options.height / options.width);
          } else {
            var w = options.width;
            var h = options.height;
          }
          
          if ((img.width <= w) && (img.height <= h)) {
            w = img.width;
            h = img.height;
          } else {
            var ratio = img.width / img.height;
            if (w /h > ratio) {
              w = Math.floor(h *ratio);
            } else {
              h = Math.floor(w / ratio);
            }
          }
          
          var title = self.attr("title");
          if (re.test(title)) title = options.title;
          // cut long title
          //if (title.length * 14 > w) title = title.substring(0, Math.floor(w / 14 - 5))  + '...';
          
          self.popover({
            container: 'body',
            content: '<img src="' + url + '" width="' + w + '" height="' + h + '" />',
          //delay: { show: 100, hide: 100 },
            html:true,
            placement: 'auto bottom',
            template: '<div class="popover popover-image"><div class="arrow"></div>' +
            '<h3 class="popover-title" style="max-width:' + w + 'px;"></h3>' +
            '<div class="popover-content"></div></div>',
            title: title,
            trigger: 'hover focus' + clicktrigger
          });
          
          if (self.data("focused")) self.trigger(self.data("focused"));
          
          //preload
          var preload = self.data("nextlink");
          if (preload) {
            var imgnext = new Image();
            imgnext.src = preload.attr("href");
          }
          
          var preload = link.data("prevlink");
          if (preload) {
            var imgprev = new Image();
            imgprev.src = preload.attr("href");
          }
        };
        
        img.onerror = function() {
          alert("Error load image");
        };
        
        img.src = url;
        return false;
      });
    });
  };
  
  ready2(function(){
    $("a.photo").popimage();
  });
})( jQuery, window, document);