/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/


$(document).ready(function() {
  $(".videofile, .audiofile").one("click", function() {
    var comment = widget_findcomment(this, false);
    if (comment) {
      var content = comment.nodeValue;
      $(comment).remove();
      $(this).replaceWith(content);
    }
    return false;
  });
});