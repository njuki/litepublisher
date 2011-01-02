/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

//imported from jquery
function load_script(url, callback) {
  var head = document.getElementsByTagName("head")[0] || document.documentElement;
  var script = document.createElement("script");
  script.type= 'text/javascript';
  script.async = true;
  script.src = url;
  var done = false;
  script.onload = script.onreadystatechange = function() {
    if ( !done && (!this.readyState ||
    this.readyState === "loaded" || this.readyState === "complete") ) {
      done = true;
      if (typeof callback=== "function") callback();
      // Handle memory leak in IE
      script.onload = script.onreadystatechange = null;
      if ( head && script.parentNode ) head.removeChild( script );
    }
  };
  
  head.insertBefore( script, head.firstChild );
}