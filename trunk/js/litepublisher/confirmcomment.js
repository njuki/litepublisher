(function( $ ){
  $.confirmcomment = function(opt) {
var options= $.extend({
form: "#commentform",
        editor: "#comment"
      }, ltoptions.theme.comments, opt);

var form= {
fields: ["name", "email", "url"],

get: function(name) {
if (name == 'content') return $(options.editor);
return $("input[name='" + name + "']", options.form);
},

init: function() {
//ctrl+enter
$(options.editor).on("keydown.confirmcomment", function (e) {
  if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
$(options.form).submit();
}
});

$(options.form).on("submit.confirmcomment", form.submit);
},

error: function(field, mesg) {
        $.messagebox(lang.comments.error, mesg)
.close = function() {
form.get(field).focus);
};
},

confirm: function(data) {
$.load_ui(function() {
if (!commentform.confirm_dialog) {
commentform.confirm_dialog= $('<div class="ui-helper-hidden" title="' + data.title + '"><h4>' + data.title + '</h4></div>')
.appendTo($("input[name='name']").closest("form"));
}

$(commentform.confirm_dialog).dialog( {
    autoOpen: true,
    modal: true,
    buttons: [
    {
      text: lang.comment.robot,
      click: function() {
        $(this).dialog("close");
      }
    } ,

    {
      text: lang.comment.human,
      click: function() {
        $(this).dialog("close");
commentform.sendconfirm(data.confirmid);
      }
    } ]
  } );
});
},

empty: function(name) {
if ("" != $.trim(form.get(name).val())) return false;
form.error(name, lang.comment.emptyname);
return true;
},

validemail: function() {
var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
if (!filter.test(form.get("email").val())) {
form.error("email", lang.comment.invalidemail);
return false;
}
return true;
},

validate: function() {
if (form.empty("name") || form.empty("email") || !form.validemail() ) return false;
if ("" == $.trim(form.get("content").val())) {
form.error("content", lang.comment.emptycontent);
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
$(document).ready(function() {
$.confirmcomment();
});

})( jQuery );