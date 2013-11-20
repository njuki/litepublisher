/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
$.fn.popimage = function(options) {
options = $.extend({
title: "Image",
cursorclass: "cursor-loading",
width: 40,
height: 30
}, options);

//create circle for preload
var prevlink = false;
return this.each(function(){
var link = $(this);
    if (prevlink) { 
    link.data("prevlink", prevlink);
    prevlink.data("nextlink", link);
}
prevlink = link;

link.one("mouseenter.popinit focus.popinit click.popinit", function() {
var self = $(this);
self.off(".popinit");
self.addClass(options.cursorclass);
var url = self.attr("href");
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
if (/\.(jpg|jpeg|png|bmp)$/.test(title)) title = options.title;
self.popover({
container: 'body',
content: '<img src="' + url + '" width="' + w + '" height="' + h + '" />',
//delay: { show: 100, hide: 100 },
   html:true,
placement: 'auto bottom',
 title: title,
trigger: 'hover focus click'
   });
   
   self.popover('show');
   
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

   $(document).ready(function(){
   $("a.photo").popimage();
   });
})( jQuery, window, document);