/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var commentform = {
fields: ["name", "email", "url"],
subscribed: [],
unsubscribed: [],
error_dialog: false,
confirm_dialog: false,

get: function(name) {
return $("input[name='" + name + "']", "#commentform").val();
},

set: function(name, value) {
$("input[name='" + name + "']", "#commentform").val(value);
},

find: function(name) {
if (name == 'content') return $("textarea[name='content']", "#commentform");
return $("input[name='" + name + "']", "#commentform");
},

init_field: function(name) {
var value = get_cookie("comuser_" + name);
if (!value ) return false;
this.set(name, value);
return true;
},

init_subscribe: function() {
var idpost = typeof ltoptions.idpost == "string" ? ltoptions.idpost : ltoptions.idpost.toString();
this.subscribed = get_cookie("comuser_subscribed").split(",");
this.unsubscribed = get_cookie("comuser_unsubscribed").split(",");
if ($.inArray(idpost, this.subscribed) >= 0) {
$("input[name='subscribe']").attr("checked", true);
} else if ($.inArray(idpost, this.unsubscribed) >= 0) {
$("input[name='subscribe']").attr("checked", false);
}
},

update_subscribe: function() {
var idpost = typeof ltoptions.idpost == "string" ? ltoptions.idpost : ltoptions.idpost.toString();
if ($("input[name='subscribe']").attr("checked")) {
if ($.inArray(idpost, this.subscribed) == -1) this.subscribed.unshift(idpost);
this.unsubscribed = $.grep(this.unsubscribed, function(val) { return val != idpost; }); 
} else {
if ($.inArray(idpost, this.unsubscribed) == -1) this.unsubscribed.unshift(idpost);
this.subscribed = $.grep(this.subscribed, function(val) { return val != idpost; }); 
}
set_cookie("comuser_subscribed", this.subscribed.join(","));
set_cookie("comuser_unsubscribed", this.unsubscribed.join(","));
},

init: function() {
$("#commentform").submit(commentform.submit);
if (commentform.init_field("name")) {
commentform.init_field("email");
commentform.init_field("url");
commentform.init_subscribe();
} else {
var iduser = get_cookie("userid");
if (!iduser) return;
$.get(ltoptions.url + "/ajaxcommentform.htm", 
{getuser : iduser, idpost: ltoptions.idpost},
function (resp) {
var data = $.parseJSON(resp);
commentform.set("name", data.name);
commentform.set("email", data.email);
commentform.set("url", data.url);
set_cookie("comuser_name", data.name);
set_cookie("comuser_email", data.email);
set_cookie("comuser_url", data.url);
$("input[name='subscribe']").attr("checked", data.subscribe);
commentform.update_subscribe();
});
}
},

set_cookie: function(name) {
set_cookie("comuser_" + name, this.get(name));
},

error: function(msg, name) {
$.load_ui(function() {
if (!commentform.error_dialog) {
commentform.error_dialog = $('<div class="ui-helper-hidden" title="' + ltoptions.commentform.error_title  +  '"><h4></h4></div>')
.appendTo($("input[name='name']").closest("form"));
}
$("h4", commentform.error_dialog ).text(msg);
$(commentform.error_dialog ).dialog( {
    autoOpen: true,
    modal: true,
    buttons: [
    {
      text: "Ok",
      click: function() {
        $(this).dialog("close");
if (name) commentform.find(name).focus();
      }
    } ]
  } );
});
},

confirm: function(data) {
$.load_ui(function() {
if (!commentform.confirm_dialog) {
commentform.confirm_dialog= $('<div class="ui-helper-hidden" title="' + data.title + '"><h4>' + data.formhead  + '</h4></div>')
.appendTo($("input[name='name']").closest("form"));
}

$(commentform.confirm_dialog).dialog( {
    autoOpen: true,
    modal: true,
    buttons: [
    {
      text: data.robot,
      click: function() {
        $(this).dialog("close");
      }
    } ,

    {
      text: data.human,
      click: function() {
        $(this).dialog("close");
commentform.sendconfirm(data.confirmid);
      }
    } ]
  } );
});
},

empty: function(name) {
if ("" == $.trim(this.get(name))) {
this.error(lang.comment.emptyname, name);
return true;
}
return false;
},

validemail: function() {
var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
if (!filter.test(this.get("email"))) {
this.error(lang.comment.invalidemail, "email");
return false;
}
return true;
},

validate: function() {
if (this.empty("name") || this.empty("email") || !this.validemail() ) return false;
if ("" == $.trim(this.find("content").val())) {
this.error(lang.comment.emptycontent, "content");
return false;
}
return true;
},

submit: function() {
if (!commentform.validate()) return false;
commentform.set_cookie("name");
commentform.set_cookie("email");
commentform.set_cookie("url");
commentform.update_subscribe();
commentform.send();
return false;
},

send: function() {
var form = $("input[name='name']").closest("form");
var values = {};
$("input, textarea, checkbox", form).each(function() {
values[$(this).attr("name")] = $(this).val();
$(this).attr("disabled", true);
});

$.post(ltoptions.url + "/ajaxcommentform.htm", values,
function (resp) {
try {
var data = $.parseJSON(resp);
switch (data.code) {
case 'confirm':
commentform.confirm(data);
break;

case 'success':
commentform.success(data);
break;

default: //error
commentform.error(data.msg, false);
break;
}
} catch(e) { commentform.error(e.message, false); }
})
.error(function(msg) {
commentform.error(msg, false);
})
.complete(function() {
$("input, textarea, checkbox", form).attr("disabled", false);
});

},

sendconfirm: function(confirmid) {
$.post(ltoptions.url + "/ajaxcommentform.htm", 
{confirmid: confirmid},
function (resp) {
try {
var data = $.parseJSON(resp);
switch (data.code) {
case 'success':
commentform.success(data);
break;

default: //error
commentform.error(data.msg, false);
break;
}
} catch(e) { commentform.error(e.message, false); }
})
.error(function(msg) {
commentform.error(msg, false);
});
},

success: function(data) {
set_cookie('userid', data.userid);
window.location = data.posturl;
}

};

$(document).ready(commentform.init);