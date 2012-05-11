(function( $ ){
  $.confirmcomment = function(opt) {
var options= $.extend({
form: "#form",
        editor: "#comment"
      }, ltoptions.theme.comments, opt);

var form= {
fields: ["name", "email", "url"],

get: function(name) {
if (name == 'content') return $(options.editor);
return $("input[name='" + name + "']", options.form);
},

error: function(field, mesg) {
        var m = $.messagebox(lang.comments.error, mesg);
if (field) m.close = function() {
form.get(field).focus);
};
},

confirm: function(data) {
          $.confirmbox(lang.comments.confirm, data.title, lang.comment.robot, lang.comment.human, function(index) {
            if (index !=1) return;
$.litejson({method: "comment_confirm", confirmid: data.confirmid}, function (resp) {
try {
form.success(data);
} catch(e) { form.error(e.message, false); }
})
.error(function(msg) {
form.error(msg, false);
});
},


  } );
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
form.error("content", lang.comment.emptycontent);
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
form.confirm(data);
break;

case 'success':
form.success(data);
break;

default: //error
form.error(false, data.msg);
break;
}
} catch(e) { form.error(e.message, false); }
})
            .error( function(jq, textStatus, errorThrown) {
form.error(false, jq.responseText);
})
.complete(function() {
inputs.removeAttr("disabled");
});

},

success: function(data) {
set_cookie('userid', data.userid);
window.location = data.posturl;
}
};

//init
//ctrl+enter
$(options.editor).off("keydown.confirmcomment").on("keydown.confirmcomment", function (e) {
  if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
$(options.form).submit();
},

submit: function() {
if (form.validate()) form.send();
return false;
}
});

$(options.form).off("submit.confirmcomment").on("submit.confirmcomment", form.submit);
};

$(document).ready(function() {
$.confirmcomment();
});

})( jQuery );