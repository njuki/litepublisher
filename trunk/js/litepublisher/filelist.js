/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  $(document).ready(function() {
    $("div.filelist-all").each(function() {
      var self = $(this);
      var images = $("span.image", self);
      if (images.length <= 2) {
        var parent = images.parent();
        images.insertAfter(this);
        images.nextAll("p,div").first().addClass("files-block");
        parent.remove();
      }
      if (self.children().length == 0) self.remove();
    });
  });
}(jQuery, document, window));