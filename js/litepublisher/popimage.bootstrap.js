/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  'use strict';
  
  $.fn.popimage = function(options) {
    options = $.extend({
      title: "",
      cursorclass: "cursor-loading",
      width: false,
      height: false
    }, options);
    
    //create circle for preload
    var prevlink = false;
    // regexp test image extension in url
    var re = /\.(jpg|jpeg|png|bmp)$/i;
    // preload cache holder
    var imgnext, imgprev;
    
    return this.each(function(){
      var link = $(this);
      var url = link.attr("href");
      if (!url || !re.test(url)) {
        url = link.data("image");
        if (!url || !re.test(url)) return;
      }
      
      link.data("image", url);
      
      if (prevlink) {
        link.data("prevlink", prevlink);
        prevlink.data("nextlink", link);
      }
      prevlink = link;
      
      link.one("mouseenter.popinit focus.popinit click.popinit", function(e) {
        var self = $(this);
        self.off(".popinit");
        self.addClass(options.cursorclass);
        if (re.test(self.attr("href"))) {
          var clicktrigger = " click";
        } else {
          // follow by link if it clicked
          if (e.type == "click") return;
          var clicktrigger = "";
        }
        
        //after load image check  is focused or inhover
        self.data("focused", e.type).on((e.type == "mouseenter" ? "mouseleave" : "blur") + ".popinit", function() {
          $(this).data("focused", false).off(".popinit");
        });
        
        var img = new Image();
        img.onload = function(){
          self.removeClass(options.cursorclass);
          //calc size
          var ratio = img.width / img.height;
          if (options.width) {
            var w = options.width;
            var h = options.height;
          } else {
            if (ratio >= 1) {
              //horizontal image, midle height and maximum width
              var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
              h = Math.floor(h / 2) - 20;
              var w = Math.floor(h * 4 /3);
            } else {
              //vertical image, midle width and maximum height
              var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
              w = Math.floor(w / 2) - 20;
              var h = Math.floor(w / 4 *3);
            }
          }
          
          if ((img.width <= w) && (img.height <= h)) {
            w = img.width;
            h = img.height;
          } else {
            if (w /h > ratio) {
              w = Math.floor(h *ratio);
            } else {
              h = Math.floor(w / ratio);
            }
          }
          
          var title = self.attr("title");
          if (re.test(title)) title = options.title;
          
          self.popover({
            container: 'body',
            content: '<img src="' + url + '" width="' + w + '" height="' + h + '" />',
            delay: 120,
            html:true,
            placement: 'auto ' + (ratio >= 1 ? 'bottom' : 'right'),
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
            imgnext = new Image();
            imgnext.src = preload.data("image");
          }
          
          var preload = link.data("prevlink");
          if (preload) {
            imgprev = new Image();
            imgprev.src = preload.data("image");
          }
          
        litepubl.stat('popimage', {src: self.data("image")});
        };
        
        img.onerror = function() {
          //alert("Error load image");
        };
        
        img.src =           self.data("image");
        return false;
      });
    });
  };
  
  $.ready2(function(){
    if ("popover" in $.fn) $("a.photo").popimage();
  });
})( jQuery, window, document);