/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $.simpletml = function(tml, view) {
    var __get = ("__get" in view) && (typeof view.__get === "function") ? view.__get : false;
tml = tml.replace(/[%\{]{2}(\w*)\.(\w*)[%\}]{2}/gim, function(str, obj, prop, offset, src) {
      if ((obj in view) && (typeof view[obj] === "object") && (prop in view[obj])) return view[obj][prop];
      if (__get) return __get(obj, prop);
      return str;
    });
    
return tml.replace(/[%\{]{2}(\w*)[%\}]{2}/gim, function(str, prop, offset, src) {
      if (prop in view) return view[prop];
      if (__get) return __get("", prop);
      return str;
    });
  };
})( jQuery );