/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

//imported from jquery
function load_script(url, callback) {
  var head = document.head || document.getElementsByTagName( "head" )[0] || document.documentElement;
  var script = document.createElement( "script" );
  script.async = "async";
  script.src = url;
  // Attach handlers for all browsers
  script.onload = script.onreadystatechange = function() {
    if ( !script.readyState || /loaded|complete/.test( script.readyState ) ) {
      // Handle memory leak in IE
      script.onload = script.onreadystatechange = null;
      // Remove the script
      if ( head && script.parentNode ) head.removeChild( script );
      // Dereference the script
      script = undefined;
      if (typeof callback=== "function") callback();
    }
  };
  // Use insertBefore instead of appendChild  to circumvent an IE6 bug.
  // This arises when a base node is used (#2709 and #4378).
  head.insertBefore( script, head.firstChild );
}

var jqloader = {
  jquery_loaded: false,
  items: [],
  holditems: [],
  holdready: [],
  
  loaded: function(url) {
    for (var i = this.items.length -1; i >= 0; i--) {
      if (url == this.items[i].url) return this.items[i].script;
    }
    return false;
  },
  
  load: function(url, fn) {
    if (this.jquery_loaded) {
      var script = this.loaded(url);
      if (script) {
        script.done(fn);
        return script;
      }
      script = $.getScript(url, fn);
    this.items.push({url: url, script: script});
      return script;
    } else {
    this.holditems.push({url: url, fn: fn});
    }
  },
  
  load_jquery: function(url) {
    load_script(url, this.init);
  },
  
  init: function() {
    var i, l;
    jqloader.jquery_loaded = true;
    for (i = 0, l = jqloader.holdready.length - 1; i <= l; i++) {
      $(document).ready(jqloader.holdready[i]);
    }
    jqloader.holdready = null;
    
    for (i = 0, l = jqloader.holditems.length -1; i <= l; i++) {
      var item = jqloader.holditems[i];
      jqloader.load(item.url, item.fn);
    }
    jqloader.holditems = null;
  // catch(e) { alert(e.message); }
  },
  
  ready: function(fn) {
    if (this.jquery_loaded) return $(document).ready(fn);
    this.holdready.push(fn);
  }
};

var $ = function() {
  this.ready=  jqloader.ready;
  this.getScript= jqloader.load;
  return this;
};