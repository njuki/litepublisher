/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  var rurl = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/;
  var dom = rurl.exec(ltoptions.url);
  var href = rurl.exec(location.href.toLowerCase()) || [];
  if (dom[2] != href[2]) {
    ltoptions.url = ltoptions.url.replace(dom[2], href[2]);
    ltoptions.files = ltoptions.files.replace(dom[2], href[2]);
  }
  
  //litepublisher namespace
  window.litepubl = {
  tml: {}, //namespace for templates
    getjson: function(data, callback) {
      return $.ajax({
        type: "get",
        url: ltoptions.url + "/admin/jsonserver.php",
        data: data,
        success: callback,
        dataType: "json",
        cache: ("cache" in data ? data.cache : true)
      });
    }
  };
  
  window.dump = function(obj) {
    alert(JSON.stringify(obj));
  };
  
  window.get_get=  function (name) {
    var q = window.location.search.substring(1);
    var vars = q.split('&');
    for (var i=0, l=  vars.length; i < l; i++) {
      var pair = vars[i].split('=');
      if (name == pair[0]) return decodeURIComponent(pair[1]);
    }
    return false;
  };
  
  //cookies
  window.get_cookie = function(name) {
    return $.cookie(name);
  };
  
  window.set_cookie = function(name, value, expires){
    $.cookie(name, value, {
      path: '/',
      expires: expires ? expires : 3650
    });
  };
  
  window.$ready = function(fn) {
    $(document).ready(fn);
  };
  
  window.erralert = function(e) {
    alert('error ' + e.message);
  };
  
  $.extend({
    load_script: function( url, callback ) {
      return $.ajax({
        type: 'get',
        url: url,
        data: undefined,
        success: callback,
        dataType: "script",
        cache: true
      });
    },
    
    load_css: function(url) {
      $('<link rel="stylesheet" type="text/css" href="' + url + '" />').appendTo("head:first");
    }  ,
    
    uiscript: false,
    load_ui: function(fn) {
      if (!$.uiscript) {
        var dir = ltoptions.files + '/js/jquery/ui-' + ltoptions.jqueryui_version;
        $.load_css(dir + '/redmond/jquery-ui-' + ltoptions.jqueryui_version + '.custom.css');
        $.uiscript = $.load_script(dir + '/jquery-ui-' + ltoptions.jqueryui_version + '.custom.min.js');
      }
      if ($.isFunction(fn)) $.uiscript.done(fn);
    },
    
    litejson: function(data, callback) {
      return $.litejsontype("get", data, callback);
    },
    
    litejsonpost: function(data, callback) {
      return $.litejsontype("post", data, callback);
    },
    
    litejsontype: function(type, data, callback) {
      var c = $.cookie("litepubl_user");
      if (c != '') {
        data.litepubl_user = c;
        c = $.cookie("litepubl_user_id");
        if (c != '') data.litepubl_user_id = c;
      }
      if (type != "post") type = "get";
      
      var         cache =  "cache" in data ? data.cache : false;
      var nocache = '';
      if (!cache && (type == "post")) {
        if (!("random" in litepubl)) litepubl.random = $.now();
        nocache = '?_=' + litepubl.random++;
      }
      
      return $.ajax({
        type: type,
        url: ltoptions.url + "/admin/jsonserver.php" + nocache,
        data: data,
        success: callback,
        dataType: "json",
        cache: cache
      });
    },
    
    onEscape: function (callback) {
      $(document).off('keydown.onEscape').on('keydown.onEscape',function(e){
        if (e.keyCode == 27) {
          if ($.isFunction(callback)) callback();
          e.preventDefault();
          $(document).off('keydown.onEscape');
        }
      });
    }
    
  });
  
  $.fn.replaceComment= function(html) {
    var result = html == undefined ? $(this.get(0).nodeValue) : $(html);
    $(this).before(result).remove();
    return result;
  };

$.fn.findcomment = function(id) {
return $.findcomment(this.get(0), id ? 'widgetcontent-' + id : false);
  };
  
  $.findcomment = function(node, text) {
    var result = false;
  do {
    if (result = $.nextcomment(node, text)) return result;
  } while (node = node.parentNode);
  return false;
};

$.nextcomment = function(node, text) {
do {
      if (node.nodeType  == 8) {
        if (!text || (text == node.nodeValue)) return node;
      }
      
                 if (node.firstChild) {
                  if (result = $.nextcomment(node.firstChild, text)) return result;
                  }
} while (node = node.nextSibling);

    return false;
};

  }(jQuery, document, window));