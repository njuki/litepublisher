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
    html: '<div><p>%%lang.subtitle%%</p>' +
'<div id="ulogin-dialog">' +
'<div id="ulogin-holder" data-ulogin="display=small;fields=first_name,last_name;optional=email,phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=%%redirurl%%;%%callback%%"></div></div>' +
'<div><a href="%%url%%">%%lang.emaillogin%%</a></div></div>',

    init: function() {
this.registered = $.cookie('litepubl_user');
if (this.registered) return;
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
if (!url) url = ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href);
var html = self.html.replace(/%%lang.emaillogin%%/gim, lang.ulogin.emaillogin)
.replace(/%%lang.subtitle%%/gim, lang.ulogin.subtitle)
.replace(/%%url%%/gim, url);

if ($.isFunction(callback)) {
html = html.replace(/%%callback%%/gim, "callback=ulogincallback")
.replace(/%%redirurl%%/gim, '');
window.ulogincallback = function(token) {
$.closedialog();
try {
callback(token);
        } catch(e) {erralert(e);}
};
} else {
html = html.replace(/%%callback%%/gim, "")
.replace(/%%redirurl%%/gim, encodeURIComponent(ltoptions.url + "/admin/ulogin.php?backurl=" + encodeURIComponent(url)));
}

      $.litedialog({
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
          click: $.closedialog
        }]
      });
});
    },

ready: function(callback) {
if (this.script) return this.script.done(callback);
return this.script = $.load_script('http://ulogin.ru/js/ulogin.js', callback);
},

auth: function(token, remote_callback, callback) {
var self =this;
return $.litejson({method: "ulogin_auth", token: token, callback: remote_callback ? remote_callback : false}, function(r) {
set_cookie("litepubl_user_id", r.id);
set_cookie("litepubl_user", r.pass);
set_cookie("litepubl_regservice", r.regservice);
self.registered = true;
if ($.isFunction(callback)) {
if (r.callback) {
callback(r.callback);
} else {
callback();
}
}
});
},

login: function(backurl, remote_callback, callback) {
var self = this;
self.open(backurl, function(token) {
self.auth(token, remote_callback, callback);
});
}
    
  });
  
}(jQuery, document, window));