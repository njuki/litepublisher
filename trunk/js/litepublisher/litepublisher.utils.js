/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
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
        cache: true
      });
    },
    
    dump: function(obj) {
      var s = '';
      for (var prop in obj) s = s + prop + " = " + obj[prop] + "\n";
      alert(s);
    }
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
  $.cookie(name, value, { expires: 3650});
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
      var c = get_cookie("litepubl_user");
      if (c != '') {
        data.litepubl_user = c;
        c = get_cookie("litepubl_user_id");
        if (c != '') data.litepubl_user_id = c;
      }
      if (type != "post") type = "get";
      
      return $.ajax({
        type: type,
        url: ltoptions.url + "/admin/jsonserver.php",
        data: data,
        success: callback,
        dataType: "json",
        cache: false
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
}(jQuery, document, window));