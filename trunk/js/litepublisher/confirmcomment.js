(function( $ ){
  $.confirmcomment = function(opt) {
var options= $.extend({
"confirmcomment": true,
form: "#form",
        editor: "#comment"
      }, ltoptions.theme.comments, opt);

var form= {
get: function(name) {
if (name == 'content') return $(options.editor);
return $("input[name='" + name + "']", options.form);
},

error: function(mesg) {
return $.messagebox(lang.dialog.error, mesg);
},

error_field: function(field, mesg) {
form.error(mesg).close = function() {
form.get(field).focus();
};
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
if ("" == $.trim(get$(options.editor).val())) {
form.error_field("content", lang.comment.emptycontent);
return false;
}
if (form.empty("name") || form.empty("email") || !form.validemail() ) return false;
return true;
},

send: function() {
var inputs = $(":input", options.form);
var values = {method: "comment_add"};
inputs.each(function() {
var self = $(this);
values[self.attr("name")] = self.val();
self.attr("disabled", "disabled");
});

$.litejsontype("post", $values, function (resp) {
try {
switch (resp.code) {
case 'confirm':
form.confirm(resp.confirmid);
break;

case 'success':
form.success(resp);
break;

default: //error
form.error(resp.msg);
break;
}
} catch(e) { form.error(e.message); }
})
            .error( function(jq, textStatus, errorThrown) {
form.error(jq.responseText);
})
.complete(function() {
inputs.removeAttr("disabled");
});
},

confirm: function(confirmid) {
          $.confirmbox(lang.dialog.confirm, lang.comment.checkspam , lang.comment.robot, lang.comment.human, function(index) {
            if (index !=1) return;
$.litejsontype("post", {method: "comment_confirm", confirmid: confirmid}, form.success)
            .error( function(jq, textStatus, errorThrown) {
form.error(jq.responseText);
});
});
},

success: function(data) {
if ("cookies" in data) {
var name = "";
for (name in data.cookies) {
set_cookie(name, data.cookies[name]);
}
}
window.location = data.posturl;
},

submit: function() {
if (!form.validate()) return false;
if (options.confirmcomment) {
form.send();
return false;
}
}

}; //form

//init
//ctrl+enter
$(options.editor).off("keydown.confirmcomment").on("keydown.confirmcomment", function (e) {
  if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
$(options.form).submit();
}
});

$(options.form).off("submit.confirmcomment").on("submit.confirmcomment", form.submit);
};

$(document).ready(function() {
$.confirmcomment();
});

})( jQuery );