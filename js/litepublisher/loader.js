/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
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
  is_ready: false,
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
    if (jqloader.jquery_loaded) {
      var script = jqloader.loaded(url);
      if (script) {
        script.done(fn);
      } else {
        script = $.load_script(url, fn);
      jqloader.items.push({url: url, script: script});
      }
      return script;
    } else {
    jqloader.holditems.push({url: url, fn: fn});
    }
  },
  
  load_jquery: function(url) {
    load_script(url, jqloader.init);
  },
  
  init: function() {
    jqloader.jquery_loaded = $.fn  !=undefined;
    if (!jqloader.jquery_loaded) return;
    if (window.jquery == undefined) window.jquery = $;
    var i, l, a = [];
    for (i = 0, l = jqloader.holditems.length -1; i <= l; i++) {
      var item = jqloader.holditems[i];
      jqloader.load(item.url, item.fn);
    }
    jqloader.holditems = null;
    
    window.setTimeout(function() {
      for (i = 0, l = jqloader.items.length -1; i <= l; i++) {
        a.push(jqloader.items[i].script);
      }
      
      var w = $.when.apply($, a);
      w.done(function() {
        jqloader.is_ready = true;
        for (i = 0, l = jqloader.holdready.length - 1; i <= l; i++) {
          $(document).ready(jqloader.holdready[i]);
        }
        jqloader.holdready = null;
      });
    }, 20);
  },
  
  ready: function(fn) {
    if (jqloader.is_ready) {
      $(document).ready(fn);
    } else {
      jqloader.holdready.push(fn);
    }
  }
};

var $ = function(fn) {
  if (typeof fn === "function") jqloader.ready(fn);
  this.ready=  jqloader.ready;
  this.getScript= jqloader.load;
  this.load_script = jqloader.load;
  return this;
};