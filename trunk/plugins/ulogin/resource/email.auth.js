/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  $(document).ready(function() {
    litepubl.emailauth = new litepubl.Emailauth();
  });
  
  litepubl.Emailauth = Class.extend({
callback: false,

    init: function() {
this.registered = litepubl.getuser().pass ? 1 : 0;
if (this.registered) return;
},

getradio: function(value) {
return $.simpletml(litepubl.tml.radio, {
name: 'authtype',
value: value,
title: lang.emailauth[value]
});
},

    open: function(callback) {
this.callback = callback;
var lang = lang.emailauth;
var html = '';
html += this.getradio('reg');
html += this.getradio('login');
html += this.getradio('lostpass');
html += litepubl.tml.getedit('E-Mail', 'email', '');
html += litepubl.tml.getedit(lang.name, 'name', '');
html += litepubl.tml.getedit(lang.password, 'password', '').replace(/text/gm, 'password');
html += '<p id="info-status"></p>';

var self = this;
      var dialog = $.litedialog({
        title: lang.title,
        html: html,
        width: 300,
open: function(dialog) {
$("input[name=authtype]", dialog).on("click", function() {
var type = $(this).val();
var name = $("#text-name", dialog);
var pass = $("#password-password", dialog);
var regbutton = $("button[data-index=0]", dialog);
var loginbutton = $("button[data-index=1]", dialog);
var lostpassbutton = $("button[data-index=2]", dialog);

switch (type) {
case 'reg':
reg.show();
regbutton.show();
pass.hide();
loginbutton.hide();
lostpassbutton.hide();
break;

case 'login':
pass.show();
loginbutton.show();
name.hide();
regbutton.hide();
lostpassbutton.hide();
break;

case 'lostpass':
name.hide();
pass.hide();
regbutton.hide();
loginbutton.hide();
lostpassbutton.show();
break;
}
})
.filter('[value=reg]').prop('checked', "checked");
},

        buttons: [{
          title: lang.regbutton,
          click: function() {
var email = self.getemail();
if (!email) return false;
var edit = $("#text-name", dialog);
var name = $.trim(edit.val());
if (name) {
self.reg(email, name);
} else {
edit.focus();
}

return false;
}

}, {
          title: lang.loginbutton,
          click: function() {
var email = self.getemail();
if (!email) return false;
var edit = $("#password-password", dialog);
var password = $.trim(edit.val());
if (password) {
self.login(email, password);
} else {
edit.focus();
}

return false;
}

}, {
          title: lang.lostpassbutton,
          click: function() {
var email = self.getemail();
if (!email) return false;
self.lostpass(email);
return false;
}

}, {
          title: lang.dialog.close,
          click: $.closedialog
        }]
      });
    },

getemail: function() {
var email = $("#text-email", this.dialog);
var result = $.trim(email.val());
if (result) {
if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(result)) {
return result;
}
}

email.focus();
return false;
},

disable: function(disabled) {
$(":input", this.dialog).prop("disabled", disabled ? 'disabled="disabled"' : false);
},

success: function(r) {
litepubl.user = r;
set_cookie("litepubl_user_id", r.id);
set_cookie("litepubl_user", r.pass);
set_cookie("litepubl_regservice", 'email');
set_cookie("litepubl_user_flag", r.adminflag);
litepubl.ulogin.registered = true;

this.dialog = false;
$.closedialog(this.callback);
},

login: function(email, password) {
this.disable(true);
var self = this;
return $.litejson({method: "email_login", email: email, password: password}, $.proxy(this.success, this))
          .fail($.proxy(this.fail, this));
},

fail: function(jq, textStatus, errorThrown) {
this.disable(false);
$("#info-status", this.dialog).text(jq.responseText);
},

setstatus: function(status) {
this.disable(false);
$("input[value=login]", this.dialog).prop("checked", "checked");
$("#password-password", this.dialog).focus();
$("#info-status", this.dialog).text(lang.emailauth[status]);
},

reg: function(email, name) {
this.disable(true);
var self = this;
return $.litejson({method: "email_reg", email: email, name: name}, function(r) {
self.setstatus('registered');
})
          .fail($.proxy(this.fail, this));
},

lostpass: function(email, name) {
this.disable(true);
var self = this;
return $.litejson({method: "email_lostpass", email: email, name: name}, function(r) {
self.setstatus('restored');
})
          .fail($.proxy(this.fail, this));
}

  });//class
  
}(jQuery, document, window));