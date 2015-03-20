/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';
  
  $.fn.flytext = function(options) {
options = $.extend({
text: "",
target: false,
 callback: false,
speed: 1400
}, options);

    var div = $('<div>' + options.text + '</div>').appendTo('body')
.css({
display: "block",
overflow: "hidden",
        position : "absolute",
        opacity: 0.5,
        zIndex : 2050,
  //border: 1px solid transparent;
borderWidth: "1px",
borderStyle: "solid",
borderColor: "black",
  borderRadius: "4px"
})
.data("callback", options.callback);

    div.offset(this.offset());
    div.animate($(options.target).offset(), options.speed, function() {
      var self = $(this);
var callback = self.data("callback");
self.remove();
      if (callback && $.isFunction(callback)) callback();
    });

return this;
  };
  
})( jQuery);