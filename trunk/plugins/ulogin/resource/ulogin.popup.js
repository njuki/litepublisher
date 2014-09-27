/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  "use strict";
  
  $(document).ready(function() {
    litepubl.ulogin = new litepubl.Ulogin();
  });
  
  litepubl.Ulogin = Class.extend({
    registered: false,
    logged: false,
    script: false,
    dialogopened: false,
    html: '<div><p>%%lang.subtitle%%</p>' +
    '<div id="ulogin-dialog">' +
    '<div id="ulogin-holder" data-ulogin="display=small;fields=first_name,last_name;optional=email,phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=%%redirurl%%;%%callback%%"></div></div>' +
    '<div><a href="%%url%%" id="email-login">%%lang.emaillogin%%</a></div></div>',
    
    init: function() {
      this.registered = litepubl.getuser().pass ? 1 : 0;
      if (this.registered) return;
      var self = this;
      $('a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]').click(function() {
        var url = $(this).attr("href");
        if (litepubl.is_admin_url(url)) self.open(url);
        return false;
      });
      
      $("#ulogin-comment-button").click(function() {
      self.onlogin({method: "comments_get_logged", idpost: ltoptions.idpost}, function(r) {
          if (typeof r === "object") {
            if ("error" in r) {
              $.messagebox(lang.dialog.error, r.error);
            } else if ("mesg" in r) {
              $("#before-commentform").html(r.mesg);
            }
          }
        });
        
        return false;
      });
    },
    
    open: function(openurl, callback, emailcallback) {
      if (this.dialogopened) return false;
      set_cookie('backurl', openurl);
      var self = this;
      self.ready(function() {
        self.dialogopened = true;
        var url= openurl ? openurl : ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href);
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
            
            if (!$.isFunction(emailcallback)) {
              emailcallback = function() {
                window.location = openurl;
              };
            }
            
            $("#email-login").click(function() {
              $.closedialog(function() {
                if (!("emailauth" in litepubl)) litepubl.emailauth = new litepubl.Emailauth();
                litepubl.emailauth.open(emailcallback);
              });
              return false;
            });
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
      return this.script = $.load_script('//ulogin.ru/js/ulogin.js', callback);
    },
    
    auth: function(token, remote_callback, callback) {
      var self =this;
    return $.litejsonpost({method: "ulogin_auth", token: token, callback: remote_callback ? remote_callback : false}, function(r) {
        litepubl.user = r;
        set_cookie("litepubl_user_id", r.id);
        set_cookie("litepubl_user", r.pass);
        set_cookie("litepubl_regservice", r.regservice);
        self.registered = true;
        self.logged = true;
        if ($.isFunction(callback)) {
callback("callback" in r ? r.callback : undefined);
        }
      });
    },
    
    login: function(backurl, remote_callback, callback) {
      var self = this;
      self.open(backurl, function(token) {
        self.auth(token, remote_callback, callback);
      }, callback);
    },
    
    logon: function(remote_callback , callback) {
      var self = this;
      self.open('', function(token) {
        self.auth(token, remote_callback , callback);
      }, function() {
if (remote_callback) {
        $.litejsonpost(remote_callback, callback);
} else {
callback();
}
      });
    },

    onlogin: function(remote_callback , callback) {
if (!this.registered) return        this.logon(remote_callback, callback);

      var self = this;
if (this.logged) {
if (remote_callback) {
        $.litejsonpost(remote_callback, callback);
} else {
callback('logged');
return true;
}
} else {
    $.litejson({method: "check_logged", callback: remote_callback ? remote_callback : false}, function(r) {
        if (r.result == "true") {
          self.logged = true;
callback("callback" in r ? r.callback : undefined);
} else {
       self.logon(remote_callback, callback);
}
});
}

return false;
}

  });//class
  
}(jQuery, document, window));