/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  $(document).ready(function() {
    litepubl.ulogin = new litepubl.Ulogin();
  });
  
  litepubl.Ulogin = Class.extend({
registered: false,
script: false,
dialogopened: false,
    html: '<div style="display:block;overflow:hidden;width:300px;height:50px;">\
<div id="ulogin-holder" data-ulogin="display=small;fields=first_name,last_name;optional=email,phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=%%redirurl%%;%%callback%%"></div>',

    init: function() {
this.registered = $.cookie('litepubl_user');
      this.html = this.html.replace(/%%redirurl%%/gim, encodeURIComponent(ltoptions.url + "/admin/ulogin.php?backurl="));
      var self = this;
      $('a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]').click(function() {
        self.open($(this).attr("href"));
        return false;
      });

$("#ulogin-comment-button").click(function() {
self.open(location.href);
return false;
});
    },
    
    open: function(url, callback) {
if (this.dialogopened) return false;
set_cookie('backurl', url);
var self = this;
self.ready(function() {
self.dialogopened = true;
var html = self.html.replace(/backurl%3D/gim, 'backurl%3D' + encodeURIComponent(encodeURIComponent(url)));
if ($.isFunction(callback)) {
html = html.replace(/%%callback%%/gim, "callback=ulogincallback");
window.ulogincallback = function(token) {
$.prettyPhoto.close();
try {
callback(token);
        } catch(e) {erralert(e);}
};
} else {
html = html.replace(/%%callback%%/gim, "");
}

      $.prettyPhotoDialog({
        title: lang.ulogin.title,
        html: html,
        width: 300,
close: function() {
self.dialogopened = false;
},

open: function() {
uLogin.customInit('ulogin-holder');
},

        buttons: [{
          title: lang.dialog.close,
          click: $.proxy($.prettyPhoto.close, $.prettyPhoto)
        }]
      });
});
    },

ready: function(callback) {
if (this.script) return this.script.done(callback);
return this.script = $.load_script('http://ulogin.ru/js/ulogin.js', callback);
},

auth: function(token, json_callback, callback) {
var self =this;
return $.litejson({method: "ulogin_auth", token: token, callback: json_callback ? json_callback : false}, function(r) {
set_cookie("litepubl_user_id", r.iduser);
set_cookie("litepubl_user", r.pass);
set_cookie("litepubl_regservice", r.regservice);
self.registered = true;
if (r.callback&& $.isFunction(callback)) callback(r.callback);
});
},

login: function(json_callback, callback) {
var self = this;
self.open(location.href, function(token) {
self.auth(token, json_callback, callback);
});
}
    
  });
  
}(jQuery, document, window));