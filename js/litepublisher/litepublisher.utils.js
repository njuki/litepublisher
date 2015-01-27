/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  var rurl = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/;
  var dom = rurl.exec(ltoptions.url);
  var href = rurl.exec(location.href.toLowerCase()) || [];
  if (dom[2] != href[2]) {
    ltoptions.url = ltoptions.url.replace(dom[2], href[2]);
    ltoptions.files = ltoptions.files.replace(dom[2], href[2]);
  }
  
  //without protocol for ajax calls
  ltoptions.ajaxurl = ltoptions.url.substring(ltoptions.url.indexOf(':') +1);
  
  //litepublisher namespace
  window.litepubl = {
    guid: $.now(),
  tml: {}, //namespace for templates
    adminpanel: false,
    is_adminpanel:  function() {
      if (litepubl.adminpanel !== false) return litepubl.adminpanel;
      return litepubl.adminpanel = litepubl.is_admin_url(location.href);
    },
    
    is_admin_url: function(url) {
      url = url.toLowerCase();
      var i = url.indexOf('://');
      if (i >= 0) url = url.substring(i + 4);
      var path = url.split('/');
      if ((path.length <= 2) || (path[1] != 'admin') || (path[2] == '')) return 0;
      return /^(login|logout|password|reguser)$/.test(path[2]) ? 0 : 1;
    },
    
    user: 0,
    getuser: function() {
      var self = litepubl;
      if (self.user) return self.user;
      return self.user = {
        id: parseInt($.cookie('litepubl_user_id')),
        pass: $.cookie('litepubl_user'),
        regservice: $.cookie('litepubl_regservice')
      };
    },
    
    //forward declaration for future plugins as yandex metrika or google analitik
  stat: function(name, param) {},
    getjson: function(data, callback) {
      return $.ajax({
        type: "get",
        url: ltoptions.ajaxurl + "/admin/jsonserver.php",
        data: data,
        success: callback,
        dataType: "json",
        cache: ("cache" in data ? data.cache : true)
      });
    },
    
  _onnew: {},
  _oninit: {},
    newinstance: function(varname, fn) {
      var self = litepubl;
      if (varname in self._onnew) {
      var opt = {varname: varname, fn: fn};
        self._onnew[varname].fire(opt);
        delete self._onnew[varname];
        fn = opt.fn;
      }
      
      var obj = new fn();
      self[varname] = obj;
      if (varname in self._oninit) {
        self._oninit[varname].fire(obj);
        delete self._oninit[varname];
      }
      return obj;
    },
    
    oninit: function(varname, fn) {
      var self = litepubl;
      if (varname in self) return fn(self[varname]);
      if (!(varname in self._oninit)) self._oninit[varname] = $.Callbacks();
      self._oninit[varname].add(fn);
    },
    
    onnew: function(varname, fn) {
      var self = llitepubl;
      if (varname in self) return false;
      if (!(varname in self._onnew)) self._onnew[varname] = $.Callbacks();
      self._onnew[varname].add(fn);
    }
    
  };
  
  window.dump = function(obj) {
    alert(JSON.stringify(obj));
  };
  
  window.get_get=  function (name, url) {
    if (url) {
      var q = url.substring(url.indexOf('?') + 1);
    } else {
      var q = window.location.search.substring(1);
    }
    
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
      expires: expires ? expires : 3650,
      secure: "secure" in ltoptions ? ltoptions.secure : false
    });
  };
  
  window.$ready = function(fn) {
    $(document).ready(fn);
  };
  
  window.erralert = function(e) {
    alert('error ' + e.message);
  };

    var ready2callback = false;
  $.extend({
    ready2: function(fn) {
      if (!ready2callback) {
        ready2callback =  $.Deferred();
        var ready2resolve = function() {
          setTimeout(function() {
            ready2callback.resolve();
          }, 0);
        };
        
        if ($.isReady) {
          $(document).ready(ready2resolve);
        } else {
          //.on('ready') call after $(document).ready
          $(document).on('ready', ready2resolve);
        }
      }
      
      ready2callback.done(fn);
    },
    
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
    
    hasprop: function(obj, prop) {
      return (typeof obj === "object") && (prop in obj);
    },
    
    jsonrpc: function(args) {
      args = $.extend({
        type: 'post',
        method: '',
      params: {},
        slave: false,
        callback: false,
        error: false,
        cache: false
      }, args);
      
      var params = args.params;
      var user = litepubl.getuser();
      if (user.id) {
        params.litepubl_user_id = user.id;
        params.litepubl_user = user.pass;
        params.litepubl_user_regservice = user.regservice;
      }
      
      if (args.slave) {
        params.slave = {
          method: args.slave.method,
          params: args.slave.params
        };
      }
      
      var ajax = {
        type: args.type,
        url: ltoptions.ajaxurl + "/admin/jsonserver.php",
        cache: args.cache,
        dataType: "json",
        success: function(r) {
          if (typeof r === "object") {
            if ("result" in r) {
              if ($.isFunction(args.callback)) args.callback(r.result);
              if (args.slave && $.hasprop(r.result, 'slave')) {
                var slave = args.slave;
                var slaveresult = r.result.slave;
                if ($.hasprop(slaveresult, 'error')) {
                  if ($.hasprop(slave, 'error') && $.isFunction(slave.error)) slave.error(slaveresult.error.message, slaveresult.error.code);
                } else {
                  if ($.hasprop(slave, 'callback') && $.isFunction(slave.callback)) slave.callback(slaveresult);
                }
              }
            } else if ("error" in r) {
              if ($.isFunction(args.error)) args.error(r.error.message, r.error.code);
            }
          }
        }
      };
      
      if (args.type == 'post') {
        if (!args.cache) ajax.url = ajax.url + '?_=' + litepubl.guid++;
        ajax.data = $.toJSON({
          jsonrpc: "2.0",
          method: args.method,
          params: params,
          id: litepubl.guid++
        });
      } else {
        ajax.type = 'get';
        params.method = args.method;
        ajax.data = params;
      }
      
      return $.ajax(ajax).fail( function(jq, textStatus, errorThrown) {
        if ($.isFunction(args.error)) args.error(jq.responseText, jq.status);
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
    var result = false;
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

$.css_loader = {
links: [],
counter: 0,
timer: false,

add: function(url) {
      var link = $('<link rel="stylesheet" type="text/css" media="only x" href="' + url + '" />').appendTo("head:first").get(0);

if (!this.ready(link)) {
this.links.push(link);
this.counter = 20;
if (!this.timer) this.timer = setInterval($.proxy(this.wait, this), 50);
}
},

wait: function() {
for (var i = this.links.length - 1; i >= 0; i--) {
if (this.ready(this.links[i])) {
this.links.splice(i, 1);
} else if (!this.counter) {
this.links[i].media = "all";
}
}

if (!this.links.length || (this.counter-- < 0)) {
    clearInterval(this.timer);
this.timer = 0;
this.counter = 0;
}
},
    
ready: function(link) {
var url = link.href;
	var sheets = document.styleSheets;
		for( var i = 0, l = sheets.length; i < l; i++ ){
			if( sheets[ i ].href && sheets[ i ].href.indexOf(url) >= 0 ){
link.media = "all";
return true;
}
}

return false;
    }  

};

    $.load_css = $.proxy($.css_loader.add, $.css_loader);

}(jQuery, document, window));